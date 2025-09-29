<?php
// Prevent any output before HTML starts
ob_start();

// Suppress notices and warnings that might cause stray output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Hub authentication check - ONLY Pro and Pro+ users have hub access
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) ||
                   (isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === 'true') ||
                   (isset($_SESSION['hub_user']['is_admin_impersonation']));

if (!isset($_SESSION['hub_user']) && !isset($_COOKIE['hub_auth']) && !$isAdminLoggedIn) {
    header('Location: /hub/login.php');
    exit;
}

if ($isAdminLoggedIn && !isset($_SESSION['hub_user'])) {
    $_SESSION['hub_user'] = [
        'email' => 'admin@quietgo.app',
        'name' => 'Admin User',
        'login_time' => time(),
        'is_admin_impersonation' => true,
        'subscription_plan' => 'pro_plus',
        'journey' => 'best_life'
    ];
}

// Get user subscription status and journey preferences
$user = $_SESSION['hub_user'];
$subscriptionPlan = $user['subscription_plan'] ?? 'free';

// CORRECTED SUBSCRIPTION LOGIC
$hasCalcuPlate = in_array($subscriptionPlan, ['pro_plus']);  // Only Pro+ has CalcuPlate
$isProUser = in_array($subscriptionPlan, ['pro', 'pro_plus']); // Pro and Pro+ have hub access
$isFreeTier = ($subscriptionPlan === 'free');

// Free users get NO hub access - redirect immediately
if ($isFreeTier && !$isAdminLoggedIn) {
    header('Location: /hub/login.php?message=pro_required');
    exit;
}

// Journey personalization
$userJourney = $user['journey'] ?? 'best_life';
$journeyConfig = [
    'clinical' => [
        'title' => '🏥 Clinical Focus',
        'focus' => 'symptom patterns and provider collaboration',
        'ai_tone' => 'clinical insights with medical terminology',
        'meal_focus' => 'symptom triggers and digestive impact',
        'recommendations' => 'healthcare provider communication and medical appointment preparation'
    ],
    'performance' => [
        'title' => '💪 Peak Performance',
        'focus' => 'nutrition impact on training and recovery',
        'ai_tone' => 'performance-focused analysis and coaching',
        'meal_focus' => 'energy, recovery, and performance optimization',
        'recommendations' => 'performance enhancement, training optimization, and macro timing'
    ],
    'best_life' => [
        'title' => '✨ Best Life Mode',
        'focus' => 'energy levels and living your best life daily',
        'ai_tone' => 'lifestyle optimization and feel-good insights',
        'meal_focus' => 'energy levels and overall wellness',
        'recommendations' => 'lifestyle improvements, wellness habits, and energy optimization'
    ]
];
$currentJourneyConfig = $journeyConfig[$userJourney];

// Enhanced photo upload handling with time/location stamping
$uploadResult = null;

// Check for pending manual logging from previous upload (SESSION persistence)
if (isset($_SESSION['pending_manual_logging'])) {
    $uploadResult = $_SESSION['pending_manual_logging'];
    unset($_SESSION['pending_manual_logging']); // Clear after retrieving
}

// Handle manual meal logging form submission (Pro users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo_filename']) && !isset($_FILES['health_item'])) {
    $uploadResult = handleManualMealLogging($_POST, $user);
}

// Check if this is an AJAX request - return JSON instead of HTML
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['health_item'])) {
    // Handle multiple file uploads properly
    $uploadResults = [];
    
    // Check if files were actually uploaded
    if (is_array($_FILES['health_item']['name'])) {
        // Multiple files - check if any files were selected
        $hasFiles = false;
        for ($i = 0; $i < count($_FILES['health_item']['name']); $i++) {
            if (!empty($_FILES['health_item']['name'][$i]) && $_FILES['health_item']['error'][$i] === UPLOAD_ERR_OK) {
                $hasFiles = true;
                break;
            }
        }
        
        if (!$hasFiles) {
            $uploadResult = ['status' => 'error', 'message' => 'No files were selected for upload.'];
        } else {
            // Process multiple files
            for ($i = 0; $i < count($_FILES['health_item']['name']); $i++) {
                if (!empty($_FILES['health_item']['name'][$i])) {
                    $singleFile = [
                        'name' => $_FILES['health_item']['name'][$i],
                        'type' => $_FILES['health_item']['type'][$i],
                        'tmp_name' => $_FILES['health_item']['tmp_name'][$i],
                        'error' => $_FILES['health_item']['error'][$i],
                        'size' => $_FILES['health_item']['size'][$i]
                    ];
                    $result = handlePhotoUpload($singleFile, $_POST, $user);
                    $uploadResults[] = $result;
                }
            }
            
            // Use the first successful result for display
            $uploadResult = null;
            foreach ($uploadResults as $result) {
                if ($result['status'] === 'success') {
                    $uploadResult = $result;
                    $uploadResult['message'] = count($uploadResults) . ' photo(s) uploaded successfully!';
                    break;
                }
            }
            if (!$uploadResult) {
                $uploadResult = $uploadResults[0]; // Show first error if all failed
            }
        }
    } else {
        // Single file
        if (!empty($_FILES['health_item']['name']) && $_FILES['health_item']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handlePhotoUpload($_FILES['health_item'], $_POST, $user);
        } else {
            $uploadResult = ['status' => 'error', 'message' => 'No file was selected for upload.'];
        }
    }
    
    // If AJAX request, return JSON and exit
    if ($isAjax && $uploadResult) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($uploadResult);
        exit;
    }
}

