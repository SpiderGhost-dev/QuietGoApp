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
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            error_log("QuietGo DB: Connected successfully to " . DB_NAME);
        } catch (PDOException $e) {
            error_log("QuietGo DB Connection Error: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
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
