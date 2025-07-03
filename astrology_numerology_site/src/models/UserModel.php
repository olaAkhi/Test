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

    /**
     * Fetches a paginated list of transactions for a specific user.
     *
     * @param int $userId The ID of the user.
     * @param int $page The current page number.
     * @param int $perPage Number of transactions per page.
     * @return array An array of transaction records.
     */
    public function getUserTransactions(int $userId, int $page = 1, int $perPage = 15): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT t.id, t.type, t.amount, t.description, t.transaction_date, t.related_service_id, s.name as service_name
                FROM transactions t
                LEFT JOIN user_services us ON t.related_service_id = us.id
                LEFT JOIN services s ON us.service_id = s.id
                WHERE t.user_id = :user_id
                ORDER BY t.transaction_date DESC
                LIMIT :limit OFFSET :offset";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching transactions for user {$userId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Counts the total number of transactions for a specific user.
     *
     * @param int $userId The ID of the user.
     * @return int Total number of transactions.
     */
    public function countUserTransactions(int $userId): int {
        $sql = "SELECT COUNT(*) FROM transactions WHERE user_id = :user_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting transactions for user {$userId}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Adds a deposit transaction for a user and updates their balance.
     * This is for simulated deposits.
     * @param int $userId
     * @param float $amount
     * @param string $description
     * @return bool True on success, false on failure.
     */
    public function addDeposit(int $userId, float $amount, string $description = "Simulated deposit"): bool {
        if ($amount <= 0) return false;

        try {
            $this->pdo->beginTransaction();

            // Get current balance and lock user row
            $stmtUser = $this->pdo->prepare("SELECT balance FROM users WHERE id = :user_id FOR UPDATE");
            $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->pdo->rollBack();
                return false; // User not found
            }
            $newBalance = (float)$user['balance'] + $amount;

            // Update user's balance
            $stmtUpdateUser = $this->pdo->prepare("UPDATE users SET balance = :new_balance WHERE id = :user_id");
            $stmtUpdateUser->bindParam(':new_balance', $newBalance);
            $stmtUpdateUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtUpdateUser->execute();

            // Log in transactions table
            $stmtLogTransaction = $this->pdo->prepare(
                "INSERT INTO transactions (user_id, type, amount, description)
                 VALUES (:user_id, 'deposit', :amount, :description)"
            );
            $stmtLogTransaction->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtLogTransaction->bindParam(':amount', $amount);
            $stmtLogTransaction->bindParam(':description', $description);
            $stmtLogTransaction->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error adding deposit for user {$userId}: " . $e->getMessage());
            return false;
        }
    }
}
?>