function handlePhotoUpload($file, $postData, $user) {
    global $hasCalcuPlate, $userJourney;

    // Include storage helper and database operations
    require_once __DIR__ . '/includes/storage-helper.php';
    require_once __DIR__ . '/includes/db-operations.php';
    $storage = getQuietGoStorage();

    // Ensure user structure exists
    $storage->createUserStructure($user['email']);

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'Upload failed: ' . $file['error']];
    }

    // Validate image file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['status' => 'error', 'message' => 'Invalid file type. Please upload images only.'];
    }

    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['status' => 'error', 'message' => 'File too large. Maximum size is 10MB.'];
    }

    // Location data
    $locationData = null;
    if (!empty($postData['latitude']) && !empty($postData['longitude'])) {
        $locationData = [
            'latitude' => floatval($postData['latitude']),
            'longitude' => floatval($postData['longitude']),
            'accuracy' => $postData['accuracy'] ?? null,
            'timestamp' => time()
        ];
    }

    // Prepare comprehensive metadata
    $metadata = [
        'original_name' => $file['name'],
        'category' => $postData['category'] ?? 'photos',
        'photo_type' => $postData['photo_type'] ?? 'general',
        'upload_timestamp' => time(),
        'upload_datetime' => date('Y-m-d H:i:s'),
        'user_email' => $user['email'],
        'user_journey' => $userJourney,
        'subscription_plan' => $user['subscription_plan'],
        'has_calcuplate' => $hasCalcuPlate,
        'location_data' => $locationData,
        'context' => [
            'time_of_day' => $postData['context_time'] ?? '',
            'symptoms' => $postData['context_symptoms'] ?? '',
            'notes' => $postData['context_notes'] ?? ''
        ],
        'file_info' => [
            'size' => $file['size'],
            'mime_type' => $mimeType
        ]
    ];

    // Store photo using organized structure
    $photoType = $postData['photo_type'] ?? 'general';
    $storeResult = $storage->storePhoto($user['email'], $photoType, $file, $metadata);

    if (!$storeResult['success']) {
        return ['status' => 'error', 'message' => $storeResult['error']];
    }

    // Generate AI analysis
    $aiAnalysis = generateAIAnalysis($postData, $userJourney, $hasCalcuPlate, $storeResult['filepath']);

    // Get/create user in database
    $userId = getOrCreateUser($user['email'], [
        'name' => $user['name'] ?? 'User',
        'journey' => $userJourney,
        'subscription_plan' => $user['subscription_plan'] ?? 'free',
        'subscription_status' => 'active'
    ]);

    // Save photo metadata to database
    $photoId = savePhoto($userId, [
        'photo_type' => $photoType,
        'filename' => basename($storeResult['filepath']),
        'filepath' => $storeResult['filepath'],
        'thumbnail_path' => $storeResult['thumbnail'] ?? null,
        'file_size' => $file['size'],
        'mime_type' => $mimeType,
        'location_latitude' => $locationData['latitude'] ?? null,
        'location_longitude' => $locationData['longitude'] ?? null,
        'location_accuracy' => $locationData['accuracy'] ?? null,
        'context_time' => $postData['context_time'] ?? null,
        'context_symptoms' => $postData['context_symptoms'] ?? null,
        'context_notes' => $postData['context_notes'] ?? null,
        'original_filename' => $file['name']
    ]);

    // Save analysis to database based on type
    if (!isset($aiAnalysis['error']) && !isset($aiAnalysis['manual_logging_required'])) {
        switch ($photoType) {
            case 'stool':
                saveStoolAnalysis($photoId, $userId, $aiAnalysis);
                break;
            case 'meal':
                if ($hasCalcuPlate) {
                    saveMealAnalysis($photoId, $userId, $aiAnalysis);
                }
                break;
            case 'symptom':
                saveSymptomAnalysis($photoId, $userId, $aiAnalysis);
                break;
        }
        
        // Track AI cost
        if (isset($aiAnalysis['ai_model'])) {
            trackAICost($userId, [
                'photo_type' => $photoType,
                'ai_model' => $aiAnalysis['ai_model'],
                'model_tier' => $aiAnalysis['model_tier'] ?? $aiAnalysis['ai_model'] ?? 'expensive',
                'tokens_used' => null,
                'processing_time' => $aiAnalysis['processing_time'] ?? null
            ]);
        }
    }

    // Store AI analysis in organized location (legacy file system)
    if (!isset($aiAnalysis['error'])) {
        $storage->storeAnalysis($user['email'], 'ai_results', [
            'photo_type' => $photoType,
            'analysis' => $aiAnalysis,
            'photo_metadata' => $metadata
        ]);
    }

    $result = [
        'status' => 'success',
        'photo_id' => $photoId ?? null,
        'filename' => basename($storeResult['filepath']),
        'thumbnail' => $storeResult['thumbnail'],
        'ai_analysis' => $aiAnalysis,
        'metadata' => $metadata,
        'requires_manual_logging' => (!$hasCalcuPlate && $postData['photo_type'] === 'meal'),
        'storage_path' => $storeResult['filepath']
    ];
    
    // Store in SESSION if manual logging required (for persistence across page reload)
    if ($result['requires_manual_logging']) {
        $_SESSION['pending_manual_logging'] = $result;
    }
    
    return $result;
}

function generateAIAnalysis($postData, $userJourney, $hasCalcuPlate, $imagePath = null) {
    // Include OpenAI configuration and analysis functions
    require_once __DIR__ . '/includes/openai-config.php';
    require_once __DIR__ . '/includes/analysis-functions.php';

    $photoType = $postData['photo_type'] ?? 'general';
    $symptoms = $postData['context_symptoms'] ?? '';
    $time = $postData['context_time'] ?? '';
    $notes = $postData['context_notes'] ?? '';
    $startTime = microtime(true);

    // Check API rate limits
    $userEmail = $_SESSION['hub_user']['email'] ?? 'anonymous';
    if (!checkAPIRateLimit($userEmail)) {
        return [
            'error' => 'Rate limit exceeded. Please try again in an hour.',
            'timestamp' => time(),
            'user_journey' => $userJourney
        ];
    }

    $analysis = [
        'timestamp' => time(),
        'user_journey' => $userJourney,
        'photo_type' => $photoType
    ];

    // Get journey-specific prompt configuration
    $journeyConfig = getJourneyPromptConfig($userJourney);

    try {
        switch ($photoType) {
            case 'stool':
                $analysis = analyzeStoolPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes);
                break;

            case 'meal':
                if ($hasCalcuPlate) {
                    // PRO+ ONLY: CalcuPlate AI meal analysis
                    $analysis = analyzeMealPhotoWithCalcuPlate($imagePath, $journeyConfig, $symptoms, $time, $notes);
                } else {
                    // PRO ONLY: Manual logging required
                    $analysis = [
                        'manual_logging_required' => true,
                        'upgrade_available' => [
                            'feature' => 'CalcuPlate AI Meal Analysis',
                            'price' => '+$2.99/month',
                            'benefits' => ['Automatic food detection', 'Instant calorie calculation', 'Auto-logged nutrition data']
                        ],
                        'next_step' => 'Complete manual meal logging form to continue',
                        'timestamp' => time(),
                        'user_journey' => $userJourney
                    ];
                }
                break;

            case 'symptom':
                $analysis = analyzeSymptomPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes);
                break;

            default:
                $analysis['error'] = 'Unknown photo type';
        }

        // Add processing time
        $analysis['processing_time'] = round(microtime(true) - $startTime, 2);

    } catch (Exception $e) {
        error_log("QuietGo AI Analysis Error: " . $e->getMessage());
        $analysis = [
            'error' => 'AI analysis temporarily unavailable. Please try again.',
            'timestamp' => time(),
            'user_journey' => $userJourney,
            'processing_time' => round(microtime(true) - $startTime, 2)
        ];
    }

    return $analysis;
}

function analyzeStoolPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes) {
    $base64Image = encodeImageForOpenAI($imagePath);
    if (!$base64Image) {
        throw new Exception('Failed to process image for AI analysis');
    }

    // Create journey-specific stool analysis prompt
    $systemPrompt = "You are a professional digestive health AI assistant specialized in Bristol Stool Scale analysis.

Analyze this stool photo and provide insights using {$journeyConfig['tone']} focused on {$journeyConfig['focus']}.

Please respond ONLY with a valid JSON object containing:
{
    \"bristol_scale\": (1-7 number),
    \"bristol_description\": \"Type X: Description\",
    \"color_assessment\": \"color description\",
    \"consistency\": \"hard/normal/loose\",
    \"volume_estimate\": \"small/normal/large\",
    \"confidence\": (70-95 number),
    \"health_insights\": [\"insight1\", \"insight2\", \"insight3\"],
    \"recommendations\": [\"rec1\", \"rec2\", \"rec3\"]
}

Context provided:";

    $userPrompt = "Time: $time
Symptoms: $symptoms
Notes: $notes

Analyze this stool photo using the Bristol Stool Scale and provide {$journeyConfig['recommendations']}.";

    $messages = [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $userPrompt
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $base64Image,
                        'detail' => 'high'
                    ]
                ]
            ]
        ]
    ];

    $response = makeOpenAIRequest($messages, OPENAI_VISION_MODEL, 800);

    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];
    
    // Strip markdown code blocks if present
    $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
    $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
    $aiContent = trim($aiContent);
    
    $analysisData = json_decode($aiContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("QuietGo Stool Analysis JSON Error: " . json_last_error_msg());
        error_log("AI Response: " . $aiContent);
        throw new Exception('Invalid AI response format');
    }

    // Add metadata
    $analysisData['timestamp'] = time();
    $analysisData['ai_model'] = OPENAI_VISION_MODEL;
    $analysisData['reported_symptoms'] = $symptoms ?: null;
    $analysisData['correlation_note'] = $symptoms ? 'Symptoms logged for pattern analysis' : null;

    return $analysisData;
}

function handleManualMealLogging($postData, $user) {
    // Include storage helper
    require_once __DIR__ . '/includes/storage-helper.php';
    $storage = getQuietGoStorage();

    // Validate required fields
    $required_fields = ['meal_type', 'meal_time', 'portion_size', 'main_foods'];
    foreach ($required_fields as $field) {
        if (empty($postData[$field])) {
            return ['status' => 'error', 'message' => 'Please fill in all required fields: ' . str_replace('_', ' ', $field)];
        }
    }

    // Validate time format
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $postData['meal_time'])) {
        return ['status' => 'error', 'message' => 'Please enter a valid time in HH:MM format'];
    }

    // Prepare meal log data
    $mealData = [
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        'user_email' => $user['email'],
        'photo_filename' => $postData['photo_filename'] ?? null,
        'meal_type' => $postData['meal_type'],
        'meal_time' => $postData['meal_time'],
        'portion_size' => $postData['portion_size'],
        'main_foods' => $postData['main_foods'],
        'estimated_calories' => !empty($postData['estimated_calories']) ? intval($postData['estimated_calories']) : null,
        'protein_grams' => !empty($postData['protein_grams']) ? floatval($postData['protein_grams']) : null,
        'carb_grams' => !empty($postData['carb_grams']) ? floatval($postData['carb_grams']) : null,
        'fat_grams' => !empty($postData['fat_grams']) ? floatval($postData['fat_grams']) : null,
        'hunger_before' => $postData['hunger_before'] ?? null,
        'fullness_after' => $postData['fullness_after'] ?? null,
        'energy_level' => !empty($postData['energy_level']) ? intval($postData['energy_level']) : null,
        'meal_notes' => $postData['meal_notes'] ?? null,
        'user_journey' => $user['journey'] ?? 'best_life',
        'subscription_plan' => $user['subscription_plan']
    ];

    // Store the meal log
    $logPath = $storage->storeLog($user['email'], 'manual_meals', $mealData);

    if ($logPath) {
        return [
            'status' => 'success',
            'message' => 'Meal logged successfully! This data will be used for pattern analysis with your stool tracking.',
            'log_path' => $logPath,
            'meal_data' => $mealData
        ];
    } else {
        return ['status' => 'error', 'message' => 'Failed to save meal log. Please try again.'];
    }
}

