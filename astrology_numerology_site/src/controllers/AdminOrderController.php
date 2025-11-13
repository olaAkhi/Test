<?php

namespace Controllers;

use Models\AdminOrderModel;
use Models\ServiceModel; // To get list of services for filtering

class AdminOrderController {
    // Auth check is handled by routing logic in index.php for admin module.
    private $adminOrderModel;
    private $serviceModel; // For fetching service names for filters, etc.

    public function __construct() {
        $this->adminOrderModel = new AdminOrderModel();
        $this->serviceModel = new ServiceModel(); // Used to fetch all service names for filter dropdown
    }

    public function listOrders() {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $perPage = 20; // Configurable

        $filters = [
            'user_search'   => filter_input(INPUT_GET, 'filter_user_search', FILTER_SANITIZE_STRING),
            'service_id'    => filter_input(INPUT_GET, 'filter_service_id', FILTER_VALIDATE_INT),
            'status'        => filter_input(INPUT_GET, 'filter_status', FILTER_SANITIZE_STRING),
            'date_from'     => filter_input(INPUT_GET, 'filter_date_from', FILTER_SANITIZE_STRING),
            'date_to'       => filter_input(INPUT_GET, 'filter_date_to', FILTER_SANITIZE_STRING),
        ];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== false && $value !== ''; });

        $sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_STRING) ?: 'us.id';
        $sort_dir = strtoupper(filter_input(INPUT_GET, 'sort_dir', FILTER_SANITIZE_STRING) ?: 'DESC');
        if (!in_array($sort_dir, ['ASC', 'DESC'])) $sort_dir = 'DESC';
        $sorting = [$sort_by => $sort_dir];

        $orders = $this->adminOrderModel->getAllUserServices($filters, $sorting, $page, $perPage);
        $totalOrders = $this->adminOrderModel->countTotalUserServices($filters);
        $totalPages = ceil($totalOrders / $perPage);

        // Fetch all services for the filter dropdown
        $allServices = $this->serviceModel->getAllActiveServices(); // Or a method that gets all, including inactive

        // Define available statuses for filter dropdown (from ENUM in DB schema)
        $orderStatuses = ['pending', 'processing', 'completed', 'failed', 'failed_generation', 'fulfilled', 'cancelled'];


        return [
            'pageTitle' => 'Order Oversight',
            'orders' => $orders,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalOrders' => $totalOrders,
            'filters' => $filters,
            'sort_by' => $sort_by,
            'sort_dir' => $sort_dir,
            'allServices' => $allServices, // For service filter dropdown
            'orderStatuses' => $orderStatuses // For status filter dropdown
        ];
    }

    public function viewOrderDetail() {
        $userServicesId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
        if (!$userServicesId) {
            $_SESSION['admin_error_message'] = 'Invalid Order ID specified.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }

        $order = $this->adminOrderModel->getUserServiceById($userServicesId);
        if (!$order) {
            $_SESSION['admin_error_message'] = 'Order not found.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }

        // Fetch status change history for this order (to be implemented in AdminOrderModel)
        // $statusHistory = $this->adminOrderModel->getOrderStatusHistory($userServicesId);
        $statusHistory = []; // Placeholder

        // Define available statuses for status change dropdown
        $orderStatuses = ['pending', 'processing', 'completed', 'failed', 'failed_generation', 'fulfilled', 'cancelled'];


        return [
            'pageTitle' => 'Order Detail - #' . htmlspecialchars($order['id']),
            'order' => $order,
            'statusHistory' => $statusHistory, // Placeholder
            'orderStatuses' => $orderStatuses
        ];
    }

    public function updateOrderStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['admin_error_message'] = 'Invalid request method.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }
        // CSRF is checked globally in index.php

        $userServicesId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $newStatus = trim(filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING) ?? '');
        $adminNotes = trim(filter_input(INPUT_POST, 'admin_notes', FILTER_SANITIZE_STRING) ?? '');
        $adminUserId = $_SESSION['admin_user_id']; // Assumes admin is logged in

        if (!$userServicesId || empty($newStatus)) {
            $_SESSION['admin_error_message'] = 'Invalid input for status update. Order ID and new status are required.';
            // Redirect back to order detail page if possible, or list
            $redirectLocation = $userServicesId ? "index.php?module=admin&action=view_order_detail&order_id={$userServicesId}" : "index.php?module=admin&action=list_orders";
            header("Location: {$redirectLocation}");
            exit;
        }

        // Define valid statuses to prevent arbitrary values if not using ENUM strictly in code
        $validStatuses = ['pending', 'processing', 'completed', 'failed', 'failed_generation', 'fulfilled', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['admin_error_message'] = 'Invalid status value provided.';
            $redirectLocation = "index.php?module=admin&action=view_order_detail&order_id={$userServicesId}";
            header("Location: {$redirectLocation}");
            exit;
        }

        $currentOrder = $this->adminOrderModel->getUserServiceById($userServicesId);
        if (!$currentOrder) {
            $_SESSION['admin_error_message'] = 'Order not found for status update.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }
        $oldStatus = $currentOrder['status'];

        if ($this->adminOrderModel->updateUserServiceStatusByAdmin($userServicesId, $newStatus, $adminUserId, $adminNotes)) {
            // Log the status change
            $this->adminOrderModel->logOrderStatusChange($userServicesId, $adminUserId, $oldStatus, $newStatus, "Admin manual update: " . $adminNotes);
            $_SESSION['admin_success_message'] = 'Order #' . $userServicesId . ' status updated to ' . htmlspecialchars($newStatus) . '.';
        } else {
            $_SESSION['admin_error_message'] = 'Failed to update order status for order #' . $userServicesId . '.';
        }

        header("Location: index.php?module=admin&action=view_order_detail&order_id={$userServicesId}");
        exit;
    }

    public function showRefundForm() {
        $userServicesId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
        if (!$userServicesId) {
            $_SESSION['admin_error_message'] = 'Invalid Order ID for refund.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }

        $order = $this->adminOrderModel->getUserServiceById($userServicesId); // Fetches order with service_price
        if (!$order) {
            $_SESSION['admin_error_message'] = 'Order not found for refund.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }

        // Calculate max refundable amount
        $originalPrice = (float)($order['service_price'] ?? 0); // Assuming service_price is fetched
        $currentRefundedAmount = (float)($order['refunded_amount'] ?? 0);
        $maxRefundable = $originalPrice - $currentRefundedAmount;

        if ($maxRefundable <= 0 && $originalPrice > 0) { // Check if already fully refunded or price was 0
             $_SESSION['admin_warning_message'] = 'This order has already been fully refunded or had a zero price.';
             // Allow viewing the form but it might be non-interactive or just informative
        }


        return [
            'pageTitle' => 'Process Refund for Order #' . htmlspecialchars($order['id']),
            'order' => $order,
            'maxRefundable' => $maxRefundable,
            'originalPrice' => $originalPrice
        ];
    }

    public function processRefundRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['admin_error_message'] = 'Invalid request method.';
            header('Location: index.php?module=admin&action=list_orders');
            exit;
        }
        // CSRF is checked globally

        $userServicesId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $refundAmount = filter_input(INPUT_POST, 'refund_amount', FILTER_VALIDATE_FLOAT);
        $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '');
        $adminUserId = $_SESSION['admin_user_id'];

        if (!$userServicesId || $refundAmount === false || empty($reason)) {
            $_SESSION['admin_error_message'] = 'Invalid input for refund. Order ID, amount, and reason are required.';
            header('Location: index.php?module=admin&action=show_refund_form&order_id=' . $userServicesId);
            exit;
        }
        if ($refundAmount <= 0) {
            $_SESSION['admin_error_message'] = 'Refund amount must be a positive value.';
            header('Location: index.php?module=admin&action=show_refund_form&order_id=' . $userServicesId);
            exit;
        }

        $result = $this->adminOrderModel->processRefund($userServicesId, $refundAmount, $adminUserId, $reason);

        if ($result === true) {
            $_SESSION['admin_success_message'] = 'Refund of $' . number_format($refundAmount, 2) . ' processed successfully for order #' . $userServicesId . '.';
        } else {
            $_SESSION['admin_error_message'] = 'Refund processing failed: ' . htmlspecialchars($result); // $result contains error message from model
        }

        header('Location: index.php?module=admin&action=view_order_detail&order_id=' . $userServicesId);
        exit;
    }
}
?>
