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

    $systemPrompt = "You are CalcuPlate, a professional meal analysis AI that uses a multi-pass algorithm for accurate nutritional tracking.

⚠️ VALIDATION STEP: Verify this is food/beverage. If not, respond: {\"error\": \"not_food\", \"message\": \"Not a food/beverage photo\"}

🧠 MULTI-PASS ANALYSIS ALGORITHM:

━━━ PASS 1: DETECTION & SEGMENTATION ━━━
• Identify ALL distinct items (food, beverages, condiments, desserts)
• Count exact quantities (distinguish between roll slices vs individual pieces)
• Note spatial relationships (what's on same plate vs separate)

━━━ PASS 2: CLASSIFICATION & RECOGNITION ━━━
SUSHI DISTINCTION IS CRITICAL:
• Roll slices: 6-8 connected pieces = 1 roll = 200-400 cal total
• Nigiri: Individual fish on rice = 50-100 cal EACH piece  
• Sashimi: Just fish, no rice = 25-40 cal per piece
• Mixed plates: Identify each type separately

RESTAURANT RECOGNITION:
• Chain restaurants: Match exact items (McDonald's Big Mac = 563 cal)
• Generic items: Use category averages (\"burger\" = 500-700 cal)
• Homemade: Estimate 20% fewer calories than restaurant version

━━━ PASS 3: VOLUME & PORTION ESTIMATION ━━━
• Use relative sizing (compare to plate, utensils, standard items)
• Apply depth/perspective cues
• Standard portions: 12oz can, 16oz grande, 8\" plate, etc.

🎯 CONSERVATIVE ESTIMATION RULES:
• Unknown beverage → Assume regular (not diet)
• Sauce on side → Assume 50% consumed
• Cooking method unclear → Assume higher calorie method
• Portion ambiguous → Use larger estimate
• Condiment packets visible → Ask if used
• Multiple images → Could be same meal, check timestamps

📊 MULTI-IMAGE MEAL AGGREGATION:
If this appears to be part of a meal set (beverage or dessert separate from main):
• Flag as \"meal_component\" with type (main/beverage/dessert/side)
• Note: \"Appears to be [beverage/dessert/side] for meal\"
• Include partial totals and grand total

For {$journeyConfig['focus']} analysis with {$journeyConfig['tone']}:

If confidence < 85% OR ambiguous items, respond:
{
    \"needs_clarification\": true,
    \"detected_items\": {
        \"confirmed\": [\"8x salmon nigiri (individual pieces, ~640 cal)\"],
        \"ambiguous\": [\"Dark beverage - cola or coffee?\"],
        \"condiments\": [\"Soy sauce packet (unopened)\"]
    },
    \"questions\": [
        {
            \"item\": \"Dark beverage\",
            \"question\": \"What is this beverage?\",
            \"options\": [\"Coke (140 cal)\", \"Diet Coke (0 cal)\", \"Coffee (5 cal)\"],
            \"impact\": \"0-140 calories\"
        }
    ],
    \"preliminary_totals\": {
        \"confirmed_calories\": 640,
        \"estimated_range\": \"640-780\",
        \"confidence\": 75
    }
}

For HIGH CONFIDENCE (85%+), respond:
{
    \"calcuplate\": {
        \"analysis_type\": \"complete_meal\" or \"meal_component\",
        \"meal_component_type\": \"main/beverage/dessert/side\" (if applicable),
        \"items_detected\": [
            {\"item\": \"Salmon nigiri\", \"quantity\": 4, \"calories\": 320, \"type\": \"sushi_nigiri\"},
            {\"item\": \"Tuna nigiri\", \"quantity\": 4, \"calories\": 320, \"type\": \"sushi_nigiri\"},
            {\"item\": \"Coca-Cola\", \"quantity\": \"12oz can\", \"calories\": 140, \"type\": \"beverage\"}
        ],
        \"condiments_analysis\": {
            \"used\": [\"Soy sauce (2 tbsp, 20 cal, 1840mg sodium)\"],
            \"unused\": [\"Wasabi packet\"],
            \"uncertain\": []
        },
        \"totals\": {
            \"calories\": 800,
            \"protein_g\": 56,
            \"carbs_g\": 84,
            \"fat_g\": 16,
            \"fiber_g\": 2,
            \"sodium_mg\": 2400
        },
        \"portion_assessment\": \"8 individual nigiri pieces + 12oz beverage\",
        \"confidence\": 92,
        \"data_source\": \"Generic sushi database\" or \"Restaurant: [name]\"
    },
    \"insights\": [\"High protein meal\", \"Moderate sodium from soy sauce\"],
    \"meal_timing_note\": \"Log as single meal if photos within 30 minutes\"
}