function analyzeMealPhotoWithCalcuPlate($imagePath, $journeyConfig, $symptoms, $time, $notes) {
    // Include smart router for multi-model cost optimization
    require_once __DIR__ . '/includes/smart-ai-router.php';
    
    $base64Image = encodeImageForOpenAI($imagePath);
    if (!$base64Image) {
        throw new Exception('Failed to process image for AI analysis');
    }

    $systemPrompt = "You are CalcuPlate, an advanced nutrition AI that analyzes meal photos for automatic logging.

Analyze this meal photo and provide comprehensive nutritional data using {$journeyConfig['tone']} focused on {$journeyConfig['focus']}.

Respond ONLY with valid JSON:
{
    \"calcuplate\": {
        \"auto_logged\": true,
        \"foods_detected\": [\"food1\", \"food2\", \"food3\"],
        \"total_calories\": (number),
        \"macros\": {
            \"protein\": \"XXg\",
            \"carbs\": \"XXg\",
            \"fat\": \"XXg\",
            \"fiber\": \"XXg\"
        },
        \"meal_quality_score\": \"X/10\",
        \"portion_sizes\": \"description\",
        \"nutritional_completeness\": \"XX%\"
    },
    \"confidence\": (75-95 number),
    \"nutrition_insights\": [\"insight1\", \"insight2\", \"insight3\"],
    \"recommendations\": [\"rec1\", \"rec2\", \"rec3\"]
}";

    $userPrompt = "Time: $time
Context: $notes
Symptoms: $symptoms

Analyze this meal photo for automatic nutritional logging with focus on {$journeyConfig['meal_focus']}.";

    $messages = [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $userPrompt
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $base64Image,
                        'detail' => 'high'
                    ]
                ]
            ]
        ]
    ];

    // Use smart router instead of direct API call
    $response = SmartAIRouter::routeImageAnalysis($imagePath, $messages, 'meal', 1000);

    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];
    
    // Strip markdown code blocks if present (GPT-4o-mini sometimes wraps JSON)
    $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
    $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
    $aiContent = trim($aiContent);
    
    $analysisData = json_decode($aiContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("QuietGo CalcuPlate JSON Error: " . json_last_error_msg());
        error_log("AI Response: " . $aiContent);
        throw new Exception('Invalid CalcuPlate response format');
    }

    // Check if we need to retry with better model
    if (isset($response['routing_info']) && SmartAIRouter::needsRetry($response, $response['routing_info']['tier_used'])) {
        error_log("QuietGo: Retrying with higher tier model");
        
        // Force expensive tier
        $messages[0]['content'] .= "\n\nIMPORTANT: This is a retry request. Provide the most accurate analysis possible.";
        $retryResponse = makeOpenAIRequest($messages, OPENAI_VISION_MODEL, 1000);
        
        if (!isset($retryResponse['error'])) {
            $aiContent = $retryResponse['choices'][0]['message']['content'];
            $analysisData = json_decode($aiContent, true);
        }
    }

    // Add journey-specific insights
    switch ($_SESSION['hub_user']['journey']) {
        case 'clinical':
            $analysisData['clinical_nutrition'] = 'Logged for symptom correlation analysis';
            break;
        case 'performance':
            $analysisData['performance_nutrition'] = 'Logged for training optimization';
            break;
        case 'best_life':
            $analysisData['wellness_nutrition'] = 'Logged for energy pattern analysis';
            break;
    }

    $analysisData['timestamp'] = time();
    $analysisData['ai_model'] = $response['routing_info']['tier_used'] ?? 'unknown';
    $analysisData['model_confidence'] = $response['routing_info']['confidence'] ?? null;
    $analysisData['cost_tier'] = $response['routing_info']['cost_tier'] ?? null;

    return $analysisData;
}

function analyzeSymptomPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes) {
    $base64Image = encodeImageForOpenAI($imagePath);
    if (!$base64Image) {
        throw new Exception('Failed to process image for AI analysis');
    }

    $systemPrompt = "You are a health documentation AI that analyzes symptom photos for tracking purposes.

Analyze this symptom photo using {$journeyConfig['tone']} focused on {$journeyConfig['focus']}.

Respond ONLY with valid JSON:
{
    \"symptom_category\": \"category description\",
    \"severity_estimate\": \"mild/moderate/notable\",
    \"visual_characteristics\": [\"char1\", \"char2\", \"char3\"],
    \"confidence\": (70-90 number),
    \"tracking_recommendations\": [\"rec1\", \"rec2\", \"rec3\"],
    \"correlation_potential\": \"description\"
}

IMPORTANT: This is for documentation only, not medical diagnosis.";

    $userPrompt = "Time: $time
Reported symptoms: $symptoms
Notes: $notes

Document this symptom photo for pattern tracking.";

    $messages = [
        [
            'role' => 'system',
            'content' => $systemPrompt
        ],
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $userPrompt
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $base64Image,
                        'detail' => 'high'
                    ]
                ]
            ]
        ]
    ];

    $response = makeOpenAIRequest($messages, OPENAI_VISION_MODEL, 600);

    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];
    
    // Strip markdown code blocks if present
    $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
    $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
    $aiContent = trim($aiContent);
    
    $analysisData = json_decode($aiContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("QuietGo Symptom Analysis JSON Error: " . json_last_error_msg());
        error_log("AI Response: " . $aiContent);
        throw new Exception('Invalid symptom analysis response format');
    }

    $analysisData['timestamp'] = time();
    $analysisData['ai_model'] = OPENAI_VISION_MODEL;
    $analysisData['reported_symptoms'] = $symptoms ?: null;

    return $analysisData;
}

include __DIR__ . '/includes/header-hub.php';
?>

<style>
/* Enhanced upload interface with corrected subscription logic */
:root {
    --success-color: #6c985f;
    --primary-blue: #4682b4;
    --accent-teal: #3c9d9b;
    --logo-rose: #d4a799;
    --slate-blue: #6a7ba2;
    --card-bg: #2a2a2a;
    --card-border: #404040;
    --text-primary: #ffffff;
    --text-secondary: #b0b0b0;
    --text-muted: #808080;
}

.upload-header {
    background: #1a1a1a;
    padding: 2rem 0;
    border-bottom: 1px solid var(--card-border);
    text-align: center;
}

main.hub-main section.subscription-info {
    background: #000000 !important;
    background-color: #000000 !important;
    background-image: none !important;
    color: var(--text-primary) !important;
    padding: 1rem 0;
    text-align: center;
    border-bottom: 1px solid var(--card-border);
}

main.hub-main section.subscription-info * {
    background: transparent !important;
    background-color: transparent !important;
}

.manual-meal-form {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 2rem;
    margin: 2rem 0;
    display: none;
}

