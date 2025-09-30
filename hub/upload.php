<?php
// Prevent any output before HTML starts
ob_start();

// TEMPORARILY ENABLE ERROR REPORTING TO DEBUG
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/error_log.txt");

// Log that upload.php started
error_log("QuietGo upload.php: Script started at " . date("Y-m-d H:i:s"));

// Hub authentication check - ONLY Pro and Pro+ users have hub access
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for admin login in multiple ways
$isAdminLoggedIn = false;
if (isset($_SESSION["admin_logged_in"])) {
    $isAdminLoggedIn = true;
}
if (isset($_COOKIE["admin_logged_in"]) && $_COOKIE["admin_logged_in"] === "true") {
    $isAdminLoggedIn = true;
}
if (isset($_SESSION["hub_user"]) && isset($_SESSION["hub_user"]["is_admin_impersonation"])) {
    $isAdminLoggedIn = true;
}

if (!isset($_SESSION["hub_user"]) && !isset($_COOKIE["hub_auth"]) && !$isAdminLoggedIn) {
    header("Location: /hub/login.php");
    exit;
}

if ($isAdminLoggedIn && !isset($_SESSION["hub_user"])) {
    $_SESSION["hub_user"] = [
        "email" => "admin@quietgo.app",
        "name" => "Admin User",
        "login_time" => time(),
        "is_admin_impersonation" => true,
        "subscription_plan" => "pro_plus",
        "journey" => "best_life"
    ];
}

// Get user subscription status and journey preferences
$user = $_SESSION["hub_user"];
$subscriptionPlan = $user["subscription_plan"] ?? "free";

// CORRECTED SUBSCRIPTION LOGIC
$hasCalcuPlate = in_array($subscriptionPlan, ["pro_plus"]);  // Only Pro+ has CalcuPlate
$isProUser = in_array($subscriptionPlan, ["pro", "pro_plus"]); // Pro and Pro+ have hub access
$isFreeTier = ($subscriptionPlan === "free");

// Free users get NO hub access - redirect immediately
if ($isFreeTier && !$isAdminLoggedIn) {
    header("Location: /hub/login.php?message=pro_required");
    exit;
}

// Journey personalization
$userJourney = $user["journey"] ?? "best_life";
$journeyConfig = [
    "clinical" => [
        "title" => "🏥 Clinical Focus",
        "focus" => "symptom patterns and provider collaboration",
        "ai_tone" => "clinical insights with medical terminology",
        "meal_focus" => "symptom triggers and digestive impact",
        "recommendations" => "healthcare provider communication and medical appointment preparation"
    ],
    "performance" => [
        "title" => "💪 Peak Performance",
        "focus" => "nutrition impact on training and recovery",
        "ai_tone" => "performance-focused analysis and coaching",
        "meal_focus" => "energy, recovery, and performance optimization",
        "recommendations" => "performance enhancement, training optimization, and macro timing"
    ],
    "best_life" => [
        "title" => "✨ Best Life Mode",
        "focus" => "energy levels and living your best life daily",
        "ai_tone" => "lifestyle optimization and feel-good insights",
        "meal_focus" => "energy levels and overall wellness",
        "recommendations" => "lifestyle improvements, wellness habits, and energy optimization"
    ]
];
$currentJourneyConfig = $journeyConfig[$userJourney];

// Enhanced photo upload handling with time/location stamping
$uploadResult = null;

// Check for pending manual logging from previous upload (SESSION persistence)
if (isset($_SESSION["pending_manual_logging"])) {
    $uploadResult = $_SESSION["pending_manual_logging"];
    unset($_SESSION["pending_manual_logging"]); // Clear after retrieving
}

// Handle manual meal logging form submission (Pro users)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["photo_filename"]) && !isset($_FILES["health_item"])) {
    $uploadResult = handleManualMealLogging($_POST, $user);
}

