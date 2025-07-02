<?php

namespace Controllers;

use PDO;
use PDOException;
use Database; // Assuming Database class is in the global namespace or correctly aliased

use Models\UserModel; // Add this

class AuthController {
    private $userModel;
    // private $pdo; // No longer directly needed if UserModel handles all DB interaction for users

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=register&error=invalid_request');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Basic Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error_message'] = "All fields are required.";
            header('Location: index.php?action=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Invalid email format.";
            header('Location: index.php?action=register');
            exit;
        }

        if (strlen($password) < 6) { // Example: Minimum password length
            $_SESSION['error_message'] = "Password must be at least 6 characters long.";
            header('Location: index.php?action=register');
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match.";
            header('Location: index.php?action=register');
            exit;
        }

        // Check if username or email already exists
        if ($this->userModel->exists($username, $email)) {
            $_SESSION['error_message'] = "Username or email already taken.";
            header('Location: index.php?action=register');
            exit;
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $initial_balance = 100.00; // Default starting balance for new users for testing

        // Create user using UserModel
        if ($this->userModel->createUser($username, $email, $password_hash, $initial_balance)) {
            $_SESSION['success_message'] = "Registration successful! You've been granted a starting balance of $" . number_format($initial_balance, 2) . ". Please login.";
            header('Location: index.php?action=login');
            exit;
        } else {
            // This 'else' might be less likely if createUser handles exceptions and returns false,
            // but good for robustness. The actual error message might be generic due to UserModel logging specifics.
            $_SESSION['error_message'] = "Registration failed due to a server error. Please try again.";
            header('Location: index.php?action=register');
            exit;
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login&error=invalid_request');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error_message'] = "Email and password are required.";
            header('Location: index.php?action=login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password is correct, start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            // Other user details like balance are fetched by index.php into $view_data['currentUser']
            $_SESSION['success_message'] = "Login successful!";
            header('Location: index.php?action=dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = "Invalid email or password.";
            // UserModel::findByEmail logs its own errors if DB issue occurs
            // So here, it's mostly about invalid credentials or user not found.
            header('Location: index.php?action=login');
            exit;
        }
    }

    public function logout() {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        $_SESSION['success_message'] = "You have been logged out successfully.";
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Fetches user data by ID.
     * Wrapper for UserModel->findById()
     * @param int $userId
     * @return array|false User data or false if not found/error.
     */
    public function getUserData(int $userId) {
        return $this->userModel->findById($userId);
    }
}
?>