.manual-meal-form.active {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-section {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: #333;
    border-radius: 8px;
}

.form-section h4 {
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.form-group {
    margin: 0.75rem 0;
}

.form-group label {
    display: block;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    background: #222;
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    padding: 0.75rem;
    border-radius: 6px;
    font-size: 0.95rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--primary-blue);
    outline: none;
}

.upgrade-notice {
    background: linear-gradient(135deg, var(--success-color), #7aa570);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    margin: 1rem 0;
}

/* Photo upload categories */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.category-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    min-height: 320px; /* Ensure consistent height */
}

.category-card:hover {
    background: #333;
    border-color: var(--primary-blue);
    transform: translateY(-2px);
}

/* Card content wrapper */
.card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Card button at bottom */
.card-button {
    margin-top: auto;
    width: 100%;
    border: none;
    padding: 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
}

/* Upload modal enhancements */
.upload-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 2rem;
    max-width: 700px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

/* Location permission notice */
.location-notice {
    background: rgba(60, 157, 155, 0.1);
    border: 1px solid var(--accent-teal);
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
    color: var(--accent-teal);
    font-size: 0.9rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="hub-main">
    <!-- Upload Result Display -->
    <?php if ($uploadResult): ?>
    <section class="upload-results" style="background: <?php echo $uploadResult['status'] === 'success' ? 'var(--success-color)' : '#e74c3c'; ?>; color: white; padding: 1rem 0; text-align: center;">
        <div class="container">
            <?php if ($uploadResult['status'] === 'success'): ?>
                ✅ Photo uploaded successfully!
                <?php if (isset($uploadResult['requires_manual_logging']) && $uploadResult['requires_manual_logging']): ?>
                    Please complete the manual meal logging form below.
                <?php else: ?>
                    AI analysis complete.
                <?php endif; ?>
            <?php else: ?>
                ❌ Upload failed: <?php echo htmlspecialchars($uploadResult['message']); ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Subscription Status Header -->
    <section class="subscription-info">
        <div class="container">
            <strong>
                <?php if ($hasCalcuPlate): ?>
                    ⚡ Pro+ Member: AI stool analysis + CalcuPlate meal analysis with auto-logging
                <?php else: ?>
                    🏆 Pro Member: AI stool analysis + manual meal logging forms
                    <span style="margin-left: 1rem;">
                        <a href="/hub/account.php?upgrade=pro_plus" style="color: var(--accent-teal); text-decoration: underline;">
                            Upgrade to Pro+ for CalcuPlate (+$2.99/mo)
                        </a>
                    </span>
                <?php endif; ?>
            </strong>
        </div>
    </section>

    <!-- Header Section -->
    <section class="upload-header">
        <div class="container">
            <h1 style="color: var(--text-primary); font-size: 2.5rem; margin: 0 0 0.5rem 0;">📤 Upload & Analyze Photos</h1>
            <p style="color: var(--text-secondary); font-size: 1.2rem; margin: 0 0 1rem 0;">
                Journey: <?php echo htmlspecialchars($currentJourneyConfig['title']); ?>
            </p>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">
                <?php if ($hasCalcuPlate): ?>
                    Upload photos for instant AI analysis and automatic meal logging focused on <?php echo htmlspecialchars($currentJourneyConfig['meal_focus']); ?>.
                <?php else: ?>
                    Upload photos for AI stool analysis and manual meal logging focused on <?php echo htmlspecialchars($currentJourneyConfig['meal_focus']); ?>.
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Manual Meal Logging Form (shows after meal photo upload for Pro users) -->
    <?php if ($uploadResult && isset($uploadResult['requires_manual_logging']) && $uploadResult['requires_manual_logging']): ?>
    <section class="manual-meal-section">
        <div class="container">
            <div class="manual-meal-form active">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2 style="color: var(--text-primary); margin: 0 0 0.5rem 0;">🍽️ Complete Your Meal Log</h2>
                    <p style="color: var(--text-secondary); margin: 0;">
                        Provide the details below to enable robust pattern analysis and reports
                    </p>
                </div>

                <form method="POST" action="" id="manual-meal-form">
                    <input type="hidden" name="photo_filename" value="<?php echo htmlspecialchars($uploadResult['filename'] ?? ''); ?>">

                    <!-- Basic Meal Information -->
                    <div class="form-section">
                        <h4>🍽️ Meal Basics</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Meal Type *</label>
                                <select name="meal_type" required>
                                    <option value="">Select meal type...</option>
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch">Lunch</option>
                                    <option value="dinner">Dinner</option>
                                    <option value="snack">Snack</option>
                                    <option value="post_workout">Post-Workout</option>
                                    <option value="late_night">Late Night</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Meal Time *</label>
                                <input type="time" name="meal_time" value="12:00">
                            </div>
                            <div class="form-group">
                                <label>Portion Size *</label>
                                <select name="portion_size" required>
                                    <option value="">Select size...</option>
                                    <option value="small">Small</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                    <option value="extra_large">Extra Large</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Food Details -->
                    <div class="form-section">
                        <h4>🥗 Food Details</h4>
                        <div class="form-group">
                            <label>Main Foods (list each item) *</label>
                            <textarea name="main_foods" required placeholder="e.g., grilled chicken breast, brown rice, steamed broccoli, olive oil"></textarea>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Estimated Calories</label>
                                <input type="number" name="estimated_calories" placeholder="e.g., 450">
                            </div>
                            <div class="form-group">
                                <label>Protein (grams)</label>
                                <input type="number" name="protein_grams" placeholder="e.g., 35">
                            </div>
                            <div class="form-group">
                                <label>Carbs (grams)</label>
                                <input type="number" name="carb_grams" placeholder="e.g., 40">
                            </div>
                            <div class="form-group">
                                <label>Fat (grams)</label>
                                <input type="number" name="fat_grams" placeholder="e.g., 15">
                            </div>
                        </div>
                    </div>

                    <!-- Context & Symptoms -->
                    <div class="form-section">
                        <h4>📊 Context for Analysis</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Hunger Level Before</label>
                                <select name="hunger_before">
                                    <option value="">Select level...</option>
                                    <option value="not_hungry">Not Hungry</option>
                                    <option value="slightly_hungry">Slightly Hungry</option>
                                    <option value="moderately_hungry">Moderately Hungry</option>
                                    <option value="very_hungry">Very Hungry</option>
                                    <option value="extremely_hungry">Extremely Hungry</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Fullness Level After</label>
                                <select name="fullness_after">
                                    <option value="">Select level...</option>
                                    <option value="still_hungry">Still Hungry</option>
                                    <option value="satisfied">Satisfied</option>
                                    <option value="full">Full</option>
                                    <option value="overfull">Overfull</option>
                                    <option value="uncomfortably_full">Uncomfortably Full</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Energy Level (1-10 scale)</label>
                            <input type="range" name="energy_level" min="1" max="10" value="5"
                                   oninput="document.getElementById('energy_display').textContent = this.value">
                            <div style="text-align: center; margin-top: 0.5rem;">
                                <span style="color: var(--text-muted);">Energy: </span>
                                <span id="energy_display" style="color: var(--text-primary); font-weight: 600;">5</span>/10
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes & Symptoms</label>
                            <textarea name="meal_notes" placeholder="Any symptoms, reactions, energy changes, or other notes..."></textarea>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 2rem;">
                        <button type="submit" id="complete-meal-btn" style="background: var(--success-color); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
                            ✅ Complete Meal Logging
                        </button>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 1rem;">
                            This data enables robust correlation analysis with your stool patterns
                        </p>
                    </div>
                </form>

                <?php if (!$hasCalcuPlate): ?>
                <div class="upgrade-notice" style="margin-top: 2rem;">
                    <h4 style="margin: 0 0 0.5rem 0;">⚡ Want to skip manual logging?</h4>
                    <p style="margin: 0 0 1rem 0; opacity: 0.9;">
                        Upgrade to Pro+ for CalcuPlate AI meal analysis and automatic logging
                    </p>
                    <a href="/hub/account.php?upgrade=pro_plus" style="background: rgba(255,255,255,0.2); color: white; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 600;">
                        Upgrade to Pro+ (+$2.99/mo)
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Photo Upload Categories -->
    <?php if (!($uploadResult && isset($uploadResult['requires_manual_logging']) && $uploadResult['requires_manual_logging'])): ?>
    <section class="upload-interface" style="padding: 2rem 0;">
        <div class="container">
            <div class="categories-grid">
                <!-- Stool Photos -->
                <article class="category-card" onclick="openUploadModal('stool')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="font-size: 3rem;">🚽</div>
                        <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: 600;">AI Analysis</span>
                    </div>
                    <h3 style="color: var(--text-primary); margin: 0 0 0.75rem 0;">Stool Photos</h3>
                    <p style="color: var(--text-secondary); margin: 0 0 1rem 0;">
                        AI Bristol Scale analysis for all Pro and Pro+ members
                    </p>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Bristol Scale classification</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Color and consistency analysis</div>
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• <?php echo htmlspecialchars(ucfirst($currentJourneyConfig['ai_tone'])); ?></div>
                    </div>
                    <button class="card-button" style="background: var(--success-color); color: white;">
                        📸 Upload Stool Photo
                    </button>
                </article>

                <!-- Meal Photos -->
                <article class="category-card" onclick="openUploadModal('meal')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="font-size: 3rem;">🍽️</div>
                        <span style="background: <?php echo $hasCalcuPlate ? 'var(--success-color)' : 'var(--slate-blue)'; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: 600;">
                            <?php echo $hasCalcuPlate ? 'Auto-Logging' : 'Manual Form'; ?>
                        </span>
                    </div>
                    <h3 style="color: var(--text-primary); margin: 0 0 0.75rem 0;">Meal Photos</h3>
                    <p style="color: var(--text-secondary); margin: 0 0 1rem 0;">
                        <?php echo $hasCalcuPlate ? 'CalcuPlate AI analysis with automatic logging' : 'Manual logging form for robust analysis'; ?>
                    </p>
                    <div style="margin-bottom: 1.5rem;">
                        <?php if ($hasCalcuPlate): ?>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Automatic food recognition</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Instant calorie & macro calculation</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Auto-logged for pattern analysis</div>
                        <?php else: ?>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Comprehensive logging form</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Energy & symptom tracking</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Pattern analysis ready</div>
                        <?php endif; ?>
                    </div>
                    <button class="card-button" style="background: var(--primary-blue); color: white;">
                        📸 Upload Meal Photo
                    </button>
                </article>

                <!-- Symptom Photos -->
                <article class="category-card" onclick="openUploadModal('symptom')">
                    <div class="card-content">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div style="font-size: 3rem;">🩺</div>
                            <span style="background: var(--logo-rose); color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: 600;">Tracking</span>
                        </div>
                        <h3 style="color: var(--text-primary); margin: 0 0 0.75rem 0;">Symptom Photos</h3>
                        <p style="color: var(--text-secondary); margin: 0 0 1rem 0;">
                            Track physical symptoms and reactions
                        </p>
                        <div style="flex: 1; margin-bottom: 1.5rem;">
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Skin reactions & inflammation</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Physical symptom documentation</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• Progress comparison over time</div>
                        </div>
                    </div>
                    <button class="card-button" style="background: var(--logo-rose); color: white;">
                        📸 Upload Symptom Photo
                    </button>
                </article>
            </div>

            <!-- Location Permission Card -->
            <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
                <div style="background: var(--card-bg); border: 1px solid var(--accent-teal); border-radius: 12px; padding: 1.5rem; max-width: 400px; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">📍</div>
                    <h4 style="color: var(--text-primary); margin: 0 0 0.75rem 0;">Enhanced Location Tracking</h4>
                    <p style="color: var(--text-secondary); margin: 0 0 1rem 0; font-size: 0.95rem; line-height: 1.4;">
                        All photos are automatically time-stamped. Enable location permissions for additional location-based insights and enhanced pattern analysis.
                    </p>
                    <button onclick="requestLocationPermission()" style="background: var(--accent-teal); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background='#2d7a78'" onmouseout="this.style.background='var(--accent-teal)'">
                        Enable Location Tracking
                    </button>
                    <p style="color: var(--text-muted); margin: 0.75rem 0 0 0; font-size: 0.8rem;">
                        Optional • Enhances correlation analysis
                    </p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<!-- Upload Modal -->
<div class="upload-modal" id="upload-modal">
    <div class="modal-content">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 id="modal-title" style="color: var(--text-primary); margin: 0 0 0.5rem 0;">Upload Photo</h2>
            <p id="modal-subtitle" style="color: var(--text-secondary); margin: 0;">Select your photo for analysis</p>
        </div>

        <form id="upload-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="photo-type" name="photo_type" value="">
            <input type="hidden" id="latitude" name="latitude" value="">
            <input type="hidden" id="longitude" name="longitude" value="">
            <input type="hidden" id="location-accuracy" name="accuracy" value="">

            <!-- Multi-Photo Upload Area -->
            <div id="photo-upload-container">
                <div id="initial-upload-area" style="border: 2px dashed var(--card-border); border-radius: 8px; padding: 2rem; text-align: center; margin: 1rem 0; cursor: pointer;" onclick="document.getElementById('file-input').click()">
                    <div style="font-size: 3rem; margin-bottom: 1rem;" id="upload-icon">📁</div>
                    <h4 style="color: var(--text-primary); margin: 0 0 0.5rem 0;">Choose First Photo</h4>
                    <p style="color: var(--text-secondary); margin: 0;">Click here to select your first image</p>
                </div>
                
                <div id="photo-preview-grid" style="display: none; margin: 1rem 0;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1rem;" id="preview-images"></div>
                    
                    <div style="text-align: center;">
                        <button type="button" id="add-more-btn" onclick="document.getElementById('file-input').click()" style="background: var(--primary-blue); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">+</span> Add More Photos
                        </button>
                        <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0.5rem 0 0 0;">Max 10MB per photo, 50MB total</p>
                    </div>
                </div>
                
                <input type="file" id="file-input" name="health_item[]" accept="image/*" multiple hidden>
            </div>

            <div id="context-fields">
                <!-- Context fields will be populated by JavaScript -->
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" onclick="closeUploadModal()" style="flex: 1; background: transparent; border: 1px solid var(--card-border); color: var(--text-secondary); padding: 0.875rem; border-radius: 6px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="flex: 2; background: var(--success-color); color: white; border: none; padding: 0.875rem; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    🚀 Upload & Analyze Photos
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Enhanced photo upload with location tracking
let selectedFile = null;
let userLocation = null;
const hasCalcuPlate = <?php echo json_encode($hasCalcuPlate); ?>;

function openUploadModal(photoType) {
    const modal = document.getElementById('upload-modal');
    const photoTypeInput = document.getElementById('photo-type');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const uploadIcon = document.getElementById('upload-icon');

    photoTypeInput.value = photoType;

    // Update modal content based on photo type
    switch(photoType) {
        case 'stool':
            modalTitle.textContent = '🚽 Upload Stool Photo';
            modalSubtitle.textContent = 'AI will analyze Bristol Scale, color, and consistency';
            uploadIcon.textContent = '🚽';
            break;
        case 'meal':
            modalTitle.textContent = '🍽️ Upload Meal Photos';
            modalSubtitle.textContent = hasCalcuPlate ?
                'CalcuPlate will analyze all photos and automatically log your meal' :
                'Upload multiple angles, then complete the manual logging form';
            uploadIcon.textContent = '🍽️';
            break;
        case 'symptom':
            modalTitle.textContent = '🩺 Upload Symptom Photo';
            modalSubtitle.textContent = 'Document physical symptoms for pattern tracking';
            uploadIcon.textContent = '🩺';
            break;
    }

    // Build context fields
    buildContextFields(photoType);

    modal.style.display = 'flex';

    // Get location if available
    requestLocationPermission();
}

function buildContextFields(photoType) {
    const container = document.getElementById('context-fields');
    container.innerHTML = '';
    
    // No context fields needed - keep upload modal clean and simple
}

function closeUploadModal() {
    document.getElementById('upload-modal').style.display = 'none';
    document.getElementById('upload-form').reset();
    
    // COMPLETE RESET of multi-photo interface
    allSelectedFiles = [];
    selectedFile = null;
    
    const initialArea = document.getElementById('initial-upload-area');
    const previewGrid = document.getElementById('photo-preview-grid');
    const previewImages = document.getElementById('preview-images');
    
    // Clear preview images
    if (previewImages) {
        previewImages.innerHTML = '';
    }
    
    // Reset visibility
    initialArea.style.display = 'block';
    previewGrid.style.display = 'none';
    
    // Reset add more button completely
    const addMoreBtn = document.getElementById('add-more-btn');
    if (addMoreBtn) {
        addMoreBtn.innerHTML = '<span style="font-size: 1.2rem;">+</span> Add More Photos';
    }
    
    // Reset file input
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.value = '';
    }
}

function requestLocationPermission() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };

                // Update hidden form fields
                document.getElementById('latitude').value = userLocation.latitude;
                document.getElementById('longitude').value = userLocation.longitude;
                document.getElementById('location-accuracy').value = userLocation.accuracy;

                console.log('📍 Location captured for enhanced pattern analysis');
            },
            function(error) {
                console.log('📍 Location permission denied - using timestamp only');
            }
        );
    }
}