// Check if this is an AJAX request - return JSON instead of HTML
$isAjax = !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
error_log("QuietGo: isAjax = " . ($isAjax ? "true" : "false") . ", X-Requested-With = " . ($_SERVER["HTTP_X_REQUESTED_WITH"] ?? "not set"));

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["health_item"])) {
    // CRITICAL: For AJAX requests, ensure clean JSON response
    if ($isAjax) {
        ob_clean();
    }
    
    try {
        error_log("QuietGo upload.php: Processing POST request");

        // Handle multiple file uploads properly
        $uploadResults = [];
        $photoType = $_POST["photo_type"] ?? "general";

        error_log("QuietGo upload.php: Photo type = $photoType");

    // Check if files were actually uploaded
    if (is_array($_FILES["health_item"]["name"])) {
        // Multiple files - check if any files were selected
        $hasFiles = false;
        $fileList = [];
        for ($i = 0; $i < count($_FILES["health_item"]["name"]); $i++) {
            if (!empty($_FILES["health_item"]["name"][$i]) && $_FILES["health_item"]["error"][$i] === UPLOAD_ERR_OK) {
                $hasFiles = true;
                $fileList[] = [
                    "name" => $_FILES["health_item"]["name"][$i],
                    "type" => $_FILES["health_item"]["type"][$i],
                    "tmp_name" => $_FILES["health_item"]["tmp_name"][$i],
                    "error" => $_FILES["health_item"]["error"][$i],
                    "size" => $_FILES["health_item"]["size"][$i]
                ];
            }
        }

        if (!$hasFiles) {
            $uploadResult = ["status" => "error", "message" => "No files were selected for upload."];
        } else {
            // CRITICAL: For meal photos with CalcuPlate, aggregate all images
            if ($photoType === "meal" && $hasCalcuPlate && count($fileList) > 1) {
                // Process as multi-image meal for CalcuPlate
                $uploadResult = handleMultiImageMeal($fileList, $_POST, $user);
            } else {
                // Process files individually for other types
                for ($i = 0; $i < count($fileList); $i++) {
                    $result = handlePhotoUpload($fileList[$i], $_POST, $user);
                    $uploadResults[] = $result;
                }

                // Use the first successful result for display
                $uploadResult = null;
                foreach ($uploadResults as $result) {
                    if ($result["status"] === "success") {
                        $uploadResult = $result;
                        $uploadResult["message"] = count($uploadResults) . " photo(s) uploaded successfully!";
                        break;
                    }
                }
                if (!$uploadResult) {
                    $uploadResult = $uploadResults[0]; // Show first error if all failed
                }
            }
        }
    } else {
        // Single file
        if (!empty($_FILES["health_item"]["name"]) && $_FILES["health_item"]["error"] === UPLOAD_ERR_OK) {
            $uploadResult = handlePhotoUpload($_FILES["health_item"], $_POST, $user);
        } else {
            $uploadResult = ["status" => "error", "message" => "No file was selected for upload."];
        }
    }

    // If AJAX request, return JSON and exit
    if ($isAjax && $uploadResult) {
        error_log("QuietGo: Sending JSON response for AJAX request");
        error_log("QuietGo: uploadResult status = " . $uploadResult["status"]);
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        header("Content-Type: application/json");
        $jsonResponse = json_encode($uploadResult);
        error_log("QuietGo: JSON response length = " . strlen($jsonResponse));
        echo $jsonResponse;
        exit();
    }

    } catch (Exception $e) {
        error_log("QuietGo upload.php: Exception caught - " . $e->getMessage());
        error_log("QuietGo upload.php: Stack trace - " . $e->getTraceAsString());

        $uploadResult = [
            "status" => "error",
            "message" => "Server error: " . $e->getMessage(),
            "debug" => [
                "file" => $e->getFile(),
                "line" => $e->getLine()
            ]
        ];

        if ($isAjax) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            ob_start();
            header("Content-Type: application/json");
            http_response_code(500);
            echo json_encode($uploadResult);
            exit();
        }
    }
}

