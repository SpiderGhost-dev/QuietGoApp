<?php
/**
 * QuietGo CalcuPlate Recalculation Endpoint v3.2
 * Handles nutritional recalculation when users adjust meal analysis
 *
 * Accepts POST JSON:
 * {
 *   "items": [
 *     {
 *       "name": "salmon fillet",
 *       "est_weight_g": 168,
 *       "cooking_method": "pan-seared",
 *       "adders": [{"type":"oil","amount_ml":7}]
 *     }
 *   ]
 * }
 *
 * Returns JSON with recalculated totals and ranges
 */

header('Content-Type: application/json');
require_once __DIR__ . '/includes/analysis-functions.php';

// Comprehensive macro database (per 100g)
// Format: [protein_g, carbs_g, fat_g]
$MACRO_DATABASE = [
    // Proteins - Fish & Seafood
    'salmon' => ['p' => 20.0, 'c' => 0, 'f' => 13.0],
    'tuna' => ['p' => 26.0, 'c' => 0, 'f' => 1.0],
    'cod' => ['p' => 18.0, 'c' => 0, 'f' => 0.7],
    'shrimp' => ['p' => 24.0, 'c' => 0.2, 'f' => 0.3],
    'tilapia' => ['p' => 26.0, 'c' => 0, 'f' => 1.7],

    // Proteins - Poultry
    'chicken breast' => ['p' => 31.0, 'c' => 0, 'f' => 3.6],
    'chicken thigh' => ['p' => 26.0, 'c' => 0, 'f' => 10.9],
    'turkey' => ['p' => 29.0, 'c' => 0, 'f' => 1.7],
    'duck' => ['p' => 19.0, 'c' => 0, 'f' => 11.2],

    // Proteins - Meat
    'beef steak' => ['p' => 26.0, 'c' => 0, 'f' => 15.0],
    'ground beef' => ['p' => 26.0, 'c' => 0, 'f' => 15.0],
    'pork chop' => ['p' => 27.0, 'c' => 0, 'f' => 7.0],
    'bacon' => ['p' => 37.0, 'c' => 1.4, 'f' => 42.0],
    'lamb' => ['p' => 25.0, 'c' => 0, 'f' => 17.0],

    // Proteins - Plant-based
    'tofu' => ['p' => 8.0, 'c' => 1.9, 'f' => 4.8],
    'tempeh' => ['p' => 19.0, 'c' => 9.0, 'f' => 11.0],
    'seitan' => ['p' => 25.0, 'c' => 14.0, 'f' => 2.0],

    // Eggs & Dairy
    'egg' => ['p' => 13.0, 'c' => 1.1, 'f' => 11.0],
    'cheese cheddar' => ['p' => 25.0, 'c' => 1.3, 'f' => 33.0],
    'mozzarella' => ['p' => 22.0, 'c' => 2.2, 'f' => 22.0],
    'feta' => ['p' => 14.0, 'c' => 4.1, 'f' => 21.0],
    'yogurt greek' => ['p' => 10.0, 'c' => 3.6, 'f' => 0.4],
    'milk' => ['p' => 3.4, 'c' => 5.0, 'f' => 3.3],

    // Grains & Starches (cooked)
    'rice white' => ['p' => 2.7, 'c' => 28.0, 'f' => 0.3],
    'rice brown' => ['p' => 2.6, 'c' => 23.0, 'f' => 0.9],
    'pasta' => ['p' => 5.0, 'c' => 25.0, 'f' => 1.0],
    'quinoa' => ['p' => 4.4, 'c' => 21.0, 'f' => 1.9],
    'bread' => ['p' => 9.0, 'c' => 49.0, 'f' => 3.2],
    'oats' => ['p' => 2.5, 'c' => 12.0, 'f' => 1.4],

    // Vegetables
    'broccoli' => ['p' => 2.8, 'c' => 7.0, 'f' => 0.4],
    'spinach' => ['p' => 2.9, 'c' => 3.6, 'f' => 0.4],
    'carrot' => ['p' => 0.9, 'c' => 10.0, 'f' => 0.2],
    'potato' => ['p' => 2.0, 'c' => 17.0, 'f' => 0.1],
    'sweet potato' => ['p' => 1.6, 'c' => 20.0, 'f' => 0.1],
    'tomato' => ['p' => 0.9, 'c' => 3.9, 'f' => 0.2],
    'lettuce' => ['p' => 1.4, 'c' => 2.9, 'f' => 0.2],
    'cucumber' => ['p' => 0.7, 'c' => 3.6, 'f' => 0.1],
    'bell pepper' => ['p' => 1.0, 'c' => 6.0, 'f' => 0.3],
    'onion' => ['p' => 1.1, 'c' => 9.0, 'f' => 0.1],
    'garlic' => ['p' => 6.4, 'c' => 33.0, 'f' => 0.5],
    'peas' => ['p' => 5.4, 'c' => 14.0, 'f' => 0.4],
    'corn' => ['p' => 3.4, 'c' => 19.0, 'f' => 1.5],
    'green beans' => ['p' => 1.8, 'c' => 7.0, 'f' => 0.2],
    'asparagus' => ['p' => 2.2, 'c' => 3.9, 'f' => 0.2],
    'mushroom' => ['p' => 3.1, 'c' => 3.3, 'f' => 0.3],

    // Fruits
    'apple' => ['p' => 0.3, 'c' => 14.0, 'f' => 0.2],
    'banana' => ['p' => 1.1, 'c' => 23.0, 'f' => 0.3],
    'orange' => ['p' => 0.9, 'c' => 12.0, 'f' => 0.1],
    'strawberry' => ['p' => 0.7, 'c' => 8.0, 'f' => 0.3],
    'blueberry' => ['p' => 0.7, 'c' => 14.0, 'f' => 0.3],
    'avocado' => ['p' => 2.0, 'c' => 9.0, 'f' => 15.0],
    'mango' => ['p' => 0.8, 'c' => 15.0, 'f' => 0.4],

    // Legumes (cooked)
    'black beans' => ['p' => 8.9, 'c' => 24.0, 'f' => 0.5],
    'chickpeas' => ['p' => 8.9, 'c' => 27.0, 'f' => 2.6],
    'lentils' => ['p' => 9.0, 'c' => 20.0, 'f' => 0.4],
    'kidney beans' => ['p' => 8.7, 'c' => 23.0, 'f' => 0.5],

    // Nuts & Seeds
    'almonds' => ['p' => 21.0, 'c' => 22.0, 'f' => 49.0],
    'walnuts' => ['p' => 15.0, 'c' => 14.0, 'f' => 65.0],
    'peanuts' => ['p' => 26.0, 'c' => 16.0, 'f' => 49.0],
    'sunflower seeds' => ['p' => 21.0, 'c' => 20.0, 'f' => 51.0],

    // Sauces & Condiments
    'tomato sauce' => ['p' => 1.0, 'c' => 6.0, 'f' => 2.0],
    'soy sauce' => ['p' => 8.0, 'c' => 6.0, 'f' => 0.1],
    'hot sauce' => ['p' => 0.9, 'c' => 0.8, 'f' => 0.5],
    'ketchup' => ['p' => 1.0, 'c' => 27.0, 'f' => 0.1],
    'mayonnaise' => ['p' => 1.4, 'c' => 0.6, 'f' => 75.0],
    'mustard' => ['p' => 4.4, 'c' => 5.0, 'f' => 6.0],
    'bbq sauce' => ['p' => 1.0, 'c' => 20.0, 'f' => 1.0],

    // Sushi items
    'sushi roll' => ['p' => 7.0, 'c' => 30.0, 'f' => 3.0],
    'sashimi' => ['p' => 23.0, 'c' => 0, 'f' => 5.0],
    'edamame' => ['p' => 11.0, 'c' => 10.0, 'f' => 5.0],

    // Generic fallbacks
    'protein' => ['p' => 25.0, 'c' => 0, 'f' => 8.0],
    'carb' => ['p' => 3.0, 'c' => 25.0, 'f' => 1.0],
    'vegetable' => ['p' => 2.0, 'c' => 7.0, 'f' => 0.3],
    'fruit' => ['p' => 0.8, 'c' => 14.0, 'f' => 0.2],
    'grain' => ['p' => 3.5, 'c' => 25.0, 'f' => 1.0],
];

