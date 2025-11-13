<?php

namespace Models;

use PDO;
use PDOException;
use Database;
use DateTime; // For date manipulations

class AdminDashboardModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("AdminDashboardModel PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in AdminDashboardModel.");
        }
    }

    public function countUsersRegisteredOnDate(string $date): int {
        $sql = "SELECT COUNT(*) FROM users WHERE DATE(created_at) = :register_date";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':register_date', $date);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting users for date {$date}: " . $e->getMessage());
            return 0;
        }
    }

    public function countUsersRegisteredInMonth(int $year, int $month): int {
        $sql = "SELECT COUNT(*) FROM users WHERE YEAR(created_at) = :year AND MONTH(created_at) = :month";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting users for {$year}-{$month}: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalActiveUserCount(): int {
        $sql = "SELECT COUNT(*) FROM users WHERE is_active = TRUE";
        try {
            $stmt = $this->pdo->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting total active users: " . $e->getMessage());
            return 0;
        }
    }

    // Calculates net revenue (price - refunded_amount) for completed/fulfilled/partially_refunded orders
    public function calculateRevenueOnDate(string $date): float {
        $sql = "SELECT SUM(s.price - us.refunded_amount) as daily_revenue
                FROM user_services us
                JOIN services s ON us.service_id = s.id
                WHERE DATE(us.purchase_date) = :purchase_date
                  AND us.status IN ('completed', 'fulfilled', 'partially_refunded')";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':purchase_date', $date);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['daily_revenue'] ?? 0.00);
        } catch (PDOException $e) {
            error_log("Error calculating revenue for date {$date}: " . $e->getMessage());
            return 0.00;
        }
    }

    public function calculateRevenueInMonth(int $year, int $month): float {
         $sql = "SELECT SUM(s.price - us.refunded_amount) as monthly_revenue
                FROM user_services us
                JOIN services s ON us.service_id = s.id
                WHERE YEAR(us.purchase_date) = :year
                  AND MONTH(us.purchase_date) = :month
                  AND us.status IN ('completed', 'fulfilled', 'partially_refunded')";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->bindParam(':month', $month, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['monthly_revenue'] ?? 0.00);
        } catch (PDOException $e) {
            error_log("Error calculating revenue for {$year}-{$month}: " . $e->getMessage());
            return 0.00;
        }
    }

    public function getDailyRevenueForLastNDays(int $days = 7): array {
        $revenueData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = (new DateTime())->modify("-{$i} days")->format('Y-m-d');
            $revenueData[$date] = $this->calculateRevenueOnDate($date);
        }
        return $revenueData; // Returns ['YYYY-MM-DD' => amount, ...]
    }


    public function countOrdersOnDate(string $date): int {
        $sql = "SELECT COUNT(*) FROM user_services WHERE DATE(purchase_date) = :purchase_date";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':purchase_date', $date);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting orders for date {$date}: " . $e->getMessage());
            return 0;
        }
    }

    public function countTotalOrders(): int {
         $sql = "SELECT COUNT(*) FROM user_services";
        try {
            $stmt = $this->pdo->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting total orders: " . $e->getMessage());
            return 0;
        }
    }

    public function countPendingOrders(): int {
        $sql = "SELECT COUNT(*) FROM user_services WHERE status IN ('pending', 'processing')";
        try {
            $stmt = $this->pdo->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting pending orders: " . $e->getMessage());
            return 0;
        }
    }

    public function getPopularServices(int $limit = 5): array {
        $sql = "SELECT s.name as service_name, COUNT(us.service_id) as purchase_count
                FROM user_services us
                JOIN services s ON us.service_id = s.id
                GROUP BY us.service_id, s.name
                ORDER BY purchase_count DESC
                LIMIT :limit";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching popular services: " . $e->getMessage());
            return [];
        }
    }
}
?>
