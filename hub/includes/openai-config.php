237
<?php
// QuietGo OpenAI API Configuration - SECURE VERSION
// ================================================

/**
 * Load environment variables securely
 */
function loadEnvironmentVariables() {
    $envFile = __DIR__ . '/../../.env';

    if (!file_exists($envFile)) {
        throw new Exception('Environment file (.env) not found. Please configure your API keys.');
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');

            if (!empty($key) && !isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load environment variables
loadEnvironmentVariables();

// Validate required environment variables
if (empty($_ENV['OPENAI_API_KEY'])) {
    throw new Exception('OPENAI_API_KEY not found in environment variables.');
}

// OpenAI API Settings - Now loaded securely from environment
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY']);
define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('OPENAI_VISION_MODEL', 'gpt-4-vision-preview');
define('OPENAI_TEXT_MODEL', 'gpt-4');

// Anthropic API Settings - Multi-model strategy for cost optimization
if (!empty($_ENV['ANTHROPIC_API_KEY'])) {
    define('ANTHROPIC_API_KEY', $_ENV['ANTHROPIC_API_KEY']);
    define('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1/messages');
    define('ANTHROPIC_HAIKU_MODEL', 'claude-3-haiku-20240307');
    define('ANTHROPIC_SONNET_MODEL', 'claude-3-5-sonnet-20241022');
}

// Model routing configuration
define('MODEL_ROUTING_ENABLED', true);
define('CONFIDENCE_THRESHOLD_HIGH', 0.85);  // Use cheap model (Haiku)
define('CONFIDENCE_THRESHOLD_MEDIUM', 0.70); // Use medium model (GPT-4o-mini)
// Below medium threshold = use expensive model (GPT-4 Vision)

/**
 * Make OpenAI API Request with enhanced error handling
 * @param array $messages - Array of messages for the API
 * @param string $model - Model to use (vision or text)
 * @param int $max_tokens - Maximum tokens in response
 * @return array - API response
 */
function makeOpenAIRequest($messages, $model = OPENAI_TEXT_MODEL, $max_tokens = 1000) {
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY,
        'User-Agent: QuietGo-Hub/1.0'
    ];

    $data = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $max_tokens,
        'temperature' => 0.7
    ];

    // Log API request (without sensitive data)
    error_log("QuietGo AI Request: Model=$model, Messages=" . count($messages) . ", MaxTokens=$max_tokens");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, OPENAI_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("QuietGo AI CURL Error: $curlError");
        return ['error' => 'Network connection failed'];
    }

    if ($httpCode !== 200) {
        error_log("QuietGo AI HTTP Error: HTTP $httpCode - Response: " . substr($response, 0, 500));

        // Parse error response for better user feedback
        $errorData = json_decode($response, true);
        if (isset($errorData['error']['message'])) {
            return ['error' => 'AI service error: ' . $errorData['error']['message']];
        }

        return ['error' => "AI service temporarily unavailable (HTTP $httpCode)"];
    }

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("QuietGo AI JSON Error: " . json_last_error_msg());
        return ['error' => 'Invalid response from AI service'];
    }

    if (!isset($decoded['choices'][0]['message']['content'])) {
        error_log("QuietGo AI Response Error: Missing content in response");
        return ['error' => 'Incomplete response from AI service'];
    }

    // Log successful response (without content)
    $usage = $decoded['usage'] ?? [];
    error_log("QuietGo AI Success: Tokens used=" . ($usage['total_tokens'] ?? 'unknown'));

    return $decoded;
}

/**
 * Make Anthropic API Request (Claude models) for cost-optimized analysis
 * @param array $messages - Array of messages for the API
 * @param string $model - Model to use (Haiku or Sonnet)
 * @param int $max_tokens - Maximum tokens in response
 * @return array - API response normalized to OpenAI format for compatibility
 */