// Adder macros (per ml or per g)
$ADDER_MACROS = [
    'oil:ml' => ['p' => 0, 'c' => 0, 'f' => 0.91], // 1ml oil ≈ 0.91g fat
    'dressing:ml' => ['p' => 0, 'c' => 0.5, 'f' => 1.0], // Average dressing
    'sauce:ml' => ['p' => 0.2, 'c' => 1.2, 'f' => 0.3], // Average sauce
    'cheese:g' => ['p' => 0.25, 'c' => 0.02, 'f' => 0.33], // Average cheese
    'sugar:g' => ['p' => 0, 'c' => 1.0, 'f' => 0],
];

/**
 * Query USDA FoodData Central API for nutrition data
 * @param string $foodName
 * @return array|null Macros array or null if not found
 */
function queryUSDADatabase($foodName)
{
    $apiKey = $_ENV['USDA_API_KEY'] ?? null;

    if (!$apiKey) {
        error_log("QuietGo: USDA API key not configured");
        return null;
    }

    $url = "https://api.nal.usda.gov/fdc/v1/foods/search?api_key=" . urlencode($apiKey) . "&query=" . urlencode($foodName) . "&pageSize=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log("QuietGo: USDA API request failed - HTTP $httpCode");
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['foods'][0])) {
        error_log("QuietGo: No USDA results for: $foodName");
        return null;
    }

    $food = $data['foods'][0];

    // Extract nutrients (per 100g)
    $macros = ['p' => 0, 'c' => 0, 'f' => 0];

    foreach ($food['foodNutrients'] as $nutrient) {
        $name = strtolower($nutrient['nutrientName'] ?? '');
        $value = floatval($nutrient['value'] ?? 0);

        if (strpos($name, 'protein') !== false) {
            $macros['p'] = $value;
        } elseif (strpos($name, 'carbohydrate') !== false) {
            $macros['c'] = $value;
        } elseif (strpos($name, 'total lipid') !== false || strpos($name, 'fat') !== false) {
            $macros['f'] = $value;
        }
    }

    error_log("QuietGo: USDA found " . $food['description'] . " - P:{$macros['p']}g C:{$macros['c']}g F:{$macros['f']}g");

    return $macros;
}