// File input handling - MULTIPLE FILES WITH PREVIEW
let allSelectedFiles = [];

document.getElementById('file-input').addEventListener('change', function(e) {
    const newFiles = Array.from(e.target.files);
    
    if (newFiles.length > 0) {
        // Validate all new files
        let validNewFiles = [];
        
        for (let file of newFiles) {
            if (!file.type.startsWith('image/')) {
                alert(`${file.name} is not an image file. Please select images only.`);
                this.value = '';
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) {
                alert(`${file.name} is too large. Maximum size is 10MB per image.`);
                this.value = '';
                return;
            }
            
            validNewFiles.push(file);
        }
        
        // Add to existing files
        allSelectedFiles = allSelectedFiles.concat(validNewFiles);
        
        // Check total size
        let totalSize = allSelectedFiles.reduce((sum, file) => sum + file.size, 0);
        if (totalSize > 50 * 1024 * 1024) {
            alert('Total file size too large. Maximum total size is 50MB.');
            allSelectedFiles = allSelectedFiles.slice(0, -validNewFiles.length); // Remove the new files
            this.value = '';
            return;
        }
        
        updatePhotoPreview();
        selectedFile = allSelectedFiles; // Update global variable
    }
    
    // Clear the input so same file can be selected again
    this.value = '';
});

