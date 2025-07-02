<?php

// Increase execution time and memory limit for this script, if needed for large schemas or seeding.
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '256M');

echo "Database Setup Script Started...\n";

// Autoloader for classes like ServiceController, ServiceModel
spl_autoload_register(function ($class_name) {
    $base_dir = __DIR__ . '/src/'; // Assuming this script is in the project root
    $file = $base_dir . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Fallback for Database class if it's not namespaced or in a different location
        $core_file = __DIR__ . '/src/core/' . $class_name . '.php';
        if (file_exists($core_file)) {
            require_once $core_file;
        }
    }
});

// Load Database Configuration
$configPath = __DIR__ . '/config/database.php';
if (!file_exists($configPath)) {
    die("ERROR: Database configuration file not found at {$configPath}\n");
}
require_once $configPath;

// Path to the SQL schema file
$schemaFilePath = __DIR__ . '/database_schema.sql';
if (!file_exists($schemaFilePath)) {
    die("ERROR: SQL schema file not found at {$schemaFilePath}\n");
}

try {
    // 1. Connect to MySQL server (without selecting a specific database yet)
    echo "Attempting to connect to MySQL server...\n";
    $serverPdo = new PDO(DSN_SERVER, DB_USER, DB_PASS, $GLOBALS['pdo_options']);
    echo "Successfully connected to MySQL server.\n";

    // 2. Create the database if it doesn't exist
    echo "Checking if database '" . DB_NAME . "' exists...\n";
    $stmt = $serverPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($stmt->fetch()) {
        echo "Database '" . DB_NAME . "' already exists.\n";
    } else {
        echo "Database '" . DB_NAME . "' does not exist. Creating...\n";
        $serverPdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci;");
        echo "Database '" . DB_NAME . "' created successfully.\n";
    }
    // Close server-only connection
    $serverPdo = null;

    // 3. Connect to the specific database
    echo "Connecting to database '" . DB_NAME . "'...\n";
    $dbPdo = Database::getInstance(); // This uses DSN_DB
    echo "Successfully connected to database '" . DB_NAME . "'.\n";

    // 4. Create tables using the schema.sql file
    echo "Reading SQL schema from {$schemaFilePath}...\n";
    $sqlSchema = file_get_contents($schemaFilePath);

    // Remove comments and split into individual statements
    $sqlSchema = preg_replace('/--.*$/m', '', $sqlSchema); // Remove SQL comments
    $sqlSchema = preg_replace('/^\s*$/m', '', $sqlSchema);   // Remove empty lines
    $sqlStatements = explode(';', $sqlSchema);
    $sqlStatements = array_filter(array_map('trim', $sqlStatements));

    echo "Executing SQL statements to create tables...\n";
    $tablesCreated = 0;
    $tablesExist = 0;
    foreach ($sqlStatements as $statement) {
        if (empty($statement)) continue;
        try {
            // Extract table name to check if it exists (basic check)
            if (preg_match('/CREATE TABLE IF NOT EXISTS `?([a-zA-Z0-9_]+)`?/i', $statement, $matches)) {
                $tableName = $matches[1];
                $checkTableStmt = $dbPdo->query("SHOW TABLES LIKE '{$tableName}'");
                if ($checkTableStmt->fetch()) {
                    echo "Table '{$tableName}' already exists. Skipping creation from schema.\n";
                    $tablesExist++;
                    // If you want to ensure it's fully created or updated, you might drop and recreate,
                    // but "IF NOT EXISTS" should handle it.
                    // For this script, we assume IF NOT EXISTS is sufficient.
                } else {
                     $dbPdo->exec($statement);
                     echo "Executed: CREATE TABLE {$tableName} ...\n";
                     $tablesCreated++;
                }
            } else if (preg_match('/ALTER TABLE `?([a-zA-Z0-9_]+)`?/i', $statement, $matches)) {
                 // For ALTER statements, execute them. More complex logic might be needed for idempotency.
                 $dbPdo->exec($statement);
                 echo "Executed: ALTER TABLE {$matches[1]} ...\n";
            } else {
                // For other statements like INSERT (if any in schema for basic data)
                // $dbPdo->exec($statement);
                // echo "Executed other statement: " . substr($statement, 0, 50) . "...\n";
                // For now, we assume schema is mostly CREATE TABLE and ALTER TABLE
            }
        } catch (PDOException $e) {
            echo "Error executing statement: " . substr($statement, 0, 100) . "...\n";
            echo "SQL Error: " . $e->getMessage() . "\n";
            // Decide if you want to stop or continue on error for a single statement
        }
    }
    if ($tablesCreated > 0) echo "{$tablesCreated} new tables created successfully from schema.\n";
    if ($tablesExist > 0) echo "{$tablesExist} tables already existed.\n";
    echo "Table setup complete.\n";

    // 5. Seed the services table using ServiceModel's populateInitialServices
    echo "Attempting to populate initial services...\n";
    // We need an instance of ServiceModel which uses Database::getInstance()
    // Ensure ServiceModel is correctly autoloaded or required
    if (!class_exists('Models\\ServiceModel')) {
         echo "ERROR: ServiceModel class not found. Make sure it's autoloaded or required correctly.\n";
    } else {
        $serviceModel = new Models\ServiceModel(); // Uses the $dbPdo from Database::getInstance()
        $servicesAddedCount = $serviceModel->populateInitialServices();
        if ($servicesAddedCount !== false) {
            echo "{$servicesAddedCount} initial services populated/verified in the 'services' table.\n";
        } else {
            echo "Failed to populate initial services or no new services were added.\n";
        }
    }

    // 6. Seed initial admin user
    echo "Seeding initial admin user...\n";
    $adminUsername = 'superadmin';
    $adminEmail = 'admin@astronumerology.example.com'; // Change this
    $adminPassword = 'SuperSecretPassword123!'; // CHANGE THIS IMMEDIATELY AFTER SETUP
    $adminPasswordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $adminRole = 'super_admin';

    $stmt = $dbPdo->prepare("SELECT id FROM admin_users WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $adminUsername);
    $stmt->bindParam(':email', $adminEmail);
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "Admin user '{$adminUsername}' or email '{$adminEmail}' already exists. Skipping creation.\n";
    } else {
        $stmt = $dbPdo->prepare(
            "INSERT INTO admin_users (username, email, password_hash, role, is_active)
             VALUES (:username, :email, :password_hash, :role, TRUE)"
        );
        $stmt->bindParam(':username', $adminUsername);
        $stmt->bindParam(':email', $adminEmail);
        $stmt->bindParam(':password_hash', $adminPasswordHash);
        $stmt->bindParam(':role', $adminRole);
        if ($stmt->execute()) {
            echo "Successfully created initial admin user '{$adminUsername}'.\n";
            echo "IMPORTANT: Login with username '{$adminUsername}' and password '{$adminPassword}'. CHANGE THIS PASSWORD IMMEDIATELY.\n";
        } else {
            echo "ERROR: Failed to create initial admin user '{$adminUsername}'.\n";
        }
    }

    // 7. User balance handling is now part of AuthController::register for new site users.

    // 8. Seed initial application settings
    echo "Seeding initial application settings...\n";
    $settingsToSeed = [
        ['emergency_pause_all_purchases', '0', 'Global switch to pause all new service purchases. 0 = false, 1 = true.'],
        ['site_maintenance_mode', '0', 'Puts the user-facing site into maintenance mode. 0 = false, 1 = true.'],
    ];

    $stmtSetting = $dbPdo->prepare("INSERT INTO app_settings (setting_key, setting_value, description) VALUES (:key, :value, :desc) ON DUPLICATE KEY UPDATE setting_key=setting_key"); // ON DUPLICATE to avoid error if re-run
    foreach ($settingsToSeed as $setting) {
        // Check if setting already exists, only insert if not (or use ON DUPLICATE KEY UPDATE)
        $checkStmt = $dbPdo->prepare("SELECT setting_key FROM app_settings WHERE setting_key = :key");
        $checkStmt->bindParam(':key', $setting[0]);
        $checkStmt->execute();
        if (!$checkStmt->fetch()) {
            $stmtSetting->bindParam(':key', $setting[0]);
            $stmtSetting->bindParam(':value', $setting[1]);
            $stmtSetting->bindParam(':desc', $setting[2]);
            if ($stmtSetting->execute()) {
                echo "Setting '{$setting[0]}' seeded successfully.\n";
            } else {
                echo "ERROR: Failed to seed setting '{$setting[0]}'.\n";
            }
        } else {
             echo "Setting '{$setting[0]}' already exists. Skipping.\n";
        }
    }


    echo "Database setup and initial seeding completed successfully!\n";

} catch (PDOException $e) {
    die("DATABASE SETUP FAILED: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("AN ERROR OCCURRED: " . $e->getMessage() . "\n");
}

?>