/**
 * Calculate calories from macros
 */
function calculateCalories($protein, $carbs, $fat)
{
    return ($protein * 4) + ($carbs * 4) + ($fat * 9);
}

/**
 * Find best matching macro entry for a food name
 * Enhanced with USDA database fallback
 */
function findMacros($foodName, $macroDatabase)
{
    $foodName = strtolower(trim($foodName));

    // Direct match
    if (isset($macroDatabase[$foodName])) {
        error_log("QuietGo: Found exact match in database for: $foodName");
        return $macroDatabase[$foodName];
    }

    // Partial match - search for keywords
    $bestMatch = null;
    $bestScore = 0;

    foreach ($macroDatabase as $key => $macros) {
        // Check if food name contains the database key
        if (strpos($foodName, $key) !== false) {
            $score = strlen($key); // Longer matches are better
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $macros;
            }
        }
    }

    if ($bestMatch) {
        error_log("QuietGo: Found partial match in database for: $foodName");
        return $bestMatch;
    }

    // Try USDA database before category fallback
    error_log("QuietGo: No database match for: $foodName, querying USDA...");
    $usdaMacros = queryUSDADatabase($foodName);
    if ($usdaMacros && ($usdaMacros['p'] > 0 || $usdaMacros['c'] > 0 || $usdaMacros['f'] > 0)) {
        error_log("QuietGo: USDA lookup successful for: $foodName");
        return $usdaMacros;
    }

    // Category-based fallback
    if (
        strpos($foodName, 'chicken') !== false ||
        strpos($foodName, 'beef') !== false ||
        strpos($foodName, 'pork') !== false ||
        strpos($foodName, 'fish') !== false
    ) {
        error_log("QuietGo: Using protein category fallback for: $foodName");
        return $macroDatabase['protein'];
    }

    if (
        strpos($foodName, 'rice') !== false ||
        strpos($foodName, 'pasta') !== false ||
        strpos($foodName, 'bread') !== false
    ) {
        error_log("QuietGo: Using grain category fallback for: $foodName");
        return $macroDatabase['grain'];
    }

    // Default fallback
    error_log("QuietGo: Using default fallback for: $foodName");
    return ['p' => 3.0, 'c' => 10.0, 'f' => 2.0];
}

