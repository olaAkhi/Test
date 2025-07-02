<?php

namespace Controllers;

use Models\OrderModel;
use Models\ServiceModel; // To get service details like price
// AuthController might be needed if we re-fetch user data after balance update
// For now, AuthController::getUserData() is sufficient for initial checks.

class OrderController {
    private $orderModel;
    private $serviceModel;
    private $authController; // For user data
    private $reportController; // For generating reports

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->serviceModel = new ServiceModel();
        $this->authController = new AuthController();
        $this->reportController = new ReportController(); // Instantiate ReportController
    }

    /**
     * Handles the process of a user purchasing a service.
     */
    public function purchaseServiceProcess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error_message'] = "Invalid request method.";
            header('Location: index.php?action=services'); // Or back to service detail
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "You must be logged in to purchase services.";
            header('Location: index.php?action=login&redirect=service_detail&id=' . ($_POST['service_id'] ?? ''));
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
        $inputData = $_POST['input_data'] ?? []; // Array of input fields from the form

        if (!$serviceId) {
            $_SESSION['error_message'] = "Invalid service selected.";
            header('Location: index.php?action=services');
            exit;
        }

        // Fetch service details (especially price and required input fields)
        $service = $this->serviceModel->getServiceById($serviceId);
        if (!$service) {
            $_SESSION['error_message'] = "Service not found.";
            header('Location: index.php?action=services');
            exit;
        }
        $servicePrice = (float)$service['price'];

        // Validate provided input_data against service's required input_fields
        $requiredFields = !empty($service['input_fields']) ? json_decode($service['input_fields'], true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) $requiredFields = []; // Handle potential JSON error

        foreach ($requiredFields as $fieldKey) {
            if (!isset($inputData[$fieldKey]) || trim($inputData[$fieldKey]) === '') {
                $_SESSION['error_message'] = "Missing required input: " . htmlspecialchars(ucwords(str_replace('_', ' ', $fieldKey))) . ".";
                // Redirect back to the service detail page, ideally with form data repopulated
                header('Location: index.php?action=service_detail&id=' . $serviceId);
                exit;
            }
        }

        // Fetch current user data (especially balance)
        $user = $this->authController->getUserData($userId);
        if (!$user) {
            // Should not happen if user is logged in, but good check
            $_SESSION['error_message'] = "Could not retrieve your user data. Please login again.";
            // Potentially log out user here
            header('Location: index.php?action=login');
            exit;
        }
        $userBalance = (float)$user['balance'];

        if ($userBalance < $servicePrice) {
            $_SESSION['error_message'] = "Insufficient balance to purchase this service. Your balance: $" . number_format($userBalance, 2) . ", Service price: $" . number_format($servicePrice, 2);
            header('Location: index.php?action=service_detail&id=' . $serviceId);
            // TODO: Add link to "Add Funds" page later
            exit;
        }

        // Attempt to create the order
        $userServicesId = $this->orderModel->createServiceOrder($userId, $serviceId, $servicePrice, $inputData);

        if ($userServicesId) {
            // Now attempt to generate the report immediately
            $reportResult = $this->reportController->generateReport($serviceId, $inputData);

            $reportDataToStore = null;
            $newStatus = 'pending'; // Default if report generation fails partially

            if (isset($reportResult['error'])) {
                $newStatus = 'failed_generation'; // Or keep as 'pending' and log error
                // Log this error more formally if possible
                error_log("Report generation failed for user_service_id {$userServicesId}: " . $reportResult['error']);
                $_SESSION['warning_message'] = "Service purchased (Order ID: {$userServicesId}), but report generation encountered an issue: " . htmlspecialchars($reportResult['error']) . ". Please contact support if it's not resolved soon.";
            } else {
                $reportDataToStore = json_encode($reportResult);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $newStatus = 'failed_generation'; // Or 'pending'
                    error_log("JSON encoding failed for report data, user_service_id {$userServicesId}. Error: " . json_last_error_msg());
                    $_SESSION['warning_message'] = "Service purchased (Order ID: {$userServicesId}), but there was an issue storing the report data. Please contact support.";
                    $reportDataToStore = json_encode(['error' => 'Failed to store report data due to encoding issue.']);
                } else {
                    $newStatus = 'completed';
                     $_SESSION['success_message'] = "Service '" . htmlspecialchars($service['name']) . "' purchased and report generated successfully! Order ID: {$userServicesId}.";
                }
            }

            // Update the user_services record with status and report data
            if ($this->orderModel->updateUserServiceReport($userServicesId, $newStatus, $reportDataToStore)) {
                // Message already set based on report generation outcome
            } else {
                // This is a more critical error, as the order might be inconsistent
                error_log("CRITICAL: Failed to update user_service_id {$userServicesId} with report data/status after purchase.");
                $_SESSION['error_message'] = "Service purchased, but failed to save the generated report. Please contact support with Order ID: {$userServicesId}.";
            }

            header('Location: index.php?action=dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = "There was an error processing your initial purchase transaction. Please try again or contact support.";
            header('Location: index.php?action=service_detail&id=' . $serviceId);
            exit;
        }
    }

    /**
     * Fetches and returns purchased services for the current user.
     * To be used by the dashboard.
     * @param int $userId
     * @return array
     */
    public function getUserPurchasedServices(int $userId): array {
        return $this->orderModel->getUserPurchasedServices($userId);
    }

    /**
     * Handles displaying a single purchased service report.
     * Returns data for the view.
     */
    public function viewPurchasedServiceReport() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "You must be logged in to view reports.";
            header('Location: index.php?action=login');
            exit;
        }

        $userServicesId = filter_input(INPUT_GET, 'user_service_id', FILTER_VALIDATE_INT);
        if (!$userServicesId) {
            $_SESSION['error_message'] = "Invalid report identifier.";
            header('Location: index.php?action=dashboard');
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $purchasedService = $this->orderModel->getUserPurchasedServiceById($userServicesId, $userId);

        if (!$purchasedService) {
            $_SESSION['error_message'] = "Report not found or you do not have permission to view it.";
            header('Location: index.php?action=dashboard');
            exit;
        }

        if (strtolower($purchasedService['status']) !== 'completed') {
            $_SESSION['warning_message'] = "This report is not yet completed. Current status: " . htmlspecialchars(ucfirst($purchasedService['status']));
            header('Location: index.php?action=dashboard');
            exit;
        }

        $reportData = null;
        if (!empty($purchasedService['result_json_data'])) {
            $reportData = json_decode($purchasedService['result_json_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // This indicates an issue with stored data
                error_log("Failed to decode report JSON for user_service_id {$userServicesId}. Error: " . json_last_error_msg());
                $_SESSION['error_message'] = "Could not display the report due to a data error. Please contact support.";
                $reportData = ['error' => 'Report data is corrupted or unreadable.'];
            }
        } else {
             $_SESSION['error_message'] = "Report data is missing for this completed service. Please contact support.";
             $reportData = ['error' => 'Report data is missing.'];
        }

        // Prepare data for the view template
        // We can reuse report_display.php or create a new one like view_user_report.php
        return [
            'pageTitle' => 'Your Report: ' . htmlspecialchars($purchasedService['service_name']),
            'report_data' => $reportData, // This is the actual content from ReportController
            'purchased_service_info' => $purchasedService // Contains purchase date, input data etc.
        ];
    }
}
?>
