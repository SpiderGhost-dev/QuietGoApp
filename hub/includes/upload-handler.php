<?php
/**
 * Enhanced handlePhotoUpload with database integration
 * Saves to both file system AND database
 */

function handlePhotoUploadWithDB($file, $postData, $user) {
    global $hasCalcuPlate, $userJourney;

    // Include required files
    require_once __DIR__ . '/includes/storage-helper.php';
    require_once __DIR__ . '/includes/db-operations.php';
    
    $storage = getQuietGoStorage();
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

    // Prepare metadata
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

    // Store photo file
    $photoType = $postData['photo_type'] ?? 'general';
    $storeResult = $storage->storePhoto($user['email'], $photoType, $file, $metadata);

    if (!$storeResult['success']) {
        return ['status' => 'error', 'message' => $storeResult['error']];
    }

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

    // Generate AI analysis
    $aiAnalysis = generateAIAnalysis($postData, $userJourney, $hasCalcuPlate, $storeResult['filepath']);

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
                'model_tier' => $aiAnalysis['model_tier'] ?? 'expensive',
                'tokens_used' => null,
                'processing_time' => $aiAnalysis['processing_time'] ?? null
            ]);
        }
    }

    // Also store in file system (legacy)
    if (!isset($aiAnalysis['error'])) {
        $storage->storeAnalysis($user['email'], 'ai_results', [
            'photo_type' => $photoType,
            'analysis' => $aiAnalysis,
            'photo_metadata' => $metadata
        ]);
    }

    $result = [
        'status' => 'success',
        'photo_id' => $photoId,
        'filename' => basename($storeResult['filepath']),
        'thumbnail' => $storeResult['thumbnail'],
        'ai_analysis' => $aiAnalysis,
        'metadata' => $metadata,
        'requires_manual_logging' => (!$hasCalcuPlate && $postData['photo_type'] === 'meal'),
        'storage_path' => $storeResult['filepath']
    ];
    
    if ($result['requires_manual_logging']) {
        $_SESSION['pending_manual_logging'] = $result;
    }
    
    return $result;
}
?>