// Parse JSON input
$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (!is_array($payload) || !isset($payload['items'])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid request format']);
    exit;
}

$items = $payload['items'];
$totalProtein = 0;
$totalCarbs = 0;
$totalFat = 0;
$totalCaloriesMin = 0;
$totalCaloriesMax = 0;

$recalculatedItems = [];

foreach ($items as $item) {
    $name = $item['name'] ?? 'unknown';
    $weight = max(1.0, floatval($item['est_weight_g'] ?? 100));
    $cookingMethod = $item['cooking_method'] ?? 'raw';
    $adders = $item['adders'] ?? [];

    // Find base macros
    $macros = findMacros($name, $MACRO_DATABASE);

    // Calculate base nutrition (per 100g → actual weight)
    $itemProtein = $macros['p'] * ($weight / 100.0);
    $itemCarbs = $macros['c'] * ($weight / 100.0);
    $itemFat = $macros['f'] * ($weight / 100.0);

    // Apply cooking method oil uptake
    $methodData = CalcuPlateAnalysis::$COOKING_METHODS[$cookingMethod] ??
        CalcuPlateAnalysis::$COOKING_METHODS['raw'];

    $oilUptake = $methodData['oil_uptake_ml_per_100g'] * ($weight / 100.0);
    if ($oilUptake > 0) {
        $itemFat += 0.91 * $oilUptake; // 1ml oil ≈ 0.91g fat
    }

    // Add adders
    foreach ($adders as $adder) {
        $type = $adder['type'] ?? 'sauce';
        $amountMl = floatval($adder['amount_ml'] ?? 0);
        $amountG = floatval($adder['amount_g'] ?? 0);

        if ($amountMl > 0 && isset($ADDER_MACROS["$type:ml"])) {
            $adderMacro = $ADDER_MACROS["$type:ml"];
            $itemProtein += $adderMacro['p'] * $amountMl;
            $itemCarbs += $adderMacro['c'] * $amountMl;
            $itemFat += $adderMacro['f'] * $amountMl;
        } elseif ($amountG > 0 && isset($ADDER_MACROS["$type:g"])) {
            $adderMacro = $ADDER_MACROS["$type:g"];
            $itemProtein += $adderMacro['p'] * $amountG;
            $itemCarbs += $adderMacro['c'] * $amountG;
            $itemFat += $adderMacro['f'] * $amountG;
        }
    }

    $itemCalories = calculateCalories($itemProtein, $itemCarbs, $itemFat);

    // Calculate range (±15% uncertainty)
    $itemCaloriesMin = $itemCalories * 0.85;
    $itemCaloriesMax = $itemCalories * 1.15;

    $totalProtein += $itemProtein;
    $totalCarbs += $itemCarbs;
    $totalFat += $itemFat;
    $totalCaloriesMin += $itemCaloriesMin;
    $totalCaloriesMax += $itemCaloriesMax;

    $recalculatedItems[] = [
        'name' => $name,
        'weight_g' => $weight,
        'calories' => round($itemCalories),
        'protein_g' => round($itemProtein, 1),
        'carbs_g' => round($itemCarbs, 1),
        'fat_g' => round($itemFat, 1),
        'calories_range' => [round($itemCaloriesMin), round($itemCaloriesMax)]
    ];
}

$totalCalories = calculateCalories($totalProtein, $totalCarbs, $totalFat);

// Return results
echo json_encode([
    'ok' => true,
    'totals' => [
        'calories' => round($totalCalories),
        'protein_g' => round($totalProtein, 1),
        'carbs_g' => round($totalCarbs, 1),
        'fat_g' => round($totalFat, 1)
    ],
    'ranges' => [
        'calories' => [round($totalCaloriesMin), round($totalCaloriesMax)]
    ],
    'items' => $recalculatedItems,
    'calculation_notes' => [
        'Macros: Protein=4kcal/g, Carbs=4kcal/g, Fat=9kcal/g',
        'Cooking method oil uptake applied',
        'Adders included in totals',
        'Range represents ±15% uncertainty'
    ]
], JSON_PRETTY_PRINT);
?>