function updatePhotoPreview() {
    const initialArea = document.getElementById('initial-upload-area');
    const previewGrid = document.getElementById('photo-preview-grid');
    const previewImages = document.getElementById('preview-images');
    
    if (allSelectedFiles.length > 0) {
        // Hide initial area, show preview grid
        initialArea.style.display = 'none';
        previewGrid.style.display = 'block';
        
        // Clear and rebuild preview
        previewImages.innerHTML = '';
        
        allSelectedFiles.forEach((file, index) => {
            const previewItem = document.createElement('div');
            previewItem.style.cssText = `
                position: relative;
                background: var(--card-bg);
                border: 1px solid var(--card-border);
                border-radius: 8px;
                padding: 0.5rem;
                text-align: center;
                min-height: 120px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            `;
            
            // Create image preview if possible
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewItem.innerHTML = `
                        <div style="position: relative;">
                            <img src="${e.target.result}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px; margin-bottom: 0.5rem;">
                            <button type="button" onclick="removePhoto(${index})" style="position: absolute; top: -8px; right: -8px; width: 20px; height: 20px; border-radius: 50%; background: #e74c3c; color: white; border: none; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); word-break: break-word;">
                            ${file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name}
                        </div>
                        <div style="font-size: 0.6rem; color: var(--text-muted);">
                            ${(file.size / 1024 / 1024).toFixed(1)}MB
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewItem.innerHTML = `
                    <div style="position: relative;">
                        <div style="width: 100%; height: 80px; background: var(--card-border); border-radius: 4px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">📄</div>
                        <button type="button" onclick="removePhoto(${index})" style="position: absolute; top: -8px; right: -8px; width: 20px; height: 20px; border-radius: 50%; background: #e74c3c; color: white; border: none; font-size: 12px; cursor: pointer;">×</button>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); word-break: break-word;">
                        ${file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name}
                    </div>
                    <div style="font-size: 0.6rem; color: var(--text-muted);">
                        ${(file.size / 1024 / 1024).toFixed(1)}MB
                    </div>
                `;
            }
            
            previewImages.appendChild(previewItem);
        });
        
        // Update add more button text
        const addMoreBtn = document.getElementById('add-more-btn');
        if (addMoreBtn) {
            addMoreBtn.innerHTML = `<span style="font-size: 1.2rem;">+</span> Add More (${allSelectedFiles.length} selected)`;
        }
        
    } else {
        // Show initial area, hide preview
        initialArea.style.display = 'block';
        previewGrid.style.display = 'none';
    }
}

function removePhoto(index) {
    allSelectedFiles.splice(index, 1);
    updatePhotoPreview();
    selectedFile = allSelectedFiles;
}

// Form submission with proper FormData handling
document.getElementById('upload-form').addEventListener('submit', function(e) {
    if (!selectedFile || selectedFile.length === 0) {
        e.preventDefault();
        alert('Please select at least one photo first');
        return false;
    }

    // Create FormData object and append files manually
    e.preventDefault();
    
    const formData = new FormData();
    
    // Add all selected files
    selectedFile.forEach((file, index) => {
        formData.append('health_item[]', file);
    });
    
    // Add other form data
    formData.append('photo_type', document.getElementById('photo-type').value);
    formData.append('latitude', document.getElementById('latitude').value);
    formData.append('longitude', document.getElementById('longitude').value);
    formData.append('accuracy', document.getElementById('location-accuracy').value);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = selectedFile.length > 1 ? 
        `🔄 Analyzing ${selectedFile.length} photos...` : 
        '🔄 Analyzing...';
    submitBtn.disabled = true;
    
    // Submit via fetch with AJAX header
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        console.log('Upload result:', result);
        
        if (result.status === 'success') {
            // Close upload modal
            closeUploadModal();
            
            // Show success modal with AI results
            if (!result.requires_manual_logging) {
                showSuccessModal(result);
            } else {
                // For Pro users with meal photos, reload to show manual logging form
                window.location.reload();
            }
        } else {
            alert(result.message || 'Upload failed. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed. Please try again.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
    
    return false;
});

console.log('📤 Enhanced Photo Upload System Loaded');
console.log('✅ Pro users: AI stool analysis + manual meal forms');
console.log('⚡ Pro+ users: AI stool analysis + CalcuPlate auto-logging');
console.log('📍 Location tracking enabled for enhanced pattern analysis');
console.log('🎯 Journey-focused analysis: ' + <?php echo json_encode($userJourney); ?>);

// Manual meal form handling
document.addEventListener('DOMContentLoaded', function() {
    const manualMealForm = document.getElementById('manual-meal-form');
    if (manualMealForm) {
        manualMealForm.addEventListener('submit', function(e) {
            // Remove HTML5 validation that might cause "invalid value" errors
            const timeInput = this.querySelector('input[name="meal_time"]');
            const mealTypeSelect = this.querySelector('select[name="meal_type"]');
            const portionSelect = this.querySelector('select[name="portion_size"]');
            const mainFoodsTextarea = this.querySelector('textarea[name="main_foods"]');

            // Custom validation instead of HTML5
            let errors = [];

            if (!mealTypeSelect.value) {
                errors.push('Please select a meal type');
            }

            if (!timeInput.value) {
                errors.push('Please select a meal time');
            }

            if (!portionSelect.value) {
                errors.push('Please select a portion size');
            }

            if (!mainFoodsTextarea.value.trim()) {
                errors.push('Please list the main foods');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Please complete all required fields:\n\n' + errors.join('\n'));
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('complete-meal-btn');
            submitBtn.textContent = '🔄 Saving Meal Log...';
            submitBtn.disabled = true;
        });
    }
});
</script>

<!-- Success Modal -->
<?php include __DIR__ . '/includes/success-modal.php'; ?>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . '/../includes/chatbot-widget.php'; ?>