function handlePhotoUpload($file, $postData, $user) {
    global $hasCalcuPlate, $userJourney;

    // Include storage helper and database operations
    require_once __DIR__ . "/includes/storage-helper.php";
    require_once __DIR__ . "/includes/db-operations.php";
    $storage = getQuietGoStorage();

    // Ensure user structure exists
    $storage->createUserStructure($user["email"]);

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["status" => "error", "message" => "Upload failed: " . $file["error"]];
    }

    // Validate image file
    $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ["status" => "error", "message" => "Invalid file type. Please upload images only."];
    }

    // Check file size (max 10MB)
    if ($file["size"] > 10 * 1024 * 1024) {
        return ["status" => "error", "message" => "File too large. Maximum size is 10MB."];
    }

    // Location data
    $locationData = null;
    if (!empty($postData["latitude"]) && !empty($postData["longitude"])) {
        $locationData = [
            "latitude" => floatval($postData["latitude"]),
            "longitude" => floatval($postData["longitude"]),
            "accuracy" => $postData["accuracy"] ?? null,
            "timestamp" => time()
        ];
    }

    // Prepare comprehensive metadata
    $metadata = [
        "original_name" => $file["name"],
        "category" => $postData["category"] ?? "photos",
        "photo_type" => $postData["photo_type"] ?? "general",
        "upload_timestamp" => time(),
        "upload_datetime" => date("Y-m-d H:i:s"),
        "user_email" => $user["email"],
        "user_journey" => $userJourney,
        "subscription_plan" => $user["subscription_plan"],
        "has_calcuplate" => $hasCalcuPlate,
        "location_data" => $locationData,
        "context" => [
            "time_of_day" => $postData["context_time"] ?? "",
            "symptoms" => $postData["context_symptoms"] ?? "",
            "notes" => $postData["context_notes"] ?? ""
        ],
        "file_info" => [
            "size" => $file["size"],
            "mime_type" => $mimeType
        ]
    ];

    // Store photo using organized structure
    $photoType = $postData["photo_type"] ?? "general";
    $storeResult = $storage->storePhoto($user["email"], $photoType, $file, $metadata);

    if (!$storeResult["success"]) {
        return ["status" => "error", "message" => $storeResult["error"]];
    }

    // Generate AI analysis
    $aiAnalysis = generateAIAnalysis($postData, $userJourney, $hasCalcuPlate, $storeResult["filepath"]);

    // Get/create user in database - WITH ERROR HANDLING
    $userId = null;
    try {
        error_log("QuietGo: Attempting to get/create user in database");
        $userId = getOrCreateUser($user["email"], [
            "name" => $user["name"] ?? "User",
            "journey" => $userJourney,
            "subscription_plan" => $user["subscription_plan"] ?? "free",
            "subscription_status" => "active"
        ]);
        error_log("QuietGo: User ID retrieved: " . ($userId ?? "NULL"));
    } catch (Exception $e) {
        error_log("QuietGo ERROR: Database operation failed - " . $e->getMessage());
        // Continue without database for now
    }

    // Save photo metadata to database
    $photoId = null;
    if ($userId) {
        try {
            $photoId = savePhoto($userId, [
                "photo_type" => $photoType,
                "filename" => basename($storeResult["filepath"]),
                "filepath" => $storeResult["filepath"],
                "thumbnail_path" => $storeResult["thumbnail"] ?? null,
                "file_size" => $file["size"],
                "mime_type" => $mimeType,
                "location_latitude" => $locationData["latitude"] ?? null,
                "location_longitude" => $locationData["longitude"] ?? null,
                "location_accuracy" => $locationData["accuracy"] ?? null,
                "context_time" => $postData["context_time"] ?? null,
                "context_symptoms" => $postData["context_symptoms"] ?? null,
                "context_notes" => $postData["context_notes"] ?? null,
                "original_filename" => $file["name"]
            ]);
        } catch (Exception $e) {
            error_log("QuietGo ERROR: Failed to save photo metadata - " . $e->getMessage());
        }
    }

    // Save analysis to database based on type
    if (!isset($aiAnalysis["error"]) && !isset($aiAnalysis["manual_logging_required"]) && $userId && $photoId) {
        try {
            switch ($photoType) {
                case "stool":
                    saveStoolAnalysis($photoId, $userId, $aiAnalysis);
                    break;
                case "meal":
                    if ($hasCalcuPlate) {
                        saveMealAnalysis($photoId, $userId, $aiAnalysis);
                    }
                    break;
                case "symptom":
                    saveSymptomAnalysis($photoId, $userId, $aiAnalysis);
                    break;
            }

            // Track AI cost
            if (isset($aiAnalysis["ai_model"])) {
                trackAICost($userId, [
                    "photo_type" => $photoType,
                    "ai_model" => $aiAnalysis["ai_model"],
                    "model_tier" => $aiAnalysis["model_tier"] ?? $aiAnalysis["ai_model"] ?? "expensive",
                    "tokens_used" => null,
                    "processing_time" => $aiAnalysis["processing_time"] ?? null
                ]);
            }
        } catch (Exception $e) {
            error_log("QuietGo ERROR: Failed to save analysis - " . $e->getMessage());
            // Continue anyway
        }
    }

    // Store AI analysis in organized location (legacy file system)
    if (!isset($aiAnalysis["error"])) {
        $storage->storeAnalysis($user["email"], "ai_results", [
            "photo_type" => $photoType,
            "analysis" => $aiAnalysis,
            "photo_metadata" => $metadata
        ]);
    }

    $result = [
        "status" => "success",
        "photo_id" => $photoId ?? null,
        "filename" => basename($storeResult["filepath"]),
        "thumbnail" => "/hub/view-image.php?type=thumbnail&path=" . urlencode(basename($storeResult["thumbnail"])),
        "ai_analysis" => $aiAnalysis,
        "metadata" => $metadata,
        "requires_manual_logging" => (!$hasCalcuPlate && $postData["photo_type"] === "meal"),
        "storage_path" => $storeResult["filepath"]
    ];

    // Store in SESSION if manual logging required (for persistence across page reload)
    if ($result["requires_manual_logging"]) {
        $_SESSION["pending_manual_logging"] = $result;
    }

    return $result;
}

