<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); // Default MySQL port, can be changed
define('DB_NAME', 'astronumerology_db'); // Choose a database name
define('DB_USER', 'root'); // Your database username - CHANGE THIS FOR PRODUCTION
define('DB_PASS', ''); // Your database password - CHANGE THIS FOR PRODUCTION
define('DB_CHARSET', 'utf8mb4');

// DSN for connecting to the MySQL server (without specifying a database initially for creation)
define('DSN_SERVER', "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET);

// DSN for connecting to the specific database
define('DSN_DB', "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET);

// Global options for PDO, accessible in Database.php or setup scripts
$GLOBALS['pdo_options'] = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

// Note: For a real application, use environment variables for these settings
// or a more robust configuration management system.
// DO NOT commit actual credentials for a production database.

// Application Settings
define('APP_NAME', 'AstroNumero');
define('ADMIN_EMAIL', 'admin@astronumerology.example.com'); // For error reports or admin functions
define('DEFAULT_EMAIL_FROM', 'noreply@astronumerology.example.com');
define('DEFAULT_EMAIL_FROM_NAME', 'AstroNumero Services');
?>
