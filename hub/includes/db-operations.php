<?php
/**
 * QuietGo Database Operations
 * Functions for saving and retrieving analysis data
 */

require_once __DIR__ . '/db-config.php';

/**
 * Get or create user in database
 * @param string $email
 * @param array $userData
 * @return int User ID
 */
function getOrCreateUser($email, $userData = []) {
    $db = getDBConnection();
    if (!$db) return null;
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        return $user['id'];
    }
    
    // Create new user
    $stmt = $db->prepare("
        INSERT INTO users (email, name, journey, subscription_plan, subscription_status)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $email,
        $userData['name'] ?? 'User',
        $userData['journey'] ?? 'best_life',
        $userData['subscription_plan'] ?? 'free',
        $userData['subscription_status'] ?? 'active'
    ]);
    
    return $db->lastInsertId();
}

/**
 * Save photo metadata to database
 * @param int $userId
 * @param array $photoData
 * @return int Photo ID
 */
function savePhoto($userId, $photoData) {
    $db = getDBConnection();
    if (!$db) return null;
    
    $stmt = $db->prepare("
        INSERT INTO photos (
            user_id, photo_type, filename, filepath, thumbnail_path,
            file_size, mime_type, location_latitude, location_longitude,
            location_accuracy, context_time, context_symptoms, context_notes,
            original_filename, upload_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    $stmt->execute([
        $userId,
        $photoData['photo_type'],
        $photoData['filename'],
        $photoData['filepath'],
        $photoData['thumbnail_path'] ?? null,
        $photoData['file_size'] ?? null,
        $photoData['mime_type'] ?? null,
        $photoData['location_latitude'] ?? null,
        $photoData['location_longitude'] ?? null,
        $photoData['location_accuracy'] ?? null,
        $photoData['context_time'] ?? null,
        $photoData['context_symptoms'] ?? null,
        $photoData['context_notes'] ?? null,
        $photoData['original_filename'] ?? null
    ]);
    
    return $db->lastInsertId();
}

/**
 * Save stool analysis to database
 * @param int $photoId
 * @param int $userId
 * @param array $analysis
 * @return bool Success
 */
function saveStoolAnalysis($photoId, $userId, $analysis) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $stmt = $db->prepare("
        INSERT INTO stool_analyses (
            photo_id, user_id, bristol_scale, bristol_description,
            color_assessment, consistency, volume_estimate, confidence_score,
            health_insights, recommendations, reported_symptoms, correlation_note,
            ai_model, processing_time, analysis_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    return $stmt->execute([
        $photoId,
        $userId,
        $analysis['bristol_scale'] ?? null,
        $analysis['bristol_description'] ?? null,
        $analysis['color_assessment'] ?? null,
        $analysis['consistency'] ?? null,
        $analysis['volume_estimate'] ?? null,
        $analysis['confidence'] ?? null,
        json_encode($analysis['health_insights'] ?? []),
        json_encode($analysis['recommendations'] ?? []),
        $analysis['reported_symptoms'] ?? null,
        $analysis['correlation_note'] ?? null,
        $analysis['ai_model'] ?? null,
        $analysis['processing_time'] ?? null
    ]);
}

/**
 * Save meal analysis to database
 * @param int $photoId
 * @param int $userId
 * @param array $analysis
 * @return bool Success
 */
function saveMealAnalysis($photoId, $userId, $analysis) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $calcuplate = $analysis['calcuplate'] ?? [];
    $macros = $calcuplate['macros'] ?? [];
    
    $stmt = $db->prepare("
        INSERT INTO meal_analyses (
            photo_id, user_id, foods_detected, total_calories,
            protein_grams, carbs_grams, fat_grams, fiber_grams,
            meal_quality_score, portion_sizes, nutritional_completeness,
            confidence_score, nutrition_insights, recommendations,
            journey_specific_note, ai_model, model_tier, cost_tier,
            processing_time, analysis_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    return $stmt->execute([
        $photoId,
        $userId,
        json_encode($calcuplate['foods_detected'] ?? []),
        $calcuplate['total_calories'] ?? null,
        floatval(str_replace('g', '', $macros['protein'] ?? '0')),
        floatval(str_replace('g', '', $macros['carbs'] ?? '0')),
        floatval(str_replace('g', '', $macros['fat'] ?? '0')),
        floatval(str_replace('g', '', $macros['fiber'] ?? '0')),
        $calcuplate['meal_quality_score'] ?? null,
        $calcuplate['portion_sizes'] ?? null,
        $calcuplate['nutritional_completeness'] ?? null,
        $analysis['confidence'] ?? null,
        json_encode($analysis['nutrition_insights'] ?? []),
        json_encode($analysis['recommendations'] ?? []),
        $analysis['clinical_nutrition'] ?? $analysis['performance_nutrition'] ?? $analysis['wellness_nutrition'] ?? null,
        $analysis['ai_model'] ?? null,
        $analysis['model_tier'] ?? null,
        $analysis['cost_tier'] ?? null,
        $analysis['processing_time'] ?? null
    ]);
}

/**
 * Save symptom analysis to database
 * @param int $photoId
 * @param int $userId
 * @param array $analysis
 * @return bool Success
 */
function saveSymptomAnalysis($photoId, $userId, $analysis) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $stmt = $db->prepare("
        INSERT INTO symptom_analyses (
            photo_id, user_id, symptom_category, severity_estimate,
            visual_characteristics, confidence_score, tracking_recommendations,
            correlation_potential, reported_symptoms, ai_model,
            processing_time, analysis_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    return $stmt->execute([
        $photoId,
        $userId,
        $analysis['symptom_category'] ?? null,
        $analysis['severity_estimate'] ?? null,
        json_encode($analysis['visual_characteristics'] ?? []),
        $analysis['confidence'] ?? null,
        json_encode($analysis['tracking_recommendations'] ?? []),
        $analysis['correlation_potential'] ?? null,
        $analysis['reported_symptoms'] ?? null,
        $analysis['ai_model'] ?? null,
        $analysis['processing_time'] ?? null
    ]);
}

/**
 * Save manual meal log to database
 * @param int $userId
 * @param int $photoId
 * @param array $mealData
 * @return bool Success
 */
function saveManualMealLog($userId, $photoId, $mealData) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $stmt = $db->prepare("
        INSERT INTO manual_meal_logs (
            user_id, photo_id, meal_type, meal_time, meal_date,
            portion_size, main_foods, estimated_calories,
            protein_grams, carb_grams, fat_grams,
            hunger_before, fullness_after, energy_level, meal_notes,
            log_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    return $stmt->execute([
        $userId,
        $photoId,
        $mealData['meal_type'],
        $mealData['meal_time'],
        date('Y-m-d'),
        $mealData['portion_size'],
        $mealData['main_foods'],
        $mealData['estimated_calories'] ?? null,
        $mealData['protein_grams'] ?? null,
        $mealData['carb_grams'] ?? null,
        $mealData['fat_grams'] ?? null,
        $mealData['hunger_before'] ?? null,
        $mealData['fullness_after'] ?? null,
        $mealData['energy_level'] ?? null,
        $mealData['meal_notes'] ?? null
    ]);
}

/**
 * Track AI API cost
 * @param int $userId
 * @param array $costData
 * @return bool Success
 */
function trackAICost($userId, $costData) {
    $db = getDBConnection();
    if (!$db) return false;
    
    $costMap = [
        'cheap' => 0.002,
        'medium' => 0.005,
        'expensive' => 0.015
    ];
    
    $stmt = $db->prepare("
        INSERT INTO ai_cost_tracking (
            user_id, photo_type, ai_model, model_tier,
            cost_estimate, tokens_used, processing_time, request_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    return $stmt->execute([
        $userId,
        $costData['photo_type'] ?? null,
        $costData['ai_model'] ?? null,
        $costData['model_tier'] ?? null,
        $costMap[$costData['model_tier'] ?? 'expensive'] ?? 0.015,
        $costData['tokens_used'] ?? null,
        $costData['processing_time'] ?? null
    ]);
}

/**
 * Get recent analyses for user
 * @param int $userId
 * @param int $limit
 * @return array Recent analyses
 */
function getRecentAnalyses($userId, $limit = 20) {
    $db = getDBConnection();
    if (!$db) return [];
    
    $stmt = $db->prepare("
        SELECT 
            p.id as photo_id,
            p.photo_type,
            p.filename,
            p.thumbnail_path,
            p.upload_timestamp,
            COALESCE(s.analysis_timestamp, m.analysis_timestamp, sy.analysis_timestamp) as analysis_timestamp,
            COALESCE(s.confidence_score, m.confidence_score, sy.confidence_score) as confidence_score
        FROM photos p
        LEFT JOIN stool_analyses s ON p.id = s.photo_id
        LEFT JOIN meal_analyses m ON p.id = m.photo_id
        LEFT JOIN symptom_analyses sy ON p.id = sy.photo_id
        WHERE p.user_id = ?
        ORDER BY p.upload_timestamp DESC
        LIMIT ?
    ");
    
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

?>