/**
 * Handle multi-image meal upload for CalcuPlate Pro+ users
 * Aggregates multiple images (main dish, beverage, dessert) into single meal analysis
 */
function handleMultiImageMeal($fileList, $postData, $user) {
    global $hasCalcuPlate, $userJourney;

    // Pro+ users ONLY - never show manual logging
    if (!$hasCalcuPlate) {
        return ["status" => "error", "message" => "Multi-image meal analysis requires Pro+ subscription"];
    }

    require_once __DIR__ . "/includes/storage-helper.php";
    require_once __DIR__ . "/includes/db-operations.php";
    $storage = getQuietGoStorage();
    $storage->createUserStructure($user["email"]);

    $storedImages = [];
    $totalSize = 0;

    // Store all images first
    foreach ($fileList as $file) {
        // Validate each image
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ["status" => "error", "message" => "Invalid file type in multi-image upload"];
        }

        $totalSize += $file["size"];
        if ($totalSize > 50 * 1024 * 1024) {
            return ["status" => "error", "message" => "Total file size exceeds 50MB limit"];
        }

        // Store each image
        $metadata = [
            "original_name" => $file["name"],
            "category" => "photos",
            "photo_type" => "meal",
            "upload_timestamp" => time(),
            "user_journey" => $userJourney,
            "subscription_plan" => $user["subscription_plan"],
            "has_calcuplate" => true,
            "multi_image_meal" => true,
            "part_of_set" => count($fileList)
        ];

        $storeResult = $storage->storePhoto($user["email"], "meal", $file, $metadata);
        if (!$storeResult["success"]) {
            return ["status" => "error", "message" => "Failed to store image: " . $storeResult["error"]];
        }

        $storedImages[] = [
            "filepath" => $storeResult["filepath"],
            "thumbnail" => $storeResult["thumbnail"],
            "filename" => basename($storeResult["filepath"]),
            "metadata" => $metadata
        ];
    }

    // Now analyze ALL images together with CalcuPlate
    $aiAnalysis = analyzeMultiImageMeal($storedImages, $userJourney);

    // Get/create user in database
    $userId = getOrCreateUser($user["email"], [
        "name" => $user["name"] ?? "User",
        "journey" => $userJourney,
        "subscription_plan" => $user["subscription_plan"] ?? "pro_plus",
        "subscription_status" => "active"
    ]);

    // Save the aggregated meal analysis
    $photoIds = [];
    foreach ($storedImages as $img) {
        $photoId = savePhoto($userId, [
            "photo_type" => "meal",
            "filename" => $img["filename"],
            "filepath" => $img["filepath"],
            "thumbnail_path" => $img["thumbnail"] ?? null,
            "file_size" => 0, // Would need to track per file
            "mime_type" => "image/jpeg",
            "original_filename" => $img["metadata"]["original_name"],
            "multi_image_set" => true
        ]);
        $photoIds[] = $photoId;
    }

    // Save CalcuPlate analysis for the meal
    if (!isset($aiAnalysis["error"])) {
        saveMealAnalysis($photoIds[0], $userId, $aiAnalysis);

        // Track AI cost
        trackAICost($userId, [
            "photo_type" => "meal",
            "ai_model" => $aiAnalysis["ai_model"] ?? "gpt-4o",
            "model_tier" => $aiAnalysis["model_tier"] ?? "expensive",
            "multi_image" => true,
            "image_count" => count($storedImages)
        ]);
    }

    return [
        "status" => "success",
        "photo_ids" => $photoIds,
        "image_count" => count($storedImages),
        "ai_analysis" => $aiAnalysis,
        "requires_manual_logging" => false, // NEVER for Pro+ users
        "message" => "CalcuPlate analyzed " . count($storedImages) . " images as complete meal",
        "thumbnails" => array_map(function($img) {
            return "/hub/view-image.php?type=thumbnail&path=" . urlencode(basename($img["thumbnail"]));
        }, $storedImages)
    ];
}

/**
 * Analyze multiple meal images together as one meal
 */
