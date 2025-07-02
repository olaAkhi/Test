<?php

namespace Controllers;

use Models\AdminUserModel;
// No need for Database class here directly if AdminUserModel handles it.
// Core\Csrf will be used in the template and checked in index.php for POST

class AdminAuthController {
    private $adminUserModel;
    private $sessionTimeoutMinutes = 15; // Inactivity timeout

    public function __construct() {
        $this->adminUserModel = new AdminUserModel();
    }

    public function showLoginForm() {
        // This method is primarily to make $view_data available if needed for the login form page.
        // The actual rendering is handled by index.php finding the template.
        return [
            'pageTitle' => 'Admin Login - AstroNumero',
            // No specific data needed for login form itself typically, beyond CSRF handled in template
        ];
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Should be caught by Csrf::checkPostToken or general POST check, but good practice
            header('Location: index.php?module=admin&action=login&error=invalid_request');
            exit;
        }

        // CSRF check is done globally in index.php for POST requests

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['admin_error_message'] = "Username and password are required.";
            header('Location: index.php?module=admin&action=login');
            exit;
        }

        $admin = $this->adminUserModel->findAdminByUsername($username);

        if ($admin && $admin['is_active'] && password_verify($password, $admin['password_hash'])) {
            // Password is correct, admin is active
            $_SESSION['admin_user_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_last_activity'] = time(); // Set initial activity time

            $this->adminUserModel->recordLogin($admin['id']); // Update last_login_at in DB

            $_SESSION['admin_success_message'] = "Login successful! Welcome, " . htmlspecialchars($admin['username']) . ".";
            header('Location: index.php?module=admin&action=dashboard');
            exit;
        } else {
            if ($admin && !$admin['is_active']) {
                $_SESSION['admin_error_message'] = "Your admin account is inactive. Please contact support.";
            } else {
                $_SESSION['admin_error_message'] = "Invalid username or password.";
            }
            // Log failed login attempt (optional, but good for security monitoring)
            error_log("Failed admin login attempt for username: {$username} from IP: {$_SERVER['REMOTE_ADDR']}");
            header('Location: index.php?module=admin&action=login');
            exit;
        }
    }

    public function logout() {
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_last_activity']);
        // session_destroy(); // This would destroy the main site session too if they share it.
        // Better to unset specific admin session variables.

        $_SESSION['admin_success_message'] = "You have been logged out successfully.";
        header('Location: index.php?module=admin&action=login');
        exit;
    }

    /**
     * Checks if an admin is currently logged in and their session is active.
     * Also handles session timeout.
     * @return bool True if admin is logged in and active, false otherwise.
     */
    public static functionisLoggedInAndActive(): bool {
        if (!isset($_SESSION['admin_user_id'])) {
            return false;
        }

        // Check for session timeout (e.g., 15 minutes of inactivity)
        // This timeout duration should ideally be configurable.
        $timeoutDuration = (new self())->sessionTimeoutMinutes * 60; // Convert to seconds
        if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $timeoutDuration)) {
            // Session timed out
            unset($_SESSION['admin_user_id']);
            unset($_SESSION['admin_username']);
            unset($_SESSION['admin_role']);
            unset($_SESSION['admin_last_activity']);
            $_SESSION['admin_error_message'] = "Your session has timed out due to inactivity. Please login again.";
            return false;
        }

        $_SESSION['admin_last_activity'] = time(); // Update last activity time
        return true;
    }
}
?>
