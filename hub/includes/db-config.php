<?php
/**
 * QuietGo Database Configuration
 * Connects to Hostinger MySQL database for storing analysis data
 */

// Load environment variables if not already loaded
if (!isset($_ENV['DB_HOST'])) {
    // Load .env file
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    } else {
        die('ERROR: .env file not found at: ' . $envFile);
    }
}

// Database credentials from environment
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

/**
 * Get database connection
 * @return PDO|null
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        // Check if MySQL credentials are available
        if (!empty(DB_NAME) && !empty(DB_USER)) {
            // Try MySQL connection
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                error_log("QuietGo DB: Connected successfully to MySQL - " . DB_NAME);
            } catch (PDOException $e) {
                error_log("QuietGo DB MySQL Connection Error: " . $e->getMessage());
                // Fall through to SQLite
            }
        }
        
        // Fallback to SQLite for local development
        if ($pdo === null) {
            try {
                $dbFile = __DIR__ . '/../QuietGoData/quietgo.db';
                $dbDir = dirname($dbFile);
                
                // Create directory if it doesn't exist
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                
                $pdo = new PDO('sqlite:' . $dbFile, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                error_log("QuietGo DB: Using SQLite database at " . $dbFile);
                
                // Create tables if they don't exist
                createTablesIfNeeded($pdo);
                
            } catch (PDOException $e) {
                error_log("QuietGo DB SQLite Error: " . $e->getMessage());
                return null;
            }
        }
    }
    
    return $pdo;
}

/**
 * Create database tables if they don't exist (for SQLite)
 * @param PDO $pdo
 */
function createTablesIfNeeded($pdo) {
    // Check if tables exist
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($result->fetch()) {
        return; // Tables already exist
    }
    
    error_log("QuietGo DB: Creating database tables...");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        name TEXT,
        journey TEXT DEFAULT 'best_life',
        subscription_plan TEXT DEFAULT 'free',
        subscription_status TEXT DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create photos table
    $pdo->exec("CREATE TABLE IF NOT EXISTS photos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        photo_type TEXT,
        filename TEXT,
        filepath TEXT,
        thumbnail_path TEXT,
        file_size INTEGER,
        mime_type TEXT,
        location_latitude REAL,
        location_longitude REAL,
        location_accuracy REAL,
        context_time TEXT,
        context_symptoms TEXT,
        context_notes TEXT,
        original_filename TEXT,
        upload_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create stool_analyses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS stool_analyses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        user_id INTEGER,
        bristol_scale INTEGER,
        bristol_description TEXT,
        color_assessment TEXT,
        consistency TEXT,
        volume_estimate TEXT,
        confidence_score INTEGER,
        health_insights TEXT,
        recommendations TEXT,
        reported_symptoms TEXT,
        correlation_note TEXT,
        ai_model TEXT,
        processing_time REAL,
        analysis_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create meal_analyses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS meal_analyses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        user_id INTEGER,
        foods_detected TEXT,
        total_calories INTEGER,
        protein_grams REAL,
        carbs_grams REAL,
        fat_grams REAL,
        fiber_grams REAL,
        meal_quality_score TEXT,
        portion_sizes TEXT,
        nutritional_completeness TEXT,
        confidence_score INTEGER,
        nutrition_insights TEXT,
        recommendations TEXT,
        journey_specific_note TEXT,
        ai_model TEXT,
        model_tier TEXT,
        cost_tier TEXT,
        processing_time REAL,
        analysis_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create symptom_analyses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS symptom_analyses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        photo_id INTEGER,
        user_id INTEGER,
        symptom_category TEXT,
        severity_estimate TEXT,
        visual_characteristics TEXT,
        confidence_score INTEGER,
        tracking_recommendations TEXT,
        correlation_potential TEXT,
        reported_symptoms TEXT,
        ai_model TEXT,
        processing_time REAL,
        analysis_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create ai_cost_tracking table
    $pdo->exec("CREATE TABLE IF NOT EXISTS ai_cost_tracking (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        photo_type TEXT,
        ai_model TEXT,
        model_tier TEXT,
        cost_estimate REAL,
        tokens_used INTEGER,
        processing_time REAL,
        request_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    
    // Create manual_meal_logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS manual_meal_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        photo_id INTEGER,
        meal_type TEXT,
        meal_time TEXT,
        meal_date DATE,
        portion_size TEXT,
        main_foods TEXT,
        estimated_calories INTEGER,
        protein_grams REAL,
        carb_grams REAL,
        fat_grams REAL,
        hunger_before TEXT,
        fullness_after TEXT,
        energy_level INTEGER,
        meal_notes TEXT,
        log_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (photo_id) REFERENCES photos(id)
    )");
    
    error_log("QuietGo DB: Tables created successfully");
}

/**
 * Test database connection
 * @return bool
 */
function testDBConnection() {
    $db = getDBConnection();
    if (!$db) {
        return false;
    }
    
    try {
        $stmt = $db->query("SELECT 1");
        return $stmt !== false;
    } catch (PDOException $e) {
        error_log("QuietGo DB Test Failed: " . $e->getMessage());
        return false;
    }
}

// Security: Clear database password from memory
unset($_ENV['DB_PASS']);

?>
