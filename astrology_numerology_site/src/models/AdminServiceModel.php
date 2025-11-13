<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class AdminServiceModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("AdminServiceModel PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in AdminServiceModel.");
        }
    }

    /**
     * Fetches all services with their current status.
     * @return array List of services.
     */
    public function getAllServicesWithStatus(): array {
        try {
            // Assuming 'services' table has id, name, type, price, is_active
            $stmt = $this->pdo->query("SELECT id, name, type, price, is_active FROM services ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in AdminServiceModel::getAllServicesWithStatus: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the is_active status of a service.
     * @param int $serviceId
     * @param bool $isActive
     * @return bool True on success, false on failure.
     */
    public function updateServiceIsActive(int $serviceId, bool $isActive): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE services SET is_active = :is_active WHERE id = :id");
            $stmt->bindValue(':is_active', $isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $serviceId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminServiceModel::updateServiceIsActive for service ID {$serviceId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a global application setting.
     * @param string $settingKey
     * @return string|null The setting value or null if not found.
     */
    public function getGlobalSetting(string $settingKey): ?string {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = :setting_key");
            $stmt->bindParam(':setting_key', $settingKey);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['setting_value'] : null;
        } catch (PDOException $e) {
            error_log("Error in AdminServiceModel::getGlobalSetting for key {$settingKey}: " . $e->getMessage());
            return null; // Or throw exception
        }
    }

    /**
     * Updates a global application setting.
     * @param string $settingKey
     * @param string $settingValue
     * @return bool True on success, false on failure.
     */
    public function updateGlobalSetting(string $settingKey, string $settingValue): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO app_settings (setting_key, setting_value, updated_at)
                 VALUES (:setting_key, :setting_value, CURRENT_TIMESTAMP)
                 ON DUPLICATE KEY UPDATE setting_value = :setting_value_update, updated_at = CURRENT_TIMESTAMP"
            );
            $stmt->bindParam(':setting_key', $settingKey);
            $stmt->bindParam(':setting_value', $settingValue);
            $stmt->bindParam(':setting_value_update', $settingValue); // For ON DUPLICATE KEY part
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in AdminServiceModel::updateGlobalSetting for key {$settingKey}: " . $e->getMessage());
            return false;
        }
    }
}
?>