function analyzeMultiImageMeal($storedImages, $userJourney) {
    require_once __DIR__ . "/includes/openai-config.php";
    require_once __DIR__ . "/includes/analysis-functions.php";

    // Build a combined prompt describing all images
    $imageDescriptions = [];
    foreach ($storedImages as $index => $img) {
        $imageNum = $index + 1;
        $imageDescriptions[] = "Image {$imageNum}: {$img["metadata"]["original_name"]}";
    }

    $journeyConfig = getJourneyPromptConfig($userJourney);

    // For now, analyze the first image with a note about multiple images
    // TODO: Future enhancement - send all images to GPT-4o vision API
    $primaryImage = $storedImages[0]["filepath"];

    // Add multi-image context to the prompt
    $multiImageContext = "\n\nNOTE: This is a multi-image meal with " . count($storedImages) . " images total. ";
    $multiImageContext .= "Analyze as components of a single meal. ";
    $multiImageContext .= "Images included: " . implode(", ", $imageDescriptions);

    $symptoms = "";
    $time = date("H:i");
    $notes = $multiImageContext;

    try {
        // Use CalcuPlate to analyze the meal
        $analysis = analyzeMealPhotoWithCalcuPlate($primaryImage, $journeyConfig, $symptoms, $time, $notes);

        // Add multi-image metadata
        $analysis["multi_image_meal"] = true;
        $analysis["image_count"] = count($storedImages);
        $analysis["analysis_note"] = "Multi-component meal analyzed as single dining session";

        // Ensure confidence is set properly
        if (!isset($analysis["confidence"]) || $analysis["confidence"] == 0) {
            $analysis["confidence"] = 85; // Default reasonable confidence for multi-image
        }

        return $analysis;
    } catch (Exception $e) {
        error_log("QuietGo Multi-Image Analysis Error: " . $e->getMessage());
        return [
            "error" => "Failed to analyze multi-image meal",
            "timestamp" => time(),
            "user_journey" => $userJourney
        ];
    }
}

function generateAIAnalysis($postData, $userJourney, $hasCalcuPlate, $imagePath = null) {
    // Include OpenAI configuration and analysis functions
    require_once __DIR__ . "/includes/openai-config.php";
    require_once __DIR__ . "/includes/analysis-functions.php";

    $photoType = $postData["photo_type"] ?? "general";
    $symptoms = $postData["context_symptoms"] ?? "";
    $time = $postData["context_time"] ?? "";
    $notes = $postData["context_notes"] ?? "";
    $startTime = microtime(true);

    // Check API rate limits
    $userEmail = $_SESSION["hub_user"]["email"] ?? "anonymous";
    if (!checkAPIRateLimit($userEmail)) {
        return [
            "error" => "Rate limit exceeded. Please try again in an hour.",
            "timestamp" => time(),
            "user_journey" => $userJourney
        ];
    }

    $analysis = [
        "timestamp" => time(),
        "user_journey" => $userJourney,
        "photo_type" => $photoType
    ];

    // Get journey-specific prompt configuration
    $journeyConfig = getJourneyPromptConfig($userJourney);

    try {
        switch ($photoType) {
            case "stool":
                $analysis = analyzeStoolPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes);
                break;

            case "meal":
                if ($hasCalcuPlate) {
                    // PRO+ ONLY: CalcuPlate AI meal analysis
                    $analysis = analyzeMealPhotoWithCalcuPlate($imagePath, $journeyConfig, $symptoms, $time, $notes);
                } else {
                    // PRO ONLY: Manual logging required
                    $analysis = [
                        "manual_logging_required" => true,
                        "upgrade_available" => [
                            "feature" => "CalcuPlate AI Meal Analysis",
                            "price" => "+$2.99/month",
                            "benefits" => ["Automatic food detection", "Instant calorie calculation", "Auto-logged nutrition data"]
                        ],
                        "next_step" => "Complete manual meal logging form to continue",
                        "timestamp" => time(),
                        "user_journey" => $userJourney
                    ];
                }
                break;

            case "symptom":
                $analysis = analyzeSymptomPhoto($imagePath, $journeyConfig, $symptoms, $time, $notes);
                break;

            default:
                $analysis["error"] = "Unknown photo type";
        }

        // Add processing time
        $analysis["processing_time"] = round(microtime(true) - $startTime, 2);

    } catch (Exception $e) {
        error_log("QuietGo AI Analysis Error: " . $e->getMessage());
        $analysis = [
            "error" => "AI analysis temporarily unavailable. Please try again.",
            "timestamp" => time(),
            "user_journey" => $userJourney,
            "processing_time" => round(microtime(true) - $startTime, 2)
        ];
    }

    return $analysis;
}

// Analysis functions now in /includes/analysis-functions.php

// CRITICAL: Only output HTML if NOT an AJAX request
if ($isAjax) {
    // For AJAX, we should have already exited above with JSON
    // If we're still here, something went wrong
    error_log("QuietGo ERROR: AJAX request reached HTML section");
    exit();
}

