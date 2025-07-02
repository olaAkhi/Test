<?php

namespace Controllers;

use PDO;
use PDOException;
use Database; // Assuming Database class is in the global namespace or correctly aliased

class UserController {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("UserController PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in UserController.");
        }
    }

    /**
     * Updates the user's preference for receiving daily forecasts.
     */
    public function updateForecastSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = "Invalid request method.";
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "You must be logged in to update settings.";
            // Ideally, redirect back to dashboard, but login is a safe bet if session is lost
            header('Location: index.php?action=login');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        // The checkbox sends '1' if checked, not sent if unchecked.
        $wantsForecast = isset($_POST['wants_daily_forecast']) ? 1 : 0;

        try {
            $stmt = $this->pdo->prepare("UPDATE users SET wants_daily_forecast = :wants_forecast WHERE id = :user_id");
            $stmt->bindParam(':wants_forecast', $wantsForecast, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Forecast settings updated successfully.";
                // Update session variable if we store it there, or just rely on next DB read
                // For example, if user object is stored in session: $_SESSION['user']['wants_daily_forecast'] = $wantsForecast;
            } else {
                $_SESSION['error_message'] = "Failed to update forecast settings. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Error updating forecast settings for user {$userId}: " . $e->getMessage());
            $_SESSION['error_message'] = "An internal error occurred while updating settings.";
        }

        header('Location: index.php?action=dashboard');
        exit;
    }

    /**
     * Fetches users who want daily forecasts.
     * @return array List of users (id, email, username).
     */
    public function getUsersForDailyForecast(): array {
        try {
            $stmt = $this->pdo->query("SELECT id, email, username FROM users WHERE wants_daily_forecast = TRUE AND email IS NOT NULL AND email != ''");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching users for daily forecast: " . $e->getMessage());
            return [];
        }
    }
}
?>
