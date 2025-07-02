<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class OrderModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("OrderModel PDO Connection Error: " . $e->getMessage());
            // Consider a more graceful error handling for a production app
            die("Database connection could not be established in OrderModel.");
        }
    }

    /**
     * Creates a new service order for a user and updates their balance.
     * This is done within a transaction to ensure atomicity.
     *
     * @param int $userId
     * @param int $serviceId
     * @param float $servicePrice
     * @param array $inputData JSON serializable array of user inputs for the service
     * @return int|false The ID of the newly created user_service record, or false on failure.
     */
    public function createServiceOrder(int $userId, int $serviceId, float $servicePrice, array $inputData) {
        try {
            $this->pdo->beginTransaction();

            // 1. Check current balance (though controller should do this first, double check for safety)
            $stmt = $this->pdo->prepare("SELECT balance FROM users WHERE id = :user_id FOR UPDATE"); // Lock row
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || $user['balance'] < $servicePrice) {
                $this->pdo->rollBack();
                error_log("User {$userId} has insufficient balance ({$user['balance']}) for service {$serviceId} (price {$servicePrice}) or user not found.");
                return false; // Insufficient balance or user not found
            }

            // 2. Deduct price from user's balance
            $newBalance = $user['balance'] - $servicePrice;
            $stmt = $this->pdo->prepare("UPDATE users SET balance = :new_balance WHERE id = :user_id");
            $stmt->bindParam(':new_balance', $newBalance);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // 3. Insert into user_services
            $inputDataJson = json_encode($inputData);
            // Initial status: 'pending' - report generation will happen later
            $stmt = $this->pdo->prepare(
                "INSERT INTO user_services (user_id, service_id, input_data, status)
                 VALUES (:user_id, :service_id, :input_data, 'pending')"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
            $stmt->bindParam(':input_data', $inputDataJson);
            $stmt->execute();

            $userServicesId = $this->pdo->lastInsertId();

            // 4. (Optional but good practice) Log the transaction
            $transactionDescription = "Purchase of service ID: {$serviceId}";
            $stmt = $this->pdo->prepare(
                "INSERT INTO transactions (user_id, type, amount, description, related_service_id)
                 VALUES (:user_id, 'purchase', :amount, :description, :related_service_id)"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', -$servicePrice); // Store as negative for purchase
            $stmt->bindParam(':description', $transactionDescription);
            $stmt->bindParam(':related_service_id', $userServicesId, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();
            return (int)$userServicesId;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error creating service order for user {$userId}, service {$serviceId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all purchased services for a given user, joined with service details.
     * @param int $userId
     * @return array An array of purchased service records.
     */
    public function getUserPurchasedServices(int $userId): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT us.id, us.purchase_date, us.status, us.result_data_path, us.input_data,
                        s.name as service_name, s.description as service_description, s.type as service_type, s.price as service_price
                 FROM user_services us
                 JOIN services s ON us.service_id = s.id
                 WHERE us.user_id = :user_id
                 ORDER BY us.purchase_date DESC"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching purchased services for user {$userId}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates a user_service record with the report generation status and data.
     *
     * @param int $userServicesId The ID of the user_services record.
     * @param string $status The new status (e.g., 'completed', 'failed_generation').
     * @param string|null $reportDataJson The JSON string of the report data, or null.
     * @return bool True on success, false on failure.
     */
    public function updateUserServiceReport(int $userServicesId, string $status, ?string $reportDataJson): bool {
        // Ensure result_json_data field exists. If it was result_data_path, this SQL needs to match.
        // Assuming we will change schema to use `result_json_data` of type TEXT or JSON.
        $sql = "UPDATE user_services SET status = :status, result_json_data = :report_data, updated_at = CURRENT_TIMESTAMP
                WHERE id = :user_services_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':report_data', $reportDataJson); // PDO will handle null correctly
            $stmt->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating user_service record {$userServicesId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a single purchased service record by its ID, ensuring it belongs to the specified user.
     *
     * @param int $userServicesId The ID of the user_services record.
     * @param int $userId The ID of the user requesting the record.
     * @return array|null The user_service record as an associative array, or null if not found or not owned by user.
     */
    public function getUserPurchasedServiceById(int $userServicesId, int $userId): ?array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT us.*, s.name as service_name
                 FROM user_services us
                 JOIN services s ON us.service_id = s.id
                 WHERE us.id = :user_services_id AND us.user_id = :user_id"
            );
            $stmt->bindParam(':user_services_id', $userServicesId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching user_service record {$userServicesId} for user {$userId}: " . $e->getMessage());
            return null;
        }
    }
}
?>
