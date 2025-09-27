<?php
/**
 * Secure Environment Variable Loader for QuietGo
 * Loads environment variables from .env file safely
 */

class SecureEnvLoader {
    private static $loaded = false;
    private static $env = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($envPath = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($envPath === null) {
            $envPath = dirname(__DIR__, 2) . '/.env';
        }
        
        if (!file_exists($envPath)) {
            error_log("Warning: .env file not found at $envPath");
            return;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^["\'](.+)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Store in environment and internal array
                $_ENV[$key] = $value;
                putenv("$key=$value");
                self::$env[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable with optional default
     */
    public static function get($key, $default = null) {
        self::load();
        
        // Check $_ENV first, then internal array, then default
        return $_ENV[$key] ?? self::$env[$key] ?? $default;
    }
    
    /**
     * Check if running in development mode
     */
    public static function isDevelopment() {
        return self::get('APP_ENV', 'development') === 'development';
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        self::load();
        return isset($_ENV[$key]) || isset(self::$env[$key]);
    }
}

// Auto-load environment variables when this file is included
SecureEnvLoader::load();
?>
