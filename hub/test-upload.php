<?php
// Test QuietGo Upload System
session_start();

// Test user setup
$_SESSION['hub_user'] = [
    'email' => 'test@quietgo.app',
    'name' => 'Test User',
    'subscription_plan' => 'pro_plus',
    'journey' => 'best_life'
];

echo "<h1>QuietGo Upload System Test</h1>\n";
echo "<pre>\n";

// Check required files
$requiredFiles = [
    __DIR__ . '/includes/storage-helper.php',
    __DIR__ . '/includes/db-operations.php',
    __DIR__ . '/includes/openai-config.php',
    __DIR__ . '/includes/analysis-functions.php',
    __DIR__ . '/../.env'
];

echo "=== File Check ===\n";
foreach ($requiredFiles as $file) {
    $exists = file_exists($file) ? '✅' : '❌';
    echo "$exists " . basename($file) . "\n";
    
    if (!file_exists($file)) {
        echo "   ERROR: Missing file: $file\n";
    }
}

// Check functions
echo "\n=== Function Check ===\n";
$functionsToCheck = [
    'getQuietGoStorage',
    'getJourneyPromptConfig',
    'checkAPIRateLimit',
    'makeOpenAIRequest',
    'analyzeStoolPhoto',
    'analyzeMealPhotoWithCalcuPlate',
    'encodeImageForOpenAI'
];

// Load required files
require_once __DIR__ . '/includes/storage-helper.php';
require_once __DIR__ . '/includes/openai-config.php';
require_once __DIR__ . '/includes/analysis-functions.php';

foreach ($functionsToCheck as $func) {
    $exists = function_exists($func) ? '✅' : '❌';
    echo "$exists $func()\n";
}

// Check database
echo "\n=== Database Check ===\n";
if (file_exists(__DIR__ . '/includes/db-operations.php')) {
    require_once __DIR__ . '/includes/db-operations.php';
    
    // Try to get database connection
    try {
        $db = getDBConnection();
        if ($db) {
            echo "✅ Database connection successful\n";
            
            // Check tables
            $tables = ['users', 'photos', 'stool_analyses', 'meal_analyses'];
            foreach ($tables as $table) {
                $result = $db->query("SELECT 1 FROM $table LIMIT 1");
                if ($result !== false) {
                    echo "✅ Table '$table' exists\n";
                } else {
                    echo "❌ Table '$table' missing or inaccessible\n";
                }
            }
        } else {
            echo "❌ Database connection failed\n";
        }
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ db-operations.php not found\n";
}

// Check directories
echo "\n=== Directory Check ===\n";
$directories = [
    __DIR__ . '/QuietGoData',
    __DIR__ . '/QuietGoData/users',
    __DIR__ . '/QuietGoData/system/cache'
];

foreach ($directories as $dir) {
    $exists = is_dir($dir) ? '✅' : '❌';
    $writable = is_writable($dir) ? '✅' : '❌';
    echo "$exists Directory: " . basename($dir) . " (Writable: $writable)\n";
}

// Check API keys
echo "\n=== API Keys Check ===\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    $hasOpenAI = strpos($envContent, 'OPENAI_API_KEY') !== false ? '✅' : '❌';
    $hasAnthropic = strpos($envContent, 'ANTHROPIC_API_KEY') !== false ? '✅' : '❌';
    
    echo "$hasOpenAI OPENAI_API_KEY configured\n";
    echo "$hasAnthropic ANTHROPIC_API_KEY configured\n";
} else {
    echo "❌ .env file not found\n";
}

echo "\n=== PHP Info ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Max Upload Size: " . ini_get('upload_max_filesize') . "\n";
echo "Post Max Size: " . ini_get('post_max_size') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";

echo "</pre>\n";

// Test form
?>

<h2>Test Upload Form</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="photo_type" value="meal">
    <input type="file" name="health_item[]" accept="image/*" required>
    <button type="submit">Test Upload</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['health_item'])) {
    echo "<h3>Upload Test Results:</h3><pre>";
    
    echo "Files received:\n";
    print_r($_FILES);
    
    echo "\nPOST data:\n";
    print_r($_POST);
    
    echo "</pre>";
}
?>
