<?php
/**
 * Simple script to run database.sql migrations automatically.
 * Useful for cloud deployments like Render.
 */

// Allow only CLI or a secure method to run this
if (php_sapi_name() !== 'cli' && !isset($_GET['secure_key'])) {
    // Note: If you want to run this from browser on render, add ?secure_key=some_secret 
    // to the URL, but CLI is preferred (e.g., in a start script).
    echo "Please run this from CLI or provide secure_key.";
    exit;
}

require_once __DIR__ . '/index.php'; // This loads .env and defines BASE_PATH
require_once __DIR__ . '/database.php';

try {
    $pdo = getDB();
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    if ($sql === false) {
        die("Could not read database.sql\n");
    }
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "Database migrated successfully!\n";
} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
}