function makeAnthropicRequest($messages, $model = ANTHROPIC_HAIKU_MODEL, $max_tokens = 1000) {
    if (!defined('ANTHROPIC_API_KEY')) {
        error_log('QuietGo: Anthropic API not configured, falling back to OpenAI');
        return ['error' => 'Anthropic API not configured'];
    }

    $headers = [
        'Content-Type: application/json',
        'x-api-key: ' . ANTHROPIC_API_KEY,
        'anthropic-version: 2023-06-01'
    ];

    // Convert OpenAI message format to Anthropic format
    $anthropicMessages = [];
    $systemPrompt = '';
    
    foreach ($messages as $msg) {
        if ($msg['role'] === 'system') {
            $systemPrompt = $msg['content'];
        } else {
            // Handle content that might be array (for vision) or string
            if (is_array($msg['content'])) {
                $convertedContent = [];
                foreach ($msg['content'] as $item) {
                    if ($item['type'] === 'text') {
                        $convertedContent[] = [
                            'type' => 'text',
                            'text' => $item['text']
                        ];
                    } elseif ($item['type'] === 'image_url') {
                        // Extract base64 data and mime type from data URL
                        $imageUrl = $item['image_url']['url'];
                        if (preg_match('/^data:(image\/[^;]+);base64,(.+)$/', $imageUrl, $matches)) {
                            $mimeType = $matches[1];
                            $base64Data = $matches[2];
                            
                            $convertedContent[] = [
                                'type' => 'image',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => $mimeType,
                                    'data' => $base64Data
                                ]
                            ];
                        }
                    }
                }
                $anthropicMessages[] = [
                    'role' => $msg['role'],
                    'content' => $convertedContent
                ];
            } else {
                $anthropicMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }
    }

    $data = [
        'model' => $model,
        'messages' => $anthropicMessages,
        'max_tokens' => $max_tokens,
        'temperature' => 0.7
    ];

    if (!empty($systemPrompt)) {
        $data['system'] = $systemPrompt;
    }

    error_log("QuietGo Anthropic Request: Model=$model, Messages=" . count($anthropicMessages));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ANTHROPIC_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("QuietGo Anthropic CURL Error: $curlError");
        return ['error' => 'Network connection failed'];
    }

    if ($httpCode !== 200) {
        error_log("QuietGo Anthropic HTTP Error: HTTP $httpCode - Response: " . substr($response, 0, 500));
        $errorData = json_decode($response, true);
        if (isset($errorData['error']['message'])) {
            return ['error' => 'AI service error: ' . $errorData['error']['message']];
        }
        return ['error' => "AI service temporarily unavailable (HTTP $httpCode)"];
    }

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("QuietGo Anthropic JSON Error: " . json_last_error_msg());
        return ['error' => 'Invalid response from AI service'];
    }

    if (!isset($decoded['content'][0]['text'])) {
        error_log("QuietGo Anthropic Response Error: Missing content");
        return ['error' => 'Incomplete response from AI service'];
    }

    // Normalize Anthropic response to match OpenAI format for compatibility
    $normalized = [
        'choices' => [
            [
                'message' => [
                    'content' => $decoded['content'][0]['text']
                ]
            ]
        ],
        'usage' => [
            'total_tokens' => ($decoded['usage']['input_tokens'] ?? 0) + ($decoded['usage']['output_tokens'] ?? 0)
        ]
    ];

    error_log("QuietGo Anthropic Success: Tokens used=" . $normalized['usage']['total_tokens']);

    return $normalized;
}

/**
 * Encode image to base64 for OpenAI Vision API with security checks
 * @param string $imagePath - Path to the image file
 * @return string|null - Base64 encoded image data URL or null on failure
 */
