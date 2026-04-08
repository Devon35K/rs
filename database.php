<?php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'acadportal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbHost = DB_HOST;
        $dbName = DB_NAME;
        $dbUser = DB_USER;
        $dbPass = DB_PASS;
        
        // Render usually provides DATABASE_URL
        $dbUrl = getenv('DATABASE_URL');
        if ($dbUrl) {
            $parsedUrl = parse_url($dbUrl);
            if ($parsedUrl !== false) {
                $dbHost = $parsedUrl['host'] ?? $dbHost;
                $dbName = ltrim($parsedUrl['path'] ?? '', '/') ?: $dbName;
                $dbUser = $parsedUrl['user'] ?? $dbUser;
                $dbPass = $parsedUrl['pass'] ?? $dbPass;
            }
        }

        $dsn = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}