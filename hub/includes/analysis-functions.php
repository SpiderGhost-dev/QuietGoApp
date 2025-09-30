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

⚠️ CRITICAL FIRST STEP: Verify this is actually a food/meal photo. If the image shows:
- Biological waste (stool, urine, vomit)
- Medical imagery (wounds, rashes, symptoms)
- Non-food items
- Inappropriate content

Respond with: {\"error\": \"not_food\", \"message\": \"This doesn't appear to be a meal photo\"}

📊 ACCURACY REQUIREMENTS FOR FOOD ANALYSIS:
1. COUNT EVERYTHING: You MUST count ALL visible items (e.g., if you see 8 pieces of sushi, count ALL 8, not just 1)
2. PORTION MULTIPLIER: If multiple servings are visible, multiply nutritional values accordingly
3. BE CONSERVATIVE: When uncertain, estimate HIGHER calories rather than lower (better to overestimate)
4. IDENTIFY AMBIGUITY: If you cannot determine specifics (e.g., regular vs diet soda, dressing on/off salad), flag for clarification

For valid food photos, analyze using {$journeyConfig['tone']} focused on {$journeyConfig['focus']}.

If confidence is below 90% OR there are ambiguous items (beverages that could be diet/regular, sauces, dressings), respond with:
{
    \"needs_clarification\": true,
    \"questions\": [
        {
            \"item\": \"beverage name\",
            \"question\": \"Is this regular or diet/zero calorie?\",
            \"options\": [\"Regular (X cal)\", \"Diet/Zero (0 cal)\"],
            \"impact\": \"affects total calories\"
        }
    ],
    \"preliminary_analysis\": {
        \"foods_detected\": [\"list with quantities: 8x sushi rolls\", \"1x miso soup\"],
        \"estimated_calories_range\": \"700-900\",
        \"confidence\": (number)
    }
}

For HIGH CONFIDENCE (90%+) analysis, respond with:
{
    \"calcuplate\": {
        \"auto_logged\": true,
        \"foods_detected\": [\"INCLUDE QUANTITIES: 8x california rolls\", \"1x miso soup\", \"2x gyoza\"],
        \"item_count\": \"total number of individual food items\",
        \"total_calories\": (BE ACCURATE - e.g., 8 sushi pieces = 700-900 cal, not 350),
        \"macros\": {
            \"protein\": \"XXg\",
            \"carbs\": \"XXg\",
            \"fat\": \"XXg\",
            \"fiber\": \"XXg\"
        },
        \"meal_quality_score\": \"X/10\",
        \"portion_sizes\": \"Be specific: large plate, 8 pieces, 16oz drink, etc\",
        \"nutritional_completeness\": \"XX%\"
    },
    \"confidence\": (90-95 for high confidence only),
    \"nutrition_insights\": [\"insight1\", \"insight2\", \"insight3\"],
    \"recommendations\": [\"rec1\", \"rec2\", \"rec3\"]
}

REMEMBER: Users are PAYING for accuracy. Count ALL items. 1 sushi = ~85-115 cal, so 8 = 680-920 cal!";

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
    
    // Check if AI detected non-food content
    if (isset($analysisData['error']) && $analysisData['error'] === 'not_food') {
        error_log("QuietGo CalcuPlate: Non-food image detected");
        throw new Exception('This doesn\'t appear to be a meal photo. Please upload a photo of food, or use the Stool or Symptom upload options for other types of health tracking.');
    }
    
    // Check if AI needs clarification for ambiguous items
    if (isset($analysisData['needs_clarification']) && $analysisData['needs_clarification'] === true) {
        error_log("QuietGo CalcuPlate: Needs clarification for ambiguous items");
        // For now, we'll return the preliminary analysis with a note
        // In the future, this should trigger a clarification modal in the UI
        $analysisData['clarification_needed'] = true;
        $analysisData['user_message'] = 'CalcuPlate detected some ambiguous items. Using estimated values for now. Future updates will allow you to clarify specifics.';
        
        // Use the preliminary analysis as the main analysis for now
        if (isset($analysisData['preliminary_analysis'])) {
            $preliminaryData = $analysisData['preliminary_analysis'];
            $analysisData['calcuplate'] = [
                'auto_logged' => true,
                'foods_detected' => $preliminaryData['foods_detected'] ?? [],
                'total_calories' => intval(explode('-', $preliminaryData['estimated_calories_range'])[1] ?? 0), // Use higher estimate
                'macros' => [
                    'protein' => 'Estimated',
                    'carbs' => 'Estimated', 
                    'fat' => 'Estimated',
                    'fiber' => 'Estimated'
                ],
                'meal_quality_score' => 'Pending clarification',
                'portion_sizes' => 'Needs confirmation',
                'nutritional_completeness' => 'Estimated'
            ];
            $analysisData['confidence'] = $preliminaryData['confidence'] ?? 75;
            $analysisData['nutrition_insights'] = ['Clarification needed for accurate analysis'];
            $analysisData['recommendations'] = ['Please confirm ambiguous items for better tracking'];
        }
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

    // Log CalcuPlate detection for debugging
    if (isset($analysisData['calcuplate'])) {
        error_log("QuietGo CalcuPlate Detection:");
        error_log(" - Foods: " . json_encode($analysisData['calcuplate']['foods_detected']));
        error_log(" - Item Count: " . ($analysisData['calcuplate']['item_count'] ?? 'N/A'));
        error_log(" - Total Calories: " . $analysisData['calcuplate']['total_calories']);
        error_log(" - Confidence: " . $analysisData['confidence']);
        error_log(" - Portion Sizes: " . $analysisData['calcuplate']['portion_sizes']);
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
