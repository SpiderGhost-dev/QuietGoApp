<?php
/**
 * Recalculate nutrition based on user-corrected quantities
 * Called from verification modal when user adjusts AI-detected counts
 */

// CRITICAL: Clean output buffer to prevent JSON parse errors
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Suppress any PHP warnings/notices that could break JSON
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Authentication check
if (!isset($_SESSION["hub_user"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['corrected_items']) || !is_array($input['corrected_items'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

require_once __DIR__ . "/includes/openai-config.php";
require_once __DIR__ . "/includes/analysis-functions.php";

$correctedItems = $input['corrected_items'];
$userJourney = $input['user_journey'] ?? 'best_life';
$journeyConfig = getJourneyPromptConfig($userJourney);

// Build prompt for nutrition calculation with corrected quantities
$systemPrompt = "You are CalcuPlate's nutrition calculator. The user has verified the following food items and quantities.

Calculate ONLY the nutritional information. Do NOT recount items - use the exact quantities provided.

Respond ONLY with valid JSON:
{
    \"calcuplate\": {
        \"analysis_type\": \"user_verified\",
        \"items_detected\": [
            {\"item\": \"Food name\", \"quantity\": \"amount\", \"calories\": number, \"protein_g\": number, \"carbs_g\": number, \"fat_g\": number}
        ],
        \"totals\": {
            \"calories\": number,
            \"protein_g\": number,
            \"carbs_g\": number,
            \"fat_g\": number,
            \"fiber_g\": number,
            \"sodium_mg\": number
        },
        \"confidence\": 95,
        \"data_source\": \"User-verified quantities\"
    }
}";

// Build item list
$itemsList = "User-verified items:\n";
foreach ($correctedItems as $item) {
    $itemsList .= "- {$item['quantity']} {$item['item']}\n";
}

$userPrompt = $itemsList . "\n\nCalculate complete nutritional breakdown for these items with the EXACT quantities specified.";

$messages = [
    [
        'role' => 'system',
        'content' => $systemPrompt
    ],
    [
        'role' => 'user',
        'content' => $userPrompt
    ]
];

try {
    $response = makeOpenAIRequest($messages, OPENAI_TEXT_MODEL, 1000);

    if (isset($response['error'])) {
        throw new Exception($response['error']);
    }

    $aiContent = $response['choices'][0]['message']['content'];

    // Strip markdown
    $aiContent = preg_replace('/^```json\s*/m', '', $aiContent);
    $aiContent = preg_replace('/\s*```$/m', '', $aiContent);
    $aiContent = trim($aiContent);

    $result = json_decode($aiContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from nutrition calculator');
    }

    // Ensure proper structure
    if (!isset($result['calcuplate'])) {
        throw new Exception('Invalid response structure');
    }

    // Add backward compatibility
    $cp = &$result['calcuplate'];
    if (isset($cp['totals'])) {
        if (!isset($cp['total_calories'])) {
            $cp['total_calories'] = $cp['totals']['calories'];
        }
        if (!isset($cp['macros'])) {
            $cp['macros'] = [
                'protein' => $cp['totals']['protein_g'] . 'g',
                'carbs' => $cp['totals']['carbs_g'] . 'g',
                'fat' => $cp['totals']['fat_g'] . 'g',
                'fiber' => ($cp['totals']['fiber_g'] ?? 0) . 'g'
            ];
        }
    }

    // Convert items_detected to foods_detected for UI compatibility
    if (isset($cp['items_detected']) && !isset($cp['foods_detected'])) {
        $foods = [];
        foreach ($cp['items_detected'] as $item) {
            $foods[] = $item['quantity'] . ' ' . $item['item'];
        }
        $cp['foods_detected'] = $foods;
    }

    // Add quality metrics
    if (!isset($cp['meal_quality_score'])) {
        $cp['meal_quality_score'] = '8/10';
    }
    if (!isset($cp['nutritional_completeness'])) {
        $cp['nutritional_completeness'] = '90%';
    }

    // Clean any stray output and send pure JSON
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;

} catch (Exception $e) {
    error_log("QuietGo Recalculation Error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        "error" => "Failed to recalculate nutrition",
        "message" => $e->getMessage()
    ]);
    exit;
}
