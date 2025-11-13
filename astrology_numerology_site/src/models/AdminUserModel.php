<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class AdminUserModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("AdminUserModel PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in AdminUserModel.");
        }
    }

    /**
     * Finds an admin user by their username.
     * @param string $username
     * @return array|false Admin user data as an associative array, or false if not found.
     */
    public function findAdminByUsername(string $username) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, password_hash, role, is_active FROM admin_users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::findAdminByUsername: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Records the last login timestamp for an admin user.
     * @param int $adminUserId
     * @return bool True on success, false on failure.
     */
    public function recordLogin(int $adminUserId): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindParam(':id', $adminUserId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::recordLogin for admin ID {$adminUserId}: " . $e->getMessage());
            return false;
        }
    }

    // --- Site User Management Methods ---

    /**
     * Fetches site users with filtering, sorting, and pagination.
     * @param array $filters Conditions like ['email' => 'test@example.com', 'reg_date_from' => 'YYYY-MM-DD']
     * @param array $sorting Sort order like ['username' => 'ASC']
     * @param int $page Current page number for pagination.
     * @param int $perPage Number of items per page.
     * @return array List of site users.
     */
    public function getAllSiteUsers(array $filters = [], array $sorting = ['id' => 'DESC'], int $page = 1, int $perPage = 20): array {
        $sql = "SELECT id, username, email, balance, wants_daily_forecast, created_at, is_active FROM users"; // Assuming 'is_active' exists or will be added to 'users' table
        $whereClauses = [];
        $params = [];

        if (!empty($filters['email'])) {
            $whereClauses[] = "email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['username'])) {
            $whereClauses[] = "username LIKE :username";
            $params[':username'] = '%' . $filters['username'] . '%';
        }
        // Add phone filter later if phone field is added to users table
        if (!empty($filters['reg_date_from'])) {
            $whereClauses[] = "created_at >= :reg_date_from";
            $params[':reg_date_from'] = $filters['reg_date_from'] . ' 00:00:00';
        }
        if (!empty($filters['reg_date_to'])) {
            $whereClauses[] = "created_at <= :reg_date_to";
            $params[':reg_date_to'] = $filters['reg_date_to'] . ' 23:59:59';
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereClauses[] = "is_active = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }


        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Sorting - ensure valid column names to prevent SQL injection if column names come from user input
        $validSortColumns = ['id', 'username', 'email', 'created_at', 'balance', 'is_active'];
        $orderByClauses = [];
        foreach($sorting as $col => $dir) {
            if (in_array($col, $validSortColumns) && in_array(strtoupper($dir), ['ASC', 'DESC'])) {
                $orderByClauses[] = "`{$col}` " . strtoupper($dir);
            }
        }
        if (!empty($orderByClauses)) {
            $sql .= " ORDER BY " . implode(", ", $orderByClauses);
        } else {
            $sql .= " ORDER BY id DESC"; // Default sort
        }


        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::getAllSiteUsers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Counts total site users based on filters (for pagination).
     * @param array $filters
     * @return int
     */
    public function countTotalSiteUsers(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM users";
        $whereClauses = [];
        $params = [];
        // Apply same filters as getAllSiteUsers for accurate count
        if (!empty($filters['email'])) {
            $whereClauses[] = "email LIKE :email";
            $params[':email'] = '%' . $filters['email'] . '%';
        }
        if (!empty($filters['username'])) {
            $whereClauses[] = "username LIKE :username";
            $params[':username'] = '%' . $filters['username'] . '%';
        }
        if (!empty($filters['reg_date_from'])) {
            $whereClauses[] = "created_at >= :reg_date_from";
            $params[':reg_date_from'] = $filters['reg_date_from'] . ' 00:00:00';
        }
        if (!empty($filters['reg_date_to'])) {
            $whereClauses[] = "created_at <= :reg_date_to";
            $params[':reg_date_to'] = $filters['reg_date_to'] . ' 23:59:59';
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereClauses[] = "is_active = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::countTotalSiteUsers: " . $e->getMessage());
            return 0;
        }
    }


    /**
     * Fetches a single site user by their ID.
     * @param int $userId
     * @return array|false User data or false if not found.
     */
    public function getSiteUserById(int $userId) {
        // This method is essentially the same as UserModel::findById
        // We can reuse that or keep it separate for admin context if needed.
        // For now, let's assume UserModel could be used or duplicate logic is fine for this phase.
        $userModel = new UserModel(); // Or inject UserModel dependency
        return $userModel->findById($userId);
    }

    /**
     * Updates the 'is_active' status of a site user.
     * Note: The 'users' table needs an 'is_active' BOOLEAN column. Default TRUE.
     * @param int $userId
     * @param bool $isActive
     * @return bool True on success, false on failure.
     */
    public function updateSiteUserStatus(int $userId, bool $isActive): bool {
        try {
            // First, ensure the 'users' table has an 'is_active' column.
            // If not, this query will fail. It should be added to database_schema.sql.
            $stmt = $this->pdo->prepare("UPDATE users SET is_active = :is_active WHERE id = :id");
            $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::updateSiteUserStatus for user ID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a site user's balance.
     * @param int $userId
     * @param float $newBalance
     * @return bool True on success, false on failure.
     */
    public function updateSiteUserBalance(int $userId, float $newBalance): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET balance = :balance WHERE id = :id");
            $stmt->bindParam(':balance', $newBalance);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::updateSiteUserBalance for user ID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logs a balance adjustment transaction.
     * @param int $userId
     * @param int $adminUserId
     * @param float $amountChanged (positive for addition, negative for subtraction)
     * @param float $oldBalance
     * @param float $newBalance
     * @param string $reason
     * @return bool True on success, false on failure.
     */
    public function logBalanceAdjustment(int $userId, int $adminUserId, float $amountChanged, float $oldBalance, float $newBalance, string $reason): bool {
        $sql = "INSERT INTO balance_audit_log (user_id, admin_user_id, amount_changed, old_balance, new_balance, reason, created_at)
                VALUES (:user_id, :admin_user_id, :amount_changed, :old_balance, :new_balance, :reason, CURRENT_TIMESTAMP)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':admin_user_id', $adminUserId, PDO::PARAM_INT);
            $stmt->bindParam(':amount_changed', $amountChanged);
            $stmt->bindParam(':old_balance', $oldBalance);
            $stmt->bindParam(':new_balance', $newBalance);
            $stmt->bindParam(':reason', $reason);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminUserModel::logBalanceAdjustment for user ID {$userId} by admin {$adminUserId}: " . $e->getMessage());
            return false;
        }
    }
}
?>
