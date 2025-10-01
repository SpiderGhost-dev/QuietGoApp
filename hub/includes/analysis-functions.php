<?php
/**
 * QuietGo AI Analysis Functions
 * Contains stool, meal, and symptom analysis functions
 */

// Function already defined in openai-config.php - no need to redeclare

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
    error_log("QuietGo CalcuPlate: Starting analysis for: $imagePath");

    // Try smart router first, but have fallback
    $useSmartRouter = false; // Temporarily disable smart router to isolate issue

    if ($useSmartRouter && file_exists(__DIR__ . '/smart-ai-router.php')) {
        require_once __DIR__ . '/smart-ai-router.php';
    }

    $base64Image = encodeImageForOpenAI($imagePath);
    if (!$base64Image) {
        error_log("QuietGo CalcuPlate ERROR: Failed to encode image");
        throw new Exception('Failed to process image for AI analysis');
    }

    error_log("QuietGo CalcuPlate: Image encoded successfully, preparing prompt");

    $systemPrompt = "You are CalcuPlate, a professional meal analysis AI with preparation-method awareness. You MUST complete a THREE-PASS analysis process.

MANDATORY THREE-PASS ANALYSIS PROCESS:

=== PASS 1: SMART DETECTION & MEASUREMENT ===
Scan the ENTIRE image systematically - check center, edges, background.
Do NOT skip ANY visible food items. For EACH food item:

1. IDENTIFY: What is the food?
2. OBSERVE PREPARATION: How is it prepared/served?
3. CHOOSE MEASUREMENT STRATEGY:

**MEASUREMENT STRATEGY RULES:**

A) COUNT-BASED (for intact, discrete items):
   - Whole eggs (fried/poached/hard-boiled) → Count yolks/whole eggs
   - Whole pieces of meat/fish → Count pieces
   - Cherry tomatoes, strawberries, cookies → Count each one
   - Whole vegetables (carrots, broccoli florets if large) → Count pieces
   
