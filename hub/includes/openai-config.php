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
        realpath(__DIR__ . '/../temp/')
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

?>
