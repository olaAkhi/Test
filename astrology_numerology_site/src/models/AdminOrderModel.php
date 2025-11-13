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

    /**
     * Processes a refund for a specific user_service order.
     * Updates user balance, order refunded_amount, logs transaction.
     *
     * @param int $userServicesId
     * @param float $refundAmount The amount to refund (must be positive).
     * @param int $adminUserId Admin performing the refund.
     * @param string $reason Reason for the refund.
     * @return bool|string True on success, error message string on failure.
     */
    public function processRefund(int $userServicesId, float $refundAmount, int $adminUserId, string $reason) {
        if ($refundAmount <= 0) {
            return "Refund amount must be positive.";
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Get the user_service details and original service price
            // We need user_id, current refunded_amount, and original price (from services table)
            $stmtOrder = $this->pdo->prepare(
                "SELECT us.user_id, us.service_id, us.refunded_amount, us.status, s.price as original_price
                 FROM user_services us
                 JOIN services s ON us.service_id = s.id
                 WHERE us.id = :user_services_id FOR UPDATE" // Lock the order row
            );
            $stmtOrder->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);
            $stmtOrder->execute();
            $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->pdo->rollBack();
                return "Order not found.";
            }

            $userId = $order['user_id'];
            $originalPrice = (float)$order['original_price'];
            $currentRefundedAmount = (float)$order['refunded_amount'];
            $maxRefundable = $originalPrice - $currentRefundedAmount;

            if ($refundAmount > $maxRefundable) {
                $this->pdo->rollBack();
                return "Refund amount ($" . number_format($refundAmount, 2) . ") exceeds maximum refundable amount ($" . number_format($maxRefundable, 2) . ").";
            }

            // 2. Get current user balance
            $stmtUser = $this->pdo->prepare("SELECT balance FROM users WHERE id = :user_id FOR UPDATE"); // Lock user row
            $stmtUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->pdo->rollBack(); // Should not happen if order exists
                return "User not found for this order.";
            }
            $oldUserBalance = (float)$user['balance'];

            // 3. Update user's balance
            $newUserBalance = $oldUserBalance + $refundAmount;
            $stmtUpdateUser = $this->pdo->prepare("UPDATE users SET balance = :new_balance WHERE id = :user_id");
            $stmtUpdateUser->bindParam(':new_balance', $newUserBalance);
            $stmtUpdateUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtUpdateUser->execute();

            // 4. Update user_services table (refunded_amount and status)
            $newTotalRefundedAmount = $currentRefundedAmount + $refundAmount;
            $newOrderStatus = $order['status']; // Keep current status unless full refund changes it
            if (abs($newTotalRefundedAmount - $originalPrice) < 0.001) { // Comparing floats for equality
                $newOrderStatus = 'refunded'; // Full refund
            } elseif ($newTotalRefundedAmount > 0) {
                $newOrderStatus = 'partially_refunded';
            }
            // If it was 'cancelled' and now gets a refund, it might stay 'cancelled' or change to 'refunded'.
            // For simplicity, if any refund occurs, we mark as 'partially_refunded' or 'refunded'.

            $stmtUpdateOrder = $this->pdo->prepare(
                "UPDATE user_services
                 SET refunded_amount = :refunded_amount, status = :status,
                     admin_notes = CONCAT(IFNULL(admin_notes,''), :admin_note_separator, :refund_note),
                     last_status_change_by_admin_id = :admin_user_id,
                     last_status_change_at = CURRENT_TIMESTAMP
                 WHERE id = :user_services_id"
            );
            $refundNote = "\nRefunded: $" . number_format($refundAmount, 2) . " by admin ID {$adminUserId}. Reason: " . $reason;
            $adminNoteSeparator = "\n-----\n"; // Separator for notes
            $stmtUpdateOrder->bindParam(':refunded_amount', $newTotalRefundedAmount);
            $stmtUpdateOrder->bindParam(':status', $newOrderStatus);
            $stmtUpdateOrder->bindParam(':admin_note_separator', $adminNoteSeparator);
            $stmtUpdateOrder->bindParam(':refund_note', $refundNote);
            $stmtUpdateOrder->bindParam(':admin_user_id', $adminUserId, PDO::PARAM_INT);
            $stmtUpdateOrder->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);
            $stmtUpdateOrder->execute();

            // 5. Log in transactions table
            $transactionDescription = "Refund for Order ID: {$userServicesId}. Reason: " . $reason;
            $stmtLogTransaction = $this->pdo->prepare(
                "INSERT INTO transactions (user_id, type, amount, description, related_service_id)
                 VALUES (:user_id, 'refund', :amount, :description, :related_service_id)"
            );
            $stmtLogTransaction->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtLogTransaction->bindParam(':amount', $refundAmount); // Positive amount for refund type
            $stmtLogTransaction->bindParam(':description', $transactionDescription);
            $stmtLogTransaction->bindParam(':related_service_id', $userServicesId, PDO::PARAM_INT);
            $stmtLogTransaction->execute();

            // 6. Optionally, log to order_status_log if status changed
            if ($newOrderStatus !== $order['status']) {
                $this->logOrderStatusChange($userServicesId, $adminUserId, $order['status'], $newOrderStatus, "Refund processed: " . $reason);
            }

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error processing refund for order {$userServicesId}: " . $e->getMessage());
            return "Database error during refund processing. Details: " . $e->getMessage();
        }
    }
}
?>
