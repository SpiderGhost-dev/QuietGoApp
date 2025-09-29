<?php
/**
 * QuietGo Database Setup Script
 * Run this ONCE to create all database tables
 */

// Prevent running in production accidentally
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("⚠️ Database setup script. Add ?confirm=yes to URL to run.");
}

require_once __DIR__ . '/db-config.php';

echo "<h1>QuietGo Database Setup</h1>";
echo "<p>Creating database tables...</p>";

// Get database connection
$db = getDBConnection();

if (!$db) {
    die("<p style='color: red;'>❌ Database connection failed! Check your credentials in .env file.</p>");
}

echo "<p style='color: green;'>✅ Database connected successfully!</p>";

// Read SQL schema file
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    die("<p style='color: red;'>❌ Schema file not found: $schemaFile</p>");
}

$sql = file_get_contents($schemaFile);

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && strpos($stmt, '--') !== 0;
    }
);

echo "<p>Found " . count($statements) . " SQL statements to execute...</p>";

// Execute each statement
$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    try {
        // Skip comments and empty statements
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        $db->exec($statement);
        $success++;
        
        // Extract table name for reporting
        if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
            echo "<p style='color: green;'>✅ Created table: {$matches[1]}</p>";
        }
    } catch (PDOException $e) {
        $errors++;
        echo "<p style='color: orange;'>⚠️ Statement failed (might already exist): " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
        echo "<p style='color: gray; font-size: 0.9em;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<h2>Setup Complete!</h2>";
echo "<p><strong>$success</strong> statements executed successfully</p>";
if ($errors > 0) {
    echo "<p style='color: orange;'><strong>$errors</strong> statements failed (likely tables already exist)</p>";
}

// Verify tables were created
try {
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Database Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>✅ $table</li>";
    }
    echo "</ul>";
    
    echo "<p style='background: #d4edda; padding: 1rem; border-radius: 5px; color: #155724;'>";
    echo "<strong>🎉 Database setup successful!</strong><br>";
    echo "Your QuietGo database is ready with " . count($tables) . " tables.";
    echo "</p>";
    
    echo "<p><a href='/hub/'>← Back to Hub</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error verifying tables: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
