<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class AdminOrderModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("AdminOrderModel PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in AdminOrderModel.");
        }
    }

    /**
     * Fetches all user service orders with filtering, sorting, and pagination.
     */
    public function getAllUserServices(array $filters = [], array $sorting = ['us.id' => 'DESC'], int $page = 1, int $perPage = 20): array {
        $sql = "SELECT us.*, u.username as site_username, u.email as site_user_email, s.name as service_name, au.username as admin_username
                FROM user_services us
                JOIN users u ON us.user_id = u.id
                JOIN services s ON us.service_id = s.id
                LEFT JOIN admin_users au ON us.last_status_change_by_admin_id = au.id";

        $whereClauses = [];
        $params = [];

        if (!empty($filters['user_search'])) { // Search by user ID, username or email
            $whereClauses[] = "(u.id = :user_id_search OR u.username LIKE :user_username_search OR u.email LIKE :user_email_search)";
            $params[':user_id_search'] = $filters['user_search']; // Assuming if numeric, it could be ID
            $params[':user_username_search'] = '%' . $filters['user_search'] . '%';
            $params[':user_email_search'] = '%' . $filters['user_search'] . '%';
        }
        if (!empty($filters['service_id'])) {
            $whereClauses[] = "us.service_id = :service_id";
            $params[':service_id'] = $filters['service_id'];
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "us.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "us.purchase_date >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "us.purchase_date <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $validSortColumns = ['us.id', 'u.username', 's.name', 'us.purchase_date', 'us.status', 'us.price_paid']; // Assuming price_paid exists or refers to s.price
        // Note: us.price_paid is not in user_services schema. We'd join services.price.
        // For now, let's sort by columns that exist.
        $orderByClauses = [];
        foreach($sorting as $col => $dir) {
            // Basic validation, ensure $col is a valid prefixed column name
            if (preg_match('/^(us|u|s)\.[a-zA-Z_]+$/', $col) && in_array(strtoupper($dir), ['ASC', 'DESC'])) {
                 if(in_array($col, $validSortColumns)) $orderByClauses[] = "{$col} " . strtoupper($dir);
            }
        }
         if (empty($orderByClauses) && isset($sorting['service_name'])) { // If sorting by service_name (s.name)
            $dir = strtoupper($sorting['service_name']);
            if(in_array($dir, ['ASC', 'DESC'])) $orderByClauses[] = "s.name " . $dir;
        }


        if (!empty($orderByClauses)) {
            $sql .= " ORDER BY " . implode(", ", $orderByClauses);
        } else {
            $sql .= " ORDER BY us.id DESC"; // Default sort
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
            error_log("Error in AdminOrderModel::getAllUserServices: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params));
            return [];
        }
    }

    public function countTotalUserServices(array $filters = []): int {
        $sql = "SELECT COUNT(us.id)
                FROM user_services us
                JOIN users u ON us.user_id = u.id
                JOIN services s ON us.service_id = s.id";
        // Apply same filters as getAllUserServices for accurate count
        $whereClauses = [];
        $params = [];
        if (!empty($filters['user_search'])) {
            $whereClauses[] = "(u.id = :user_id_search OR u.username LIKE :user_username_search OR u.email LIKE :user_email_search)";
            $params[':user_id_search'] = $filters['user_search'];
            $params[':user_username_search'] = '%' . $filters['user_search'] . '%';
            $params[':user_email_search'] = '%' . $filters['user_search'] . '%';
        }
        if (!empty($filters['service_id'])) {
            $whereClauses[] = "us.service_id = :service_id";
            $params[':service_id'] = $filters['service_id'];
        }
        if (!empty($filters['status'])) {
            $whereClauses[] = "us.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $whereClauses[] = "us.purchase_date >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $whereClauses[] = "us.purchase_date <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in AdminOrderModel::countTotalUserServices: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserServiceById(int $userServicesId) {
        $sql = "SELECT us.*, u.username as site_username, u.email as site_user_email, s.name as service_name, s.price as service_price, au.username as last_change_admin_username
                FROM user_services us
                JOIN users u ON us.user_id = u.id
                JOIN services s ON us.service_id = s.id
                LEFT JOIN admin_users au ON us.last_status_change_by_admin_id = au.id
                WHERE us.id = :user_services_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AdminOrderModel::getUserServiceById for ID {$userServicesId}: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserServiceStatusByAdmin(int $userServicesId, string $newStatus, int $adminUserId, ?string $adminNotes): bool {
        $sql = "UPDATE user_services
                SET status = :status,
                    admin_notes = :admin_notes,
                    last_status_change_by_admin_id = :admin_user_id,
                    last_status_change_at = CURRENT_TIMESTAMP,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :user_services_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':admin_notes', $adminNotes);
            $stmt->bindParam(':admin_user_id', $adminUserId, PDO::PARAM_INT);
            $stmt->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminOrderModel::updateUserServiceStatusByAdmin for ID {$userServicesId}: " . $e->getMessage());
            return false;
        }
    }

    public function logOrderStatusChange(int $userServicesId, ?int $adminUserId, string $oldStatus, string $newStatus, ?string $reason): bool {
        $sql = "INSERT INTO order_status_log (user_service_id, admin_user_id, old_status, new_status, change_reason, created_at)
                VALUES (:user_service_id, :admin_user_id, :old_status, :new_status, :change_reason, CURRENT_TIMESTAMP)";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_service_id', $userServicesId, PDO::PARAM_INT);
            $stmt->bindParam(':admin_user_id', $adminUserId, $adminUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':old_status', $oldStatus);
            $stmt->bindParam(':new_status', $newStatus);
            $stmt->bindParam(':change_reason', $reason);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminOrderModel::logOrderStatusChange for user_service_id {$userServicesId}: " . $e->getMessage());
            return false;
        }
    }
}
?>