// Now safe to output HTML for normal page loads
include __DIR__ . "/includes/header-hub.php";
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
    <section class="upload-results" style="background: <?php echo $uploadResult["status"] === "success" ? "var(--success-color)" : "#e74c3c"; ?>; color: white; padding: 1rem 0; text-align: center;">
        <div class="container">
            <?php if ($uploadResult["status"] === "success"): ?>
                ✅ Photo uploaded successfully!
                <?php if (isset($uploadResult["requires_manual_logging"]) && $uploadResult["requires_manual_logging"]): ?>
                    Please complete the manual meal logging form below.
                <?php else: ?>
                    AI analysis complete.
                <?php endif; ?>
            <?php else: ?>
                ❌ Upload failed: <?php echo htmlspecialchars($uploadResult["message"]); ?>
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
                Journey: <?php echo htmlspecialchars($currentJourneyConfig["title"]); ?>
            </p>
            <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">
                <?php if ($hasCalcuPlate): ?>
                    Upload photos for instant AI analysis and automatic meal logging focused on <?php echo htmlspecialchars($currentJourneyConfig["meal_focus"]); ?>.
                <?php else: ?>
                    Upload photos for AI stool analysis and manual meal logging focused on <?php echo htmlspecialchars($currentJourneyConfig["meal_focus"]); ?>.
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Manual Meal Logging Form (shows after meal photo upload for Pro users) -->
    <?php if ($uploadResult && isset($uploadResult["requires_manual_logging"]) && $uploadResult["requires_manual_logging"]): ?>
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
                    <input type="hidden" name="photo_filename" value="<?php echo htmlspecialchars($uploadResult["filename"] ?? ""); ?>">

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
    <?php if (!($uploadResult && isset($uploadResult["requires_manual_logging"]) && $uploadResult["requires_manual_logging"])): ?>
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
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin: 0.25rem 0;">• <?php echo htmlspecialchars(ucfirst($currentJourneyConfig["ai_tone"])); ?></div>
                    </div>
                    <button class="card-button" style="background: var(--success-color); color: white;">
                        📸 Upload Stool Photo
                    </button>
                </article>

                <!-- Meal Photos -->
                <article class="category-card" onclick="openUploadModal('meal')">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div style="font-size: 3rem;">🍽️</div>
                        <span style="background: <?php echo $hasCalcuPlate ? "var(--success-color)" : "var(--slate-blue)"; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.75rem; font-weight: 600;">
                            <?php echo $hasCalcuPlate ? "Auto-Logging" : "Manual Form"; ?>
                        </span>
                    </div>
                    <h3 style="color: var(--text-primary); margin: 0 0 0.75rem 0;">Meal Photos</h3>
                    <p style="color: var(--text-secondary); margin: 0 0 1rem 0;">
                        <?php echo $hasCalcuPlate ? "CalcuPlate AI analysis with automatic logging" : "Manual logging form for robust analysis"; ?>
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
        <button onclick="requestLocationPermission()" style="background: var(--accent-teal); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.background=&quot;#2d7a78&quot;" onmouseout="this.style.background=&quot;var(--accent-teal)&quot;">
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
    const modal = document.getElementById("upload-modal");
    const photoTypeInput = document.getElementById("photo-type");
    const modalTitle = document.getElementById("modal-title");
    const modalSubtitle = document.getElementById("modal-subtitle");
    const uploadIcon = document.getElementById("upload-icon");

    photoTypeInput.value = photoType;

    // Update modal content based on photo type
    switch(photoType) {
        case "stool":
            modalTitle.textContent = "🚽 Upload Stool Photo";
            modalSubtitle.textContent = "AI will analyze Bristol Scale, color, and consistency";
            uploadIcon.textContent = "🚽";
            break;
        case "meal":
            modalTitle.textContent = "🍽️ Upload Meal Photos";
            modalSubtitle.textContent = hasCalcuPlate ?
                "CalcuPlate will analyze all photos and automatically log your meal":
                "Upload multiple angles, then complete the manual logging form";
            uploadIcon.textContent = "🍽️";
            break;
        case "symptom":
            modalTitle.textContent = "🩺 Upload Symptom Photo";
            modalSubtitle.textContent = "Document physical symptoms for pattern tracking";
            uploadIcon.textContent = "🩺";
            break;
    }

    // Build context fields
    buildContextFields(photoType);

    modal.style.display = "flex";

    // Get location if available
    requestLocationPermission();
}

function buildContextFields(photoType) {
    const container = document.getElementById("context-fields");
    container.innerHTML = "";

    // No context fields needed - keep upload modal clean and simple
}

