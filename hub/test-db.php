<?php
// Simple database connection test
echo "<h1>Database Connection Test</h1><pre>";

// Check if SQLite extension is loaded
if (!extension_loaded('sqlite3') && !extension_loaded('pdo_sqlite')) {
    echo "❌ SQLite extension is not loaded in PHP\n";
    echo "Required: Either sqlite3 or pdo_sqlite extension\n";
} else {
    echo "✅ SQLite extension is loaded\n";
}

// Check db-operations.php
$dbOpsFile = __DIR__ . '/includes/db-operations.php';
if (file_exists($dbOpsFile)) {
    echo "✅ db-operations.php found\n";
    
    // Try to include it
    try {
        require_once $dbOpsFile;
        echo "✅ db-operations.php loaded successfully\n";
        
        // Check if functions exist
        if (function_exists('getDBConnection')) {
            echo "✅ getDBConnection() function exists\n";
            
            // Try to connect
            try {
                $db = getDBConnection();
                if ($db) {
                    echo "✅ Database connection successful\n";
                } else {
                    echo "❌ Database connection returned null\n";
                }
            } catch (Exception $e) {
                echo "❌ Database connection error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ getDBConnection() function not found\n";
        }
        
        // Check other required functions
        $requiredFunctions = [
            'getOrCreateUser',
            'savePhoto', 
            'saveStoolAnalysis',
            'saveMealAnalysis',
            'saveSymptomAnalysis',
            'trackAICost'
        ];
        
        echo "\nDatabase functions check:\n";
        foreach ($requiredFunctions as $func) {
            if (function_exists($func)) {
                echo "✅ $func() exists\n";
            } else {
                echo "❌ $func() missing\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error loading db-operations.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ db-operations.php not found at: $dbOpsFile\n";
}

// Check database file
$dbFile = __DIR__ . '/QuietGoData/quietgo.db';
if (file_exists($dbFile)) {
    echo "\n✅ Database file exists at: $dbFile\n";
    echo "   Size: " . number_format(filesize($dbFile) / 1024, 2) . " KB\n";
    echo "   Writable: " . (is_writable($dbFile) ? '✅' : '❌') . "\n";
    echo "   Directory writable: " . (is_writable(dirname($dbFile)) ? '✅' : '❌') . "\n";
} else {
    echo "\n❌ Database file not found at: $dbFile\n";
    echo "   Directory exists: " . (is_dir(dirname($dbFile)) ? '✅' : '❌') . "\n";
    echo "   Directory writable: " . (is_writable(dirname($dbFile)) ? '✅' : '❌') . "\n";
}

echo "</pre>";
?>
