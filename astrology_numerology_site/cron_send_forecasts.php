<?php

// This script is intended to be run daily by a cron job.
// Example cron: 0 7 * * * /usr/bin/php /path/to/your/project/astrology_numerology_site/cron_send_forecasts.php >> /path/to/your/project/cron_log.txt 2>&1

echo "Forecast Sending Script Started: " . date("Y-m-d H:i:s") . "\n";

// Setup environment (Autoloader, Config)
ini_set('display_errors', 1); // Show errors for cron debugging
error_reporting(E_ALL);
set_time_limit(0); // Allow script to run for a long time if many emails

$baseDir = __DIR__; // Project root

// Autoloader
spl_autoload_register(function ($class_name) use ($baseDir) {
    $file = $baseDir . '/src/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        $core_file = $baseDir . '/src/core/' . $class_name . '.php';
        if (file_exists($core_file)) {
            require_once $core_file;
        }
    }
});

// Load Database Configuration
$configPath = $baseDir . '/config/database.php';
if (!file_exists($configPath)) {
    die("ERROR: Database configuration file not found at {$configPath}\n");
}
require_once $configPath;

// --- Main Logic ---
try {
    $userController = new Controllers\UserController();
    $emailer = new Core\Emailer(); // If Emailer is not namespaced, just new Emailer();

    $usersToSend = $userController->getUsersForDailyForecast();

    if (empty($usersToSend)) {
        echo "No users found who want daily forecasts.\n";
        exit;
    }

    echo "Found " . count($usersToSend) . " user(s) to send forecasts to.\n";
    $sentCount = 0;
    $failedCount = 0;

    foreach ($usersToSend as $user) {
        if (empty($user['email'])) {
            echo "Skipping user ID {$user['id']} (Username: {$user['username']}) due to missing email.\n";
            $failedCount++;
            continue;
        }

        echo "Generating forecast for {$user['username']} ({$user['email']})...\n";
        $forecastMessage = $emailer::generateUserForecast($user); // Using static call
        $subject = "Your Daily AstroNumero Forecast for " . date("F j, Y");

        // In a real application, you'd use a proper HTML email template
        $htmlMessage = "<html><head><title>{$subject}</title></head><body>";
        $htmlMessage .= "<h2>Hello " . htmlspecialchars($user['username']) . ",</h2>";
        $htmlMessage .= "<p>Here is your daily forecast:</p>";
        $htmlMessage .= "<blockquote>" . $forecastMessage . "</blockquote>"; // generateUserForecast already returns HTML
        $htmlMessage .= "<p>For more detailed readings, visit our website!</p>";
        $htmlMessage .= "<p>Sincerely,<br>The AstroNumero Team</p>";
        $htmlMessage .= "<hr><p><small>To unsubscribe from these daily emails, please update your settings in your dashboard on our website.</small></p>";
        $htmlMessage .= "</body></html>";

        echo "Sending email to {$user['email']}...\n";
        if ($emailer::sendEmail($user['email'], $subject, $htmlMessage)) {
            echo "Successfully sent forecast to {$user['email']}.\n";
            // Optional: Log to daily_forecast_log table
            // $logStmt = Database::getInstance()->prepare("INSERT INTO daily_forecast_log (user_id, forecast_date, content_summary) VALUES (?, CURDATE(), ?) ON DUPLICATE KEY UPDATE sent_at = CURRENT_TIMESTAMP, content_summary = ?");
            // $logStmt->execute([$user['id'], substr($forecastMessage, 0, 200), substr($forecastMessage, 0, 200)]);
            $sentCount++;
        } else {
            echo "FAILED to send forecast to {$user['email']}.\n";
            $failedCount++;
        }
        // Optional: Add a small delay to avoid overwhelming mail server
        // sleep(1);
    }

    echo "\nForecast Sending Complete.\n";
    echo "Successfully sent: {$sentCount}\n";
    echo "Failed to send: {$failedCount}\n";

} catch (Exception $e) {
    echo "AN ERROR OCCURRED during forecast sending: " . $e->getMessage() . "\n";
    error_log("Cron job cron_send_forecasts.php failed: " . $e->getMessage());
}

echo "Forecast Sending Script Ended: " . date("Y-m-d H:i:s") . "\n";
?>
