<?php

namespace Controllers;

use Models\AdminUserModel;
use Models\OrderModel; // For viewing user's order history

class AdminUserController {
    // Auth check is handled by routing logic in index.php before calling these methods.
    private $adminUserModel;
    private $orderModel; // For fetching user order history

    public function __construct() {
        $this->adminUserModel = new AdminUserModel();
        $this->orderModel = new OrderModel();
    }

    public function listUsers() {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $perPage = 20; // Or make this configurable

        // Filters from GET request
        $filters = [
            'username' => filter_input(INPUT_GET, 'filter_username', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_GET, 'filter_email', FILTER_SANITIZE_EMAIL),
            'is_active' => filter_input(INPUT_GET, 'filter_is_active', FILTER_VALIDATE_INT), // 0 for inactive, 1 for active, '' for all
            'reg_date_from' => filter_input(INPUT_GET, 'filter_reg_date_from', FILTER_SANITIZE_STRING),
            'reg_date_to' => filter_input(INPUT_GET, 'filter_reg_date_to', FILTER_SANITIZE_STRING),
        ];
        // Remove empty filters so they don't apply
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== false && $value !== ''; });

        // Sorting (simple example, can be expanded)
        $sort_by = filter_input(INPUT_GET, 'sort_by', FILTER_SANITIZE_STRING) ?: 'id';
        $sort_dir = strtoupper(filter_input(INPUT_GET, 'sort_dir', FILTER_SANITIZE_STRING) ?: 'DESC');
        if (!in_array($sort_dir, ['ASC', 'DESC'])) $sort_dir = 'DESC';
        $sorting = [$sort_by => $sort_dir];

        $users = $this->adminUserModel->getAllSiteUsers($filters, $sorting, $page, $perPage);
        $totalUsers = $this->adminUserModel->countTotalSiteUsers($filters);
        $totalPages = ceil($totalUsers / $perPage);

        return [
            'pageTitle' => 'Manage Site Users',
            'users' => $users,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalUsers' => $totalUsers,
            'filters' => $filters, // Pass filters back to view for form repopulation
            'sort_by' => $sort_by,
            'sort_dir' => $sort_dir
        ];
    }

    public function toggleUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
            $_SESSION['admin_error_message'] = 'Invalid request.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }
        // CSRF is checked globally in index.php

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $user = $this->adminUserModel->getSiteUserById($userId);

        if (!$userId || !$user) {
            $_SESSION['admin_error_message'] = 'User not found.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }

        $newStatus = !(bool)$user['is_active']; // Toggle current status

        if ($this->adminUserModel->updateSiteUserStatus($userId, $newStatus)) {
            $_SESSION['admin_success_message'] = 'User status updated successfully for ' . htmlspecialchars($user['username']) . '.';
        } else {
            $_SESSION['admin_error_message'] = 'Failed to update user status for ' . htmlspecialchars($user['username']) . '.';
        }
        header('Location: index.php?module=admin&action=list_users'); // Consider redirecting with filters
        exit;
    }

    public function adjustUserBalance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
            $_SESSION['admin_error_message'] = 'Invalid request.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }
        // CSRF checked globally

        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $adjustmentAmount = filter_input(INPUT_POST, 'adjustment_amount', FILTER_VALIDATE_FLOAT);
        $adjustmentType = $_POST['adjustment_type'] ?? 'add'; // 'add' or 'subtract'
        $reason = trim(filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING) ?? '');
        $adminUserId = $_SESSION['admin_user_id']; // Assumes admin is logged in

        if (!$userId || $adjustmentAmount === false || empty($reason)) {
            $_SESSION['admin_error_message'] = 'Invalid input for balance adjustment. Amount and reason are required.';
            header('Location: index.php?module=admin&action=list_users'); // Or redirect back to a specific user page
            exit;
        }
        if ($adjustmentAmount <= 0) {
             $_SESSION['admin_error_message'] = 'Adjustment amount must be positive.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }

        $user = $this->adminUserModel->getSiteUserById($userId);
        if (!$user) {
            $_SESSION['admin_error_message'] = 'User not found.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }

        $oldBalance = (float)$user['balance'];
        $amountToChange = ($adjustmentType === 'subtract') ? -$adjustmentAmount : $adjustmentAmount;
        $newBalance = $oldBalance + $amountToChange;

        if ($newBalance < 0 && $adjustmentType === 'subtract') {
            $_SESSION['admin_error_message'] = 'Cannot adjust balance below zero by subtraction.';
            header('Location: index.php?module=admin&action=list_users'); // Or user detail page
            exit;
        }

        // Use a transaction if both balance update and logging must succeed together
        // For now, separate calls:
        if ($this->adminUserModel->updateSiteUserBalance($userId, $newBalance)) {
            if ($this->adminUserModel->logBalanceAdjustment($userId, $adminUserId, $amountToChange, $oldBalance, $newBalance, $reason)) {
                $_SESSION['admin_success_message'] = 'User balance updated successfully for ' . htmlspecialchars($user['username']) . '.';
            } else {
                $_SESSION['admin_warning_message'] = 'User balance updated, but failed to log the adjustment for ' . htmlspecialchars($user['username']) . '. Please check logs.';
            }
        } else {
            $_SESSION['admin_error_message'] = 'Failed to update user balance for ' . htmlspecialchars($user['username']) . '.';
        }
        header('Location: index.php?module=admin&action=list_users'); // Or user detail page
        exit;
    }

    public function viewUserOrders() {
        $userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
        if (!$userId) {
            $_SESSION['admin_error_message'] = 'Invalid user ID specified.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }

        $user = $this->adminUserModel->getSiteUserById($userId); // Fetch user details to display on page
        if (!$user) {
            $_SESSION['admin_error_message'] = 'User not found.';
            header('Location: index.php?module=admin&action=list_users');
            exit;
        }

        $purchasedServices = $this->orderModel->getUserPurchasedServices($userId);

        return [
            'pageTitle' => 'Order History for ' . htmlspecialchars($user['username']),
            'user' => $user,
            'purchased_services' => $purchasedServices
        ];
    }
}
?>
