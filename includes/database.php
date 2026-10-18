<?php
/**
 * MIDHILA VOYAGES — Database Connection Handler
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Failed: " . $e->getMessage());
                // Safe friendly error message to visitor
                http_response_code(500);
                echo '<div style="font-family:system-ui,sans-serif;max-width:600px;margin:50px auto;padding:24px;background:#FEF2F2;border:1px solid #FCA5A5;border-radius:12px;color:#991B1B;">';
                echo '<h2 style="margin-top:0;">Database Connection Notice</h2>';
                echo '<p>Unable to connect to the database. Please ensure MySQL is running in XAMPP and the database <code>' . htmlspecialchars(DB_NAME) . '</code> has been imported.</p>';
                echo '</div>';
                exit;
            }
        }
        return self::$instance;
    }
}

/**
 * Global helper to access PDO instance
 */
function getDB(): PDO {
    return Database::getConnection();
}