REMEMBER: Users pay for ACCURACY. Count correctly, identify types properly, include everything.";


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
        
        // Log detected items
        if (isset($analysisData['detected_items'])) {
            error_log(" - Confirmed: " . json_encode($analysisData['detected_items']['confirmed'] ?? []));
            error_log(" - Ambiguous: " . json_encode($analysisData['detected_items']['ambiguous'] ?? []));
        }
        
        // Log questions
        if (isset($analysisData['questions'])) {
            foreach ($analysisData['questions'] as $q) {
                error_log(" - Question: {$q['question']} (Impact: {$q['impact']})");
            }
        }
        
        // For now, use preliminary totals with conservative estimates
        // TODO: Future enhancement - trigger clarification modal in UI
        $analysisData['clarification_needed'] = true;
        $analysisData['user_message'] = 'Some items need clarification. Using higher estimates for now.';
        
        // Build analysis from preliminary data
        if (isset($analysisData['preliminary_totals'])) {
            $preliminaryTotals = $analysisData['preliminary_totals'];
            $detectedItems = $analysisData['detected_items'] ?? [];
            
            // Use the higher estimate from range
            $calorieRange = $preliminaryTotals['estimated_range'] ?? '0';
            preg_match('/(\d+)-(\d+)/', $calorieRange, $matches);
            $higherCalories = isset($matches[2]) ? intval($matches[2]) : intval($preliminaryTotals['confirmed_calories'] ?? 0);
            
            // Build food list from detected items
            $foods = array_merge(
                $detectedItems['confirmed'] ?? [],
                $detectedItems['ambiguous'] ?? []
            );
            
            $analysisData['calcuplate'] = [
                'analysis_type' => 'needs_clarification',
                'items_detected' => [], // Would be populated after clarification
                'foods_detected' => $foods, // Legacy format for compatibility
                'total_calories' => $higherCalories,
                'condiments_analysis' => [
                    'uncertain' => $detectedItems['condiments'] ?? []
                ],
                'totals' => [
                    'calories' => $higherCalories,
                    'protein_g' => 'TBD',
                    'carbs_g' => 'TBD',
                    'fat_g' => 'TBD',
                    'sodium_mg' => 'TBD'
                ],
                'confidence' => $preliminaryTotals['confidence'] ?? 75
            ];
            
            $analysisData['confidence'] = $preliminaryTotals['confidence'] ?? 75;
            $analysisData['nutrition_insights'] = ['Some items need confirmation for accurate tracking'];
            $analysisData['recommendations'] = ['Clarify ambiguous items for better analysis'];
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
        error_log("QuietGo CalcuPlate Analysis Complete:");
        error_log(" - Analysis Type: " . ($analysisData['calcuplate']['analysis_type'] ?? 'unknown'));
        
        // Log detailed items if available
        if (isset($analysisData['calcuplate']['items_detected'])) {
            foreach ($analysisData['calcuplate']['items_detected'] as $item) {
                error_log("   • {$item['quantity']}x {$item['item']}: {$item['calories']} cal ({$item['type']})");
            }
        } else {
            // Fallback to legacy format
            error_log(" - Foods: " . json_encode($analysisData['calcuplate']['foods_detected'] ?? []));
        }
        
        // Log condiments analysis
        if (isset($analysisData['calcuplate']['condiments_analysis'])) {
            $condiments = $analysisData['calcuplate']['condiments_analysis'];
            if (!empty($condiments['used'])) {
                error_log(" - Condiments Used: " . json_encode($condiments['used']));
            }
            if (!empty($condiments['unused'])) {
                error_log(" - Condiments Unused: " . json_encode($condiments['unused']));
            }
            if (!empty($condiments['uncertain'])) {
                error_log(" - Condiments Uncertain: " . json_encode($condiments['uncertain']));
            }
        }
        
        // Log totals
        if (isset($analysisData['calcuplate']['totals'])) {
            $totals = $analysisData['calcuplate']['totals'];
            error_log(" - Total Calories: " . $totals['calories']);
            error_log(" - Macros: P:{$totals['protein_g']}g C:{$totals['carbs_g']}g F:{$totals['fat_g']}g");
            if (isset($totals['sodium_mg'])) {
                error_log(" - Sodium: {$totals['sodium_mg']}mg");
            }
        } else {
            // Fallback to legacy format
            error_log(" - Total Calories: " . ($analysisData['calcuplate']['total_calories'] ?? 'N/A'));
        }
        
        error_log(" - Confidence: " . ($analysisData['confidence'] ?? $analysisData['calcuplate']['confidence'] ?? 'N/A'));
        error_log(" - Data Source: " . ($analysisData['calcuplate']['data_source'] ?? 'Generic database'));
    }
    
    // Ensure backward compatibility with UI
    if (isset($analysisData['calcuplate'])) {
        // Make sure legacy fields exist for UI compatibility
        if (!isset($analysisData['calcuplate']['auto_logged'])) {
            $analysisData['calcuplate']['auto_logged'] = true;
        }
        
        // Convert new totals format to legacy macros format if needed
        if (isset($analysisData['calcuplate']['totals']) && !isset($analysisData['calcuplate']['macros'])) {
            $totals = $analysisData['calcuplate']['totals'];
            $analysisData['calcuplate']['macros'] = [
                'protein' => $totals['protein_g'] . 'g',
                'carbs' => $totals['carbs_g'] . 'g',
                'fat' => $totals['fat_g'] . 'g',
                'fiber' => ($totals['fiber_g'] ?? 0) . 'g',
                'sodium' => ($totals['sodium_mg'] ?? 0) . 'mg'
            ];
        }
        
        // Ensure total_calories exists for legacy UI
        if (!isset($analysisData['calcuplate']['total_calories']) && isset($analysisData['calcuplate']['totals']['calories'])) {
            $analysisData['calcuplate']['total_calories'] = $analysisData['calcuplate']['totals']['calories'];
        }
        
        // Build legacy foods_detected from items_detected if needed
        if (!isset($analysisData['calcuplate']['foods_detected']) && isset($analysisData['calcuplate']['items_detected'])) {
            $foods = [];
            foreach ($analysisData['calcuplate']['items_detected'] as $item) {
                $foods[] = "{$item['quantity']}x {$item['item']}";
            }
            $analysisData['calcuplate']['foods_detected'] = $foods;
        }
        
        // Add default quality metrics if missing
        if (!isset($analysisData['calcuplate']['meal_quality_score'])) {
            $analysisData['calcuplate']['meal_quality_score'] = '7/10';
        }
        if (!isset($analysisData['calcuplate']['nutritional_completeness'])) {
            $analysisData['calcuplate']['nutritional_completeness'] = '75%';
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

    // Map new insights field to legacy nutrition_insights if needed
    if (!isset($analysisData['nutrition_insights']) && isset($analysisData['insights'])) {
        $analysisData['nutrition_insights'] = $analysisData['insights'];
    }
    
    // Ensure recommendations exist
    if (!isset($analysisData['recommendations']) || empty($analysisData['recommendations'])) {
        $analysisData['recommendations'] = [
            'Stay hydrated with water between meals',
            'Consider adding more vegetables for fiber',
            'Track patterns over time for best insights'
        ];
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