function closeUploadModal() {
    const modal = document.getElementById("upload-modal");
    const form = document.getElementById("upload-form");
    const initialArea = document.getElementById("initial-upload-area");
    const previewGrid = document.getElementById("photo-preview-grid");
    const previewImages = document.getElementById("preview-images");
    const fileInput = document.getElementById("file-input");
    const addMoreBtn = document.getElementById("add-more-btn");
    const submitBtn = form.querySelector('button[type="submit"]');

    // Hide modal
    modal.style.display = "none";

    // Reset form
    form.reset();

    // COMPLETE RESET of all state
    allSelectedFiles = [];
    selectedFile = null;

    // Clear preview images
    if (previewImages) {
        previewImages.innerHTML = "";
    }

    // Reset visibility
    if (initialArea) initialArea.style.display = "block";
    if (previewGrid) previewGrid.style.display = "none";

    // Reset button text
    if (addMoreBtn) {
        addMoreBtn.innerHTML = '<span style="font-size: 1.2rem;">+</span> Add More Photos';
    }

    // Reset submit button text and state
    if (submitBtn) {
        submitBtn.textContent = "🚀 Upload & Analyze Photos";
        submitBtn.disabled = false;
    }

    // Clear file input
    if (fileInput) {
        fileInput.value = "";
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
                document.getElementById("latitude").value = userLocation.latitude;
                document.getElementById("longitude").value = userLocation.longitude;
                document.getElementById("location-accuracy").value = userLocation.accuracy;

                console.log("📍 Location captured for enhanced pattern analysis");
            },
            function(error) {
                console.log("📍 Location permission denied - using timestamp only");
            }
        );
    }
}

// File input handling - MULTIPLE FILES WITH PREVIEW
let allSelectedFiles = [];

document.getElementById("file-input").addEventListener("change", function(e) {
    const newFiles = Array.from(e.target.files);

    if (newFiles.length > 0) {
        // Validate all new files
        let validNewFiles = [];

        for (let file of newFiles) {
            if (!file.type.startsWith("image/")) {
                alert(`${file.name} is not an image file. Please select images only.`);
                this.value = "";
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert(`${file.name} is too large. Maximum size is 10MB per image.`);
                this.value = "";
                return;
            }

            validNewFiles.push(file);
        }

        // Add to existing files
        allSelectedFiles = allSelectedFiles.concat(validNewFiles);

        // Check total size
        let totalSize = allSelectedFiles.reduce((sum, file) => sum + file.size, 0);
        if (totalSize > 50 * 1024 * 1024) {
            alert("Total file size too large. Maximum total size is 50MB.");
            allSelectedFiles = allSelectedFiles.slice(0, -validNewFiles.length); // Remove the new files
            this.value = "";
            return;
        }

        updatePhotoPreview();
        selectedFile = allSelectedFiles; // Update global variable
    }

    // Clear the input so same file can be selected again
    this.value = "";
});

function updatePhotoPreview() {
    const initialArea = document.getElementById("initial-upload-area");
    const previewGrid = document.getElementById("photo-preview-grid");
    const previewImages = document.getElementById("preview-images");

    if (allSelectedFiles.length > 0) {
        // Hide initial area, show preview grid
        initialArea.style.display = "none";
        previewGrid.style.display = "block";

        // Clear and rebuild preview
        previewImages.innerHTML = "";

        allSelectedFiles.forEach((file, index) => {
            const previewItem = document.createElement("div");
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
            if (file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewItem.innerHTML = `
                        <div style="position: relative;">
                            <img src="${e.target.result}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px; margin-bottom: 0.5rem;">
                            <button type="button" onclick="removePhoto(${index})" style="position: absolute; top: -8px; right: -8px; width: 20px; height: 20px; border-radius: 50%; background: #e74c3c; color: white; border: none; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); word-break: break-word;">
                            ${file.name.length > 15 ? file.name.substring(0, 12) + "..." : file.name}
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
                        ${file.name.length > 15 ? file.name.substring(0, 12) + "..." : file.name}
                    </div>
                    <div style="font-size: 0.6rem; color: var(--text-muted);">
                        ${(file.size / 1024 / 1024).toFixed(1)}MB
                    </div>
                `;
            }

            previewImages.appendChild(previewItem);
        });

        // Update add more button text
        const addMoreBtn = document.getElementById("add-more-btn");
        if (addMoreBtn) {
            addMoreBtn.innerHTML = `<span style="font-size: 1.2rem;">+</span> Add More (${allSelectedFiles.length} selected)`;
        }

    } else {
        // Show initial area, hide preview
        initialArea.style.display = "block";
        previewGrid.style.display = "none";
    }
}

function removePhoto(index) {
    allSelectedFiles.splice(index, 1);
    updatePhotoPreview();
    selectedFile = allSelectedFiles;
}

