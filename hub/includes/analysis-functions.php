<?php
/**
 * QuietGo AI Analysis Functions
 * Contains stool, meal, and symptom analysis functions
 */

/**
 * Analyze Stool Photo using Bristol Stool Scale
 */
function analyzeStoolPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes) {
    error_log("QuietGo: analyzeStoolPhoto called for: $imagePath");
    
    $base64Image = encodeImageForOpenAI($imagePath);
    if (!$base64Image) {
        error_log("QuietGo ERROR: Failed to encode image for stool analysis");
        throw new Exception('Failed to process image for AI analysis');
    }
    
    error_log("QuietGo: Image encoded successfully, preparing AI request");

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

    error_log("QuietGo: OpenAI response received. Has error: " . (isset($response['error']) ? 'YES - ' . $response['error'] : 'NO'));

    if (isset($response['error'])) {
        error_log("QuietGo ERROR: " . $response['error']);
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];
    error_log("QuietGo: AI content length: " . strlen($aiContent));
    
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
    
    error_log("QuietGo: Stool analysis parsed successfully. Bristol: " . ($analysisData['bristol_scale'] ?? 'NULL') . ", Confidence: " . ($analysisData['confidence'] ?? 'NULL'));

    // Add metadata
    $analysisData['timestamp'] = time();
    $analysisData['ai_model'] = OPENAI_VISION_MODEL;
    $analysisData['reported_symptoms'] = $symptoms ?: null;
    $analysisData['correlation_note'] = $symptoms ? 'Symptoms logged for pattern analysis' : null;

    return $analysisData;
}

/**
 * Analyze Meal Photo with CalcuPlate AI
 */
function analyzeMealPhotoWithCalcuPlate($imagePath, $journeyConfig, $symptoms, $time, $notes) {
    // Include smart router for multi-model cost optimization
    require_once __DIR__ . '/smart-ai-router.php';
    
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
            $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
            $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
            $aiContent = trim($aiContent);
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

/**
 * Analyze Symptom Photo for tracking
 */
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

/**
 * Handle Manual Meal Logging
 */
function handleManualMealLogging($postData, $user) {
    // Include storage helper
    require_once __DIR__ . '/storage-helper.php';
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
?>