B) PORTION-BASED (for scrambled/chopped/piled foods):
   - Scrambled eggs → Estimate by volume (\"looks like 2-3 eggs worth\")
   - Ground/shredded meat → Estimate weight (\"4 oz\", \"6 oz\")
   - Peas, corn, beans, rice, pasta → Estimate volume (\"1/2 cup\", \"1 cup\")
   - Leafy greens → Volume (\"2 cups\", \"handful\")
   - Chopped vegetables → Volume (\"1/2 cup diced\")
   - Sauces, dressings → Tablespoons or ounces
   - Mashed/pureed foods → Portion size (\"1/2 cup\")

**CRITICAL EXAMPLES:**

EGGS:
- Fried with visible yolks → Count yolks (2 yolks = 2 eggs)
- Scrambled → Estimate portion (\"appears to be 2-3 eggs scrambled\")
- Omelet → Estimate by size (\"3-egg omelet based on thickness\")

MEAT/FISH:
- Whole proteins → Count AND estimate size (\"1 chicken breast, ~6 oz\")
- Sliced/fanned presentation → Count as ONE + size (\"1 salmon fillet, ~5 oz\" NOT \"5 pieces\")
- Multiple separate fillets → Count each with sizes (\"2 chicken breasts, ~4 oz each\")
- Chopped/diced meat → Total weight estimate (\"6 oz chopped chicken\")
- Ground meat → Weight estimate (\"4 oz ground beef\")
- Steak/chops → Count + size (\"1 pork chop, ~5 oz\")
- Sashimi/sushi → Count pieces + estimate per piece
- ALWAYS estimate weight: small (~3-4 oz), medium (~5-6 oz), large (~7-8 oz)

VEGETABLES:
- Whole cherry tomatoes → Count each (\"9 cherry tomatoes\")
- Peas in a pile → Volume (\"1/2 cup peas\")
- Broccoli florets (large) → Count if <10, else estimate (\"5 florets\" or \"1 cup\")
- Spinach → Volume (\"2 cups fresh spinach\")

BEVERAGES:
- Identify type AND size (\"12 oz can Cola\", \"8 oz glass milk\")
- Note if diet/regular/zero matters for calories

Output format for Pass 1:
{
  \"pass_1_detection\": {
    \"eggs\": {\"count\": 2, \"method\": \"counted 2 yolks\"},
    \"salmon\": {\"count\": \"1 piece, ~6 oz\", \"method\": \"1 fillet sliced, estimated weight\"},
    \"cherry_tomatoes\": {\"count\": 7, \"method\": \"counted individually\"},
    \"broccoli\": {\"count\": \"1 cup\", \"method\": \"visual estimate of florets\"},
    \"peas\": {\"count\": \"1/2 cup\", \"method\": \"portion estimate - NOT counting individual peas\"},
    \"spinach\": {\"count\": \"1 cup\", \"method\": \"volume estimate of leaves\"},
    ...
  }
}

CRITICAL: Use \"count\" field for BOTH numeric counts AND portion estimates.
NEVER set count to 0 unless the item truly doesn't exist.
For piled/small foods, ALWAYS estimate portions (\"1/2 cup\", \"1 cup\") NOT individual counts.

=== PASS 2: VERIFICATION & CORRECTION ===
Review your Pass 1 counts. Look at the image AGAIN specifically for:
- Did you count YOLKS for eggs, not just \"an egg\"?
- Are there items partially hidden you missed?
- Did you count touching items separately?
- Check corners and edges of plates

For EACH item from Pass 1, verify or correct:

Output format for Pass 2:
{
  \"pass_2_verification\": {
    \"eggs\": {\"pass_1_count\": X, \"verified_count\": Y, \"changed\": true/false, \"reason\": \"explanation\"},
    \"salmon\": {\"pass_1_count\": X, \"verified_count\": Y, \"changed\": true/false},
    ...
  }
}

=== PASS 3: NUTRITIONAL ANALYSIS ===
Using VERIFIED counts from Pass 2, calculate nutrition.

⚠️ CRITICAL VALIDATION RULE:
You MUST analyze ANY image that contains food, drinks, beverages, snacks, or edible items.
ACCEPT: Coke, water, coffee, alcohol, candy, gum, protein shakes, supplements - ANYTHING consumable
DO NOT reject images just because they have backgrounds, tables, people, or restaurant settings
ONLY reject if there is LITERALLY ZERO FOOD OR BEVERAGE visible (e.g., empty room, car, landscape)

Beverages like soda, juice, water, coffee ARE VALID MEAL COMPONENTS - analyze them!

If you see ZERO consumable items, respond: {\"error\": \"not_food\", \"message\": \"No food or beverage detected\"}

For {$journeyConfig['focus']} with {$journeyConfig['tone']}, respond with this COMPLETE JSON structure:

{
  \"pass_1_detection\": {
    \"eggs\": {\"count\": 2, \"method\": \"counted 2 yolks\"},
    \"salmon\": {\"count\": \"1 piece, ~6 oz\", \"method\": \"1 fillet sliced, estimated medium portion\"},
    \"peas\": {\"count\": \"1/2 cup\", \"method\": \"portion estimate NOT individual count\"},
    \"broccoli\": {\"count\": \"1 cup\", \"method\": \"visual estimate\"},
    [... EVERY visible food item must be listed ...]
  },
  \"pass_2_verification\": {
    \"eggs\": {\"pass_1_count\": X, \"verified_count\": Y, \"changed\": boolean, \"reason\": \"if changed\"},
    [... verification for all items ...]
  },
  \"calcuplate\": {
    \"analysis_type\": \"complete_meal\",
    \"items_detected\": [
      {\"item\": \"Fried eggs\", \"quantity\": \"VERIFIED count\", \"calories\": number, \"type\": \"protein\"}
    ],
    \"totals\": {
      \"calories\": number,
      \"protein_g\": number,
      \"carbs_g\": number,
      \"fat_g\": number,
      \"fiber_g\": number,
      \"sodium_mg\": number
    },
    \"confidence\": 90,
    \"data_source\": \"source\"
  },
  \"insights\": [\"insight1\", \"insight2\"],
  \"recommendations\": [\"rec1\", \"rec2\"]
}

CRITICAL: You MUST output all three passes. Users are paying for accurate tracking.";


    $userPrompt = "Time: $time
Context: $notes
Symptoms: $symptoms

Analyze this meal photo for automatic nutritional logging with focus on {$journeyConfig['focus']}.";

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

    // Use direct API call instead of smart router for now
    if ($useSmartRouter && class_exists('SmartAIRouter')) {
        error_log("QuietGo CalcuPlate: Using smart router");
        $response = SmartAIRouter::routeImageAnalysis($imagePath, $messages, 'meal', 1000);
    } else {
        error_log("QuietGo CalcuPlate: Using direct OpenAI call");
        $response = makeOpenAIRequest($messages, OPENAI_VISION_MODEL, 1000);
    }

    error_log("QuietGo CalcuPlate: Response received, has error: " . (isset($response['error']) ? 'YES' : 'NO'));

    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];

    // Strip markdown code blocks if present (GPT-4o-mini sometimes wraps JSON)
    $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
    $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
    $aiContent = trim($aiContent);

    // Fix common JSON errors from AI
    // Fix: "quantity": 1 portion" -> "quantity": "1 portion"
    $aiContent = preg_replace('/"quantity":\s*(\d+\s+[^,}"]+)"/', '"quantity": "$1"', $aiContent);
    // Fix: "quantity": 5 spears -> "quantity": "5 spears"
    $aiContent = preg_replace('/"quantity":\s*([^,}"]+)([,}])/', '"quantity": "$1"$2', $aiContent);

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
