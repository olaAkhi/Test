<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class UserModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("UserModel PDO Connection Error: " . $e->getMessage());
            // In a real app, handle this more gracefully than die()
            die("Database connection could not be established in UserModel.");
        }
    }

    /**
     * Finds a user by their email address.
     * @param string $email
     * @return array|false User data as an associative array, or false if not found.
     */
    public function findByEmail(string $email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, password_hash, balance, wants_daily_forecast FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in UserModel::findByEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finds a user by their ID.
     * @param int $userId
     * @return array|false User data as an associative array, or false if not found.
     */
    public function findById(int $userId) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, balance, wants_daily_forecast FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in UserModel::findById for ID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a username or email already exists.
     * @param string $username
     * @param string $email
     * @return bool True if exists, false otherwise.
     */
    public function exists(string $username, string $email): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error in UserModel::exists: " . $e->getMessage());
            return false; // Fail safe
        }
    }

    /**
     * Creates a new user.
     * @param string $username
     * @param string $email
     * @param string $passwordHash
     * @param float $initialBalance
     * @return bool True on success, false on failure.
     */
    public function createUser(string $username, string $email, string $passwordHash, float $initialBalance): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password_hash, balance)
                 VALUES (:username, :email, :password_hash, :balance)"
            );
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $passwordHash);
            $stmt->bindParam(':balance', $initialBalance);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in UserModel::createUser: " . $e->getMessage());
            return false;
        }
    }
}
?>
