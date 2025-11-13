<?php

// The config/database.php should be required once, typically by the entry point (index.php)
// or by scripts like the setup_database.php.
// If this class is autoloaded, ensure config/database.php is loaded before its first use.
// require_once __DIR__ . '/../../config/database.php'; // Potentially remove if loaded globally

class Database {
    private static $pdoInstance = null;

    // Private constructor to prevent direct creation of object
    private function __construct() {}

    // Private clone method to prevent cloning of the instance
    private function __clone() {}

    // Private unserialize method to prevent unserializing of the instance
    public function __wakeup() {}

    /**
     * Returns the PDO instance for connecting to the specific database (DB_NAME).
     * If an instance already exists, it returns that instance.
     * If not, it creates a new PDO connection.
     *
     * @return PDO The PDO instance.
     * @throws PDOException if the connection fails.
     */
    public static function getInstance(): PDO {
        if (self::$pdoInstance === null) {
            // Ensure config constants are available
            if (!defined('DSN_DB')) {
                // Attempt to load config if not already loaded. This is a fallback.
                // Ideally, config/database.php is loaded by the calling script/application entry point.
                $configFile = __DIR__ . '/../../config/database.php';
                if (file_exists($configFile)) {
                    require_once $configFile;
                } else {
                    throw new PDOException("Database configuration file not found or DSN_DB not defined.");
                }
            }

            try {
                // DSN_DB, DB_USER, DB_PASS, and $pdo_options are defined in config/database.php
                self::$pdoInstance = new PDO(DSN_DB, DB_USER, DB_PASS, $GLOBALS['pdo_options'] ?? [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log("Database Connection Error (to DB): " . $e->getMessage());
                throw new PDOException("Database connection failed (to DB): " . $e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$pdoInstance;
    }

    /**
     * A utility method to test the connection to the specific database.
     */
    public static function testConnection(): bool {
        try {
            self::getInstance();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
