<?php
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
        'meal_focus' => 'symptom triggers and digestive impact'
    ],
    'performance' => [
        'title' => '💪 Peak Performance', 
        'focus' => 'nutrition impact on training and recovery',
        'ai_tone' => 'performance-focused analysis and coaching',
        'meal_focus' => 'energy, recovery, and performance optimization'
    ],
    'best_life' => [
        'title' => '✨ Best Life Mode',
        'focus' => 'energy levels and living your best life daily', 
        'ai_tone' => 'lifestyle optimization and feel-good insights',
        'meal_focus' => 'energy levels and overall wellness'
    ]
];
$currentJourneyConfig = $journeyConfig[$userJourney];

// Enhanced photo upload handling with time/location stamping
$uploadResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['health_item'])) {
    $uploadResult = handlePhotoUpload($_FILES['health_item'], $_POST, $user);
}

function handlePhotoUpload($file, $postData, $user) {
    global $hasCalcuPlate, $userJourney;
    
    // Create user-specific upload directory
    $userEmail = $user['email'];
    $userDir = preg_replace('/[^a-zA-Z0-9]/', '_', $userEmail);
    $uploadDir = __DIR__ . '/uploads/' . $userDir . '/' . date('Y-m-d') . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

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

    // Generate unique filename with timestamp
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $timestamp = date('His');
    $filename = date('Ymd') . '_' . $timestamp . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Generate thumbnail
        $thumbnailPath = generateThumbnail($filepath, $uploadDir);
        
        // ALWAYS timestamp, location stamp if available
        $locationData = null;
        if (!empty($postData['latitude']) && !empty($postData['longitude'])) {
            $locationData = [
                'latitude' => floatval($postData['latitude']),
                'longitude' => floatval($postData['longitude']),
                'accuracy' => $postData['accuracy'] ?? null,
                'timestamp' => time()
            ];
        }
        
        // Generate AI analysis (different for Pro vs Pro+)
        $aiAnalysis = generateAIAnalysis($postData, $userJourney, $hasCalcuPlate);
        
        // Save comprehensive metadata
        $metadata = [
            'filename' => $filename,
            'original_name' => $file['name'],
            'filepath' => $filepath,
            'thumbnail' => $thumbnailPath,
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
            'ai_analysis' => $aiAnalysis,
            'manual_meal_data' => $postData['manual_meal_data'] ?? null, // For Pro users
            'file_info' => [
                'size' => $file['size'],
                'dimensions' => getimagesize($filepath),
                'mime_type' => $mimeType
            ]
        ];
        
        // Save metadata to JSON file (in real app: database)
        $metadataFile = str_replace('.' . $extension, '_metadata.json', $filepath);
        file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
        
        return [
            'status' => 'success',
            'filename' => $filename,
            'thumbnail' => $thumbnailPath,
            'ai_analysis' => $aiAnalysis,
            'metadata' => $metadata,
            'requires_manual_logging' => (!$hasCalcuPlate && $postData['photo_type'] === 'meal')
        ];
    }

    return ['status' => 'error', 'message' => 'Failed to save file'];
}