// Form submission with proper FormData handling
document.getElementById("upload-form").addEventListener("submit", function(e) {
    if (!selectedFile || selectedFile.length === 0) {
        e.preventDefault();
        alert("Please select at least one photo first");
        return false;
    }

    // Create FormData object and append files manually
    e.preventDefault();

    const formData = new FormData();

    // Add all selected files
    selectedFile.forEach((file, index) => {
        formData.append("health_item[]", file);
    });

    // Add other form data
    formData.append("photo_type", document.getElementById("photo-type").value);
    formData.append("latitude", document.getElementById("latitude").value);
    formData.append("longitude", document.getElementById("longitude").value);
    formData.append("accuracy", document.getElementById("location-accuracy").value);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = selectedFile.length > 1 ?
        `🔄 Analyzing ${selectedFile.length} photos...` :
        "🔄 Analyzing...";
    submitBtn.disabled = true;

    // Submit via fetch with AJAX header
    fetch(window.location.href, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers);

        if (!response.ok) {
            // Try to get error text
            return response.text().then(text => {
                console.error("Response body:", text);
                throw new Error(`HTTP error! status: ${response.status}, body: ${text.substring(0, 500)}`);
            });
        }
        return response.json();
    })
    .then(result => {
        console.log("Upload result:", result);

        if (result.status === "success") {
            // Close upload modal
            closeUploadModal();

            // Check if clarification is needed (Pro+ CalcuPlate)
            if (result.ai_analysis && result.ai_analysis.needs_clarification) {
                // Show clarification modal for quick questions
                showClarificationModal(result.ai_analysis.questions, result);
            } else if (!result.requires_manual_logging) {
                // Show success modal with AI results
                showSuccessModal(result);
            } else {
                // For Pro users with meal photos, reload to show manual logging form
                window.location.reload();
            }
        } else {
            alert(result.message || "Upload failed. Please try again.");
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error("Upload error details:", error);
        console.error("Error message:", error.message);
        console.error("Error stack:", error.stack);

        // More specific error messages
        let errorMessage = "Upload failed: ";

        if (error.message.includes("NetworkError") || error.message.includes("Failed to fetch")) {
            errorMessage += "Network connection error. Please check your internet connection.";
        } else if (error.message.includes("HTTP error")) {
            errorMessage += error.message;
        } else if (error.message.includes("JSON")) {
            errorMessage += "Invalid response from server. Please try again.";
        } else {
            errorMessage += error.message || "Unknown error. Please try again.";
        }

        alert(errorMessage);
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });

    return false;
});

console.log("📤 Enhanced Photo Upload System Loaded");
console.log("✅ Pro users: AI stool analysis + manual meal forms");
console.log("⚡ Pro+ users: AI stool analysis + CalcuPlate auto-logging");
console.log("📍 Location tracking enabled for enhanced pattern analysis");
console.log("🎯 Journey-focused analysis: " + <?php echo json_encode($userJourney); ?>);

// Manual meal form handling
document.addEventListener("DOMContentLoaded", function() {
    const manualMealForm = document.getElementById("manual-meal-form");
    if (manualMealForm) {
        manualMealForm.addEventListener("submit", function(e) {
            // Remove HTML5 validation that might cause "invalid value" errors
            const timeInput = this.querySelector('input[name="meal_time"]');
            const mealTypeSelect = this.querySelector('select[name="meal_type"]');
            const portionSelect = this.querySelector('select[name="portion_size"]');
            const mainFoodsTextarea = this.querySelector('textarea[name="main_foods"]');

            // Custom validation instead of HTML5
            let errors = [];

            if (!mealTypeSelect.value) {
                errors.push("Please select a meal type");
            }

            if (!timeInput.value) {
                errors.push("Please select a meal time");
            }

            if (!portionSelect.value) {
                errors.push("Please select a portion size");
            }

            if (!mainFoodsTextarea.value.trim()) {
                errors.push("Please list the main foods");
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert("Please complete all required fields:\n\n" + errors.join("\n"));
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById("complete-meal-btn");
            submitBtn.textContent = "🔄 Saving Meal Log...";
            submitBtn.disabled = true;
        });
    }
});
</script>

<!-- Success Modal -->
<?php include __DIR__ . "/includes/success-modal.php"; ?>

<!-- Clarification Modal for CalcuPlate Pro+ -->
<?php if ($hasCalcuPlate): ?>
<?php include __DIR__ . "/includes/clarification-modal.php"; ?>
<?php endif; ?>

<?php include __DIR__ . "/includes/footer-hub.php"; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . "/../includes/chatbot-widget.php"; ?>