function encodeImageForOpenAI($imagePath) {
    // Security: Validate file path is within allowed directories
    $realPath = realpath($imagePath);
    $allowedBasePaths = [
        realpath(__DIR__ . '/../uploads/'),
        realpath(__DIR__ . '/../../uploads/'),
        realpath(__DIR__ . '/../temp/'),
        realpath(__DIR__ . '/../QuietGoData/')  // User data storage (inside hub/)
    ];

    $isAllowed = false;
    foreach ($allowedBasePaths as $basePath) {
        if ($basePath && strpos($realPath, $basePath) === 0) {
            $isAllowed = true;
            break;
        }
    }

    if (!$isAllowed) {
        error_log("QuietGo Security: Attempted to access file outside allowed directories: $imagePath");
        return null;
    }

    if (!file_exists($realPath)) {
        error_log("QuietGo Error: Image file not found: $imagePath");
        return null;
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mimeType = mime_content_type($realPath);

    if (!in_array($mimeType, $allowedTypes)) {
        error_log("QuietGo Security: Invalid image type: $mimeType for file: $imagePath");
        return null;
    }

    // Check file size (max 20MB for OpenAI)
    $fileSize = filesize($realPath);
    if ($fileSize > 20 * 1024 * 1024) {
        error_log("QuietGo Error: Image too large: " . ($fileSize / 1024 / 1024) . "MB");
        return null;
    }

    $imageData = file_get_contents($realPath);
    if ($imageData === false) {
        error_log("QuietGo Error: Failed to read image file: $imagePath");
        return null;
    }

    $base64 = base64_encode($imageData);
    error_log("QuietGo Image: Encoded " . strlen($imageData) . " bytes for AI analysis");

    return "data:$mimeType;base64,$base64";
}

/**
 * Journey-specific prompt configurations
 */
function getJourneyPromptConfig($userJourney) {
    $configs = [
        'clinical' => [
            'tone' => 'professional medical terminology suitable for healthcare providers',
            'focus' => 'clinical significance, symptoms correlation, and medical documentation',
            'recommendations' => 'healthcare provider communication and medical appointment preparation'
        ],
        'performance' => [
            'tone' => 'athletic performance and training optimization language',
            'focus' => 'nutrition impact on training, recovery, energy, and athletic performance',
            'recommendations' => 'performance enhancement, training optimization, and macro timing'
        ],
        'best_life' => [
            'tone' => 'encouraging, positive lifestyle wellness and feel-good insights',
            'focus' => 'energy levels, daily wellness, quality of life, and lifestyle optimization',
            'recommendations' => 'lifestyle improvements, wellness habits, and energy optimization'
        ]
    ];

    return $configs[$userJourney] ?? $configs['best_life'];
}

/**
 * Rate limiting for API calls (basic implementation)
 */
function checkAPIRateLimit($userId = null) {
    $rateLimitFile = __DIR__ . '/../temp/api_rate_limit.json';
    $now = time();
    $limits = [];

    // Load existing limits
    if (file_exists($rateLimitFile)) {
        $limits = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    }

    // Clean old entries (older than 1 hour)
    $limits = array_filter($limits, function($timestamp) use ($now) {
        return ($now - $timestamp) < 3600;
    });

    $userKey = $userId ?: 'anonymous';
    $userRequests = array_filter($limits, function($timestamp, $key) use ($userKey, $now) {
        return strpos($key, $userKey . '_') === 0 && ($now - $timestamp) < 3600;
    }, ARRAY_FILTER_USE_BOTH);

    // Limit: 50 requests per hour per user
    if (count($userRequests) >= 50) {
        return false;
    }
    // Log this request
    $limits[$userKey . '_' . $now . '_' . rand(1000, 9999)] = $now;

    // Save updated limits
    $tempDir = dirname($rateLimitFile);
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    file_put_contents($rateLimitFile, json_encode($limits));

    return true;
}

// Security: Clear any sensitive variables from memory
unset($_ENV['OPENAI_API_KEY']);
if (isset($_ENV['ANTHROPIC_API_KEY'])) {
    unset($_ENV['ANTHROPIC_API_KEY']);
}

?>