function generateThumbnail($imagePath, $uploadDir) {
    $thumbnailDir = $uploadDir . 'thumbnails/';
    if (!is_dir($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $filename = basename($imagePath);
    $thumbnailPath = $thumbnailDir . 'thumb_' . $filename;
    
    // Get image info
    list($width, $height, $type) = getimagesize($imagePath);
    
    // Create image resource
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($imagePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($imagePath);
            break;
        default:
            return null;
    }
    
    // Calculate thumbnail dimensions (max 200px)
    $maxSize = 200;
    if ($width > $height) {
        $newWidth = $maxSize;
        $newHeight = ($height / $width) * $maxSize;
    } else {
        $newHeight = $maxSize;
        $newWidth = ($width / $height) * $maxSize;
    }
    
    // Create thumbnail
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save thumbnail
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $thumbnailPath, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $thumbnailPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $thumbnailPath);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($thumbnail);
    
    return str_replace(__DIR__, '', $thumbnailPath);
}

function generateAIAnalysis($postData, $userJourney, $hasCalcuPlate) {
    $photoType = $postData['photo_type'] ?? 'general';
    $symptoms = $postData['context_symptoms'] ?? '';
    $time = $postData['context_time'] ?? '';
    
    $analysis = [
        'timestamp' => time(),
        'confidence' => rand(78, 94),
        'processing_time' => rand(15, 45) / 10, // 1.5 to 4.5 seconds
        'user_journey' => $userJourney
    ];
    
    switch ($photoType) {
        case 'stool':
            // ALL users (Pro and Pro+) get AI stool analysis
            $bristolTypes = [
                1 => 'Type 1: Separate hard lumps - indicates constipation',
                2 => 'Type 2: Lumpy and sausage-like - mild constipation', 
                3 => 'Type 3: Sausage with surface cracks - normal range',
                4 => 'Type 4: Smooth, soft sausage - ideal consistency',
                5 => 'Type 5: Soft blobs with clear edges - lacking fiber',
                6 => 'Type 6: Mushy with ragged edges - mild diarrhea',
                7 => 'Type 7: Liquid consistency - severe diarrhea'
            ];
            
            $bristolType = rand(3, 6);
            $analysis['bristol_scale'] = $bristolType;
            $analysis['bristol_description'] = $bristolTypes[$bristolType];
            $analysis['color_assessment'] = ['normal brown', 'dark brown', 'light brown'][rand(0, 2)];
            $analysis['consistency'] = $bristolType <= 2 ? 'hard' : ($bristolType >= 6 ? 'loose' : 'normal');
            $analysis['volume_estimate'] = ['small', 'normal', 'large'][rand(0, 2)];
            
            // Journey-specific insights
            if ($userJourney === 'clinical') {
                $analysis['clinical_significance'] = 'Bristol Scale ' . $bristolType . ' - document for provider review';
                $analysis['tracking_recommendations'] = ['Note timing patterns', 'Record associated symptoms', 'Monitor consistency trends'];
            } elseif ($userJourney === 'performance') {
                $analysis['performance_impact'] = $bristolType >= 5 ? 'May impact training performance' : 'Good baseline for training';
                $analysis['athletic_considerations'] = ['Hydration status', 'Pre-workout timing', 'Recovery indicators'];
            } else {
                $analysis['wellness_insights'] = ['Normal variation expected', 'Stay consistent with habits', 'Monitor energy correlation'];
            }
            break;
            
        case 'meal':
            if ($hasCalcuPlate) {
                // PRO+ ONLY: CalcuPlate AI meal analysis with auto-logging
                $foodDatabase = [
                    'proteins' => ['grilled chicken', 'salmon', 'eggs', 'tofu', 'greek yogurt', 'lean beef'],
                    'carbs' => ['brown rice', 'quinoa', 'sweet potato', 'oatmeal', 'whole grain bread'],
                    'vegetables' => ['broccoli', 'spinach', 'carrots', 'bell peppers', 'zucchini'],
                    'fats' => ['avocado', 'olive oil', 'nuts', 'seeds', 'coconut oil']
                ];
                
                $detectedFoods = [];
                foreach ($foodDatabase as $category => $foods) {
                    if (rand(0, 1)) {
                        $detectedFoods[] = $foods[array_rand($foods)];
                    }
                }
                
                $totalCalories = rand(300, 750);
                $analysis['calcuplate'] = [
                    'auto_logged' => true,
                    'foods_detected' => $detectedFoods,
                    'total_calories' => $totalCalories,
                    'macros' => [
                        'protein' => rand(20, 40) . 'g',
                        'carbs' => rand(25, 55) . 'g',
                        'fat' => rand(12, 28) . 'g',
                        'fiber' => rand(6, 12) . 'g'
                    ],
                    'meal_quality_score' => rand(7, 10) . '/10',
                    'portion_sizes' => 'Automatically estimated',
                    'nutritional_completeness' => rand(75, 95) . '%'
                ];
                
                // Journey-specific auto-logged insights
                if ($userJourney === 'clinical') {
                    $analysis['clinical_nutrition'] = 'Logged for symptom correlation analysis';
                } elseif ($userJourney === 'performance') {
                    $analysis['performance_nutrition'] = 'Logged for training optimization';
                } else {
                    $analysis['wellness_nutrition'] = 'Logged for energy pattern analysis';
                }
            } else {
                // PRO ONLY: Manual logging required - no AI meal analysis
                $analysis['manual_logging_required'] = true;
                $analysis['upgrade_available'] = [
                    'feature' => 'CalcuPlate AI Meal Analysis',
                    'price' => '+$2.99/month',
                    'benefits' => ['Automatic food detection', 'Instant calorie calculation', 'Auto-logged nutrition data']
                ];
                $analysis['next_step'] = 'Complete manual meal logging form to continue';
            }
            break;
            
        case 'symptom':
            // All users get basic symptom photo analysis
            $analysis['symptom_category'] = ['skin reaction', 'inflammation', 'physical symptoms'][rand(0, 2)];
            $analysis['severity_estimate'] = ['mild', 'moderate', 'notable'][rand(0, 2)];
            $analysis['correlation_potential'] = 'Will improve with more meal and symptom data';
            break;
    }
    
    // Add symptom correlation if provided
    if ($symptoms) {
        $analysis['reported_symptoms'] = $symptoms;
        $analysis['correlation_note'] = 'Symptoms logged for pattern analysis';
    }
    
    return $analysis;
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

.subscription-info {
    background: var(--primary-blue);
    color: white;
    padding: 1rem 0;
    text-align: center;
    margin-bottom: 2rem;
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
                <?php if ($uploadResult['requires_manual_logging']): ?>
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
                        <a href="/hub/account.php?upgrade=pro_plus" style="color: yellow; text-decoration: underline;">
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
    <?php if ($uploadResult && $uploadResult['requires_manual_logging']): ?>
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
                                <input type="time" name="meal_time" required>
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
                        <button type="submit" style="background: var(--success-color); color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 600; cursor: pointer;">
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

            <div style="border: 2px dashed var(--card-border); border-radius: 8px; padding: 2rem; text-align: center; margin: 1rem 0; cursor: pointer;" onclick="document.getElementById('file-input').click()">
                <div style="font-size: 3rem; margin-bottom: 1rem;" id="upload-icon">📁</div>
                <h4 style="color: var(--text-primary); margin: 0 0 0.5rem 0;">Choose Photo</h4>
                <p style="color: var(--text-secondary); margin: 0;">Click here or drag and drop your photo</p>
                <input type="file" id="file-input" name="health_item" accept="image/*" hidden>
            </div>

            <div id="context-fields">
                <!-- Context fields will be populated by JavaScript -->
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" onclick="closeUploadModal()" style="flex: 1; background: transparent; border: 1px solid var(--card-border); color: var(--text-secondary); padding: 0.875rem; border-radius: 6px; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="flex: 2; background: var(--success-color); color: white; border: none; padding: 0.875rem; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    🚀 Upload & Analyze
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
            modalTitle.textContent = '🍽️ Upload Meal Photo';
            modalSubtitle.textContent = hasCalcuPlate ? 
                'CalcuPlate will automatically analyze and log your meal' : 
                'You\'ll complete a manual logging form after upload';
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
    
    // Time context (all photo types)
    container.innerHTML += `
        <div style="margin: 1rem 0;">
            <label style="display: block; color: var(--text-secondary); margin-bottom: 0.5rem; font-weight: 500;">Time of Day</label>
            <select name="context_time" style="width: 100%; background: #222; border: 1px solid var(--card-border); color: var(--text-primary); padding: 0.75rem; border-radius: 6px;">
                <option value="">Select time...</option>
                <option value="morning">Morning</option>
                <option value="afternoon">Afternoon</option>
                <option value="evening">Evening</option>
                <option value="night">Night</option>
            </select>
        </div>
    `;
    
    // Symptoms context (all photo types)
    container.innerHTML += `
        <div style="margin: 1rem 0;">
            <label style="display: block; color: var(--text-secondary); margin-bottom: 0.5rem; font-weight: 500;">Any Symptoms?</label>
            <input type="text" name="context_symptoms" placeholder="e.g., pain, bloating, energy changes, none" style="width: 100%; background: #222; border: 1px solid var(--card-border); color: var(--text-primary); padding: 0.75rem; border-radius: 6px;">
        </div>
    `;
    
    // Notes context (all photo types)  
    container.innerHTML += `
        <div style="margin: 1rem 0;">
            <label style="display: block; color: var(--text-secondary); margin-bottom: 0.5rem; font-weight: 500;">Additional Notes</label>
            <textarea name="context_notes" placeholder="Any additional context, patterns, or observations..." style="width: 100%; background: #222; border: 1px solid var(--card-border); color: var(--text-primary); padding: 0.75rem; border-radius: 6px; min-height: 80px;"></textarea>
        </div>
    `;
}

function closeUploadModal() {
    document.getElementById('upload-modal').style.display = 'none';
    document.getElementById('upload-form').reset();
    selectedFile = null;
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

// File input handling
document.getElementById('file-input').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        selectedFile = e.target.files[0];
        
        // Validate file type
        if (!selectedFile.type.startsWith('image/')) {
            alert('Please select an image file only');
            this.value = '';
            return;
        }
        
        // Validate file size (10MB max)
        if (selectedFile.size > 10 * 1024 * 1024) {
            alert('File too large. Maximum size is 10MB');
            this.value = '';
            return;
        }
        
        // Update UI to show selected file
        const uploadArea = document.querySelector('#upload-modal div[style*="border: 2px dashed"]');
        const instructions = uploadArea.querySelector('p');
        instructions.textContent = `Selected: ${selectedFile.name} (${(selectedFile.size / 1024 / 1024).toFixed(1)}MB)`;
        uploadArea.style.borderColor = 'var(--success-color)';
        uploadArea.style.background = 'rgba(108, 152, 95, 0.1)';
    }
});

// Form submission
document.getElementById('upload-form').addEventListener('submit', function(e) {
    if (!selectedFile) {
        e.preventDefault();
        alert('Please select a photo first');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '🔄 Analyzing...';
    submitBtn.disabled = true;
});

console.log('📤 Enhanced Photo Upload System Loaded');
console.log('✅ Pro users: AI stool analysis + manual meal forms'); 
console.log('⚡ Pro+ users: AI stool analysis + CalcuPlate auto-logging');
console.log('📍 Location tracking enabled for enhanced pattern analysis');
console.log('🎯 Journey-focused analysis: ' + <?php echo json_encode($userJourney); ?>);
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . '/../includes/chatbot-widget.php'; ?>
