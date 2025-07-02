<?php
session_start(); // Start the session at the very beginning

// Autoloader (simple version for now)
spl_autoload_register(function ($class_name) {
    // Adjust the path to correctly find classes in subdirectories like Controllers, Models
    $base_dir = __DIR__ . '/../src/';
    $file = $base_dir . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Basic router
$action = $_GET['action'] ?? 'home';

// Include core files
require_once __DIR__ . '/../config/database.php';
// Database class is loaded by autoloader if needed by controllers/models

$authController = new Controllers\AuthController();
$serviceController = new Controllers\ServiceController();
$orderController = new Controllers\OrderController();
$userController = new Controllers\UserController();

// Page/Action routing
$page_content_file = null;
$view_data = []; // Data to pass to the view

// Determine if it's an admin module request
$module = $_GET['module'] ?? 'site'; // Default to 'site'

if ($module === 'admin') {
    // --- ADMIN MODULE LOGIC ---
    $adminAction = $_GET['action'] ?? 'login'; // Default admin action is login page

    // Instantiate Admin Controllers
    // $adminAuthController, $userController are already instantiated globally for both site/admin
    $adminOrderController = new Controllers\AdminOrderController();
    $adminServiceController = new Controllers\AdminServiceController(); // Instantiate AdminServiceController

    // Handle Admin POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Core\Csrf::checkPostToken(); // CSRF check for all POST requests
        if ($adminAction === 'login_process') {
            $adminAuthController->login(); // Handles redirect
            exit;
        } elseif ($adminAction === 'toggle_user_status') {
            $adminUserController->toggleUserStatus(); // Handles redirect
            exit;
        } elseif ($adminAction === 'adjust_user_balance') {
            $adminUserController->adjustUserBalance(); // Handles redirect
            exit;
        } elseif ($adminAction === 'update_order_status') {
            $adminOrderController->updateOrderStatus(); // Handles redirect
            exit;
        } elseif ($adminAction === 'toggle_service_status') {
            $adminServiceController->toggleServiceStatus(); // Handles redirect
            exit;
        } elseif ($adminAction === 'update_global_setting') {
            $adminServiceController->updateGlobalSetting(); // Handles redirect
            exit;
        }
        // Add other admin POST actions here
    }

    // Admin GET requests / Page Loads
    // Protected routes first, then public (like login)
    if ($adminAction !== 'login' && $adminAction !== 'login_process') {
        if (!Controllers\AdminAuthController::isLoggedInAndActive()) {
            // If not logged in or session timed out, redirect to admin login
            // Store intended action to redirect after login? (optional enhancement)
            // $_SESSION['admin_redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: index.php?module=admin&action=login');
            exit;
        }
    }

    switch ($adminAction) {
        case 'login':
            $view_data = $adminAuthController->showLoginForm();
            $page_content_file = '../templates/admin/login.php'; // Admin login doesn't use main admin layout
            break;
        case 'logout':
            $adminAuthController->logout(); // Handles redirect
            exit;
        case 'dashboard':
            $adminDashboardController = new Controllers\AdminDashboardController();
            $view_data = $adminDashboardController->index();
            // $view_data already contains pageTitle and adminUsername from controller
            $page_content_file = '../templates/admin/dashboard.php'; // Uses admin layout
            break;
        case 'list_users':
            $view_data = $adminUserController->listUsers();
            $page_content_file = '../templates/admin/users/list.php';
            break;
        case 'view_user_orders':
            $view_data = $adminUserController->viewUserOrders();
            $page_content_file = '../templates/admin/users/user_orders.php';
            break;
        case 'list_orders':
            $view_data = $adminOrderController->listOrders();
            $page_content_file = '../templates/admin/orders/list.php'; // To be created
            break;
        case 'view_order_detail':
            $view_data = $adminOrderController->viewOrderDetail();
            $page_content_file = '../templates/admin/orders/detail.php';
            break;
        case 'list_services_admin': // Distinct action name for admin view of services
            $view_data = $adminServiceController->listServices();
            $page_content_file = '../templates/admin/services/list.php'; // To be created
            break;
        case 'manage_global_settings':
            $view_data = $adminServiceController->manageGlobalSettings();
            $page_content_file = '../templates/admin/services/settings.php'; // To be created
            break;
        // Add other admin GET actions here (e.g., edit_service)
        default:
            http_response_code(404);
            $view_data['pageTitle'] = 'Admin Page Not Found';
            $view_data['errorMessage'] = 'The requested admin page or action was not found.';
            $page_content_file = '../templates/admin/404.php'; // Create admin-specific 404
            break;
    }

    // Render admin page (either login page or a page within the admin layout)
    if ($page_content_file) {
        if ($adminAction === 'login') { // Login page has its own full HTML structure
            extract($view_data);
            require_once $page_content_file;
        } else { // Other admin pages use the admin layout
            // Ensure $view_data['currentUser'] for main site doesn't interfere if admin layout needs it
            // (It shouldn't, admin layout is separate)
            extract($view_data);
            require_once '../templates/admin/layouts/main.php'; // Admin layout includes $page_content_file
        }
    } else {
        // Should not happen if default case leads to 404 page
        echo "Admin page content file not set for action: " . htmlspecialchars($adminAction);
    }

} else {
    // --- SITE MODULE LOGIC (existing logic) ---
    // Fetch logged-in user data if a session exists, to make it available globally to views
    if (isset($_SESSION['user_id'])) {
        $currentUserData = $authController->getUserData($_SESSION['user_id']);
        if ($currentUserData) {
            $view_data['currentUser'] = $currentUserData;
        } else {
            // User in session but not in DB? Force logout.
            $authController->logout();
            // logout() calls exit, so script stops here.
        }
    }

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Core\Csrf::checkPostToken(); // Check CSRF token for all POST requests

        if ($action === 'register_process') {
        $authController->register(); // Handles redirect
        exit;
    } elseif ($action === 'login_process') {
        $authController->login(); // Handles redirect
        exit;
    } elseif ($action === 'purchase_service_process') {
        $orderController->purchaseServiceProcess(); // Handles redirect
        exit;
    } elseif ($action === 'update_forecast_settings') {
        $userController->updateForecastSettings(); // Handles redirect
        exit;
    }
    // Add other POST actions here if needed
}

// Handle GET requests
switch ($action) {
    case 'home':
        $page_content_file = '../templates/pages/home.php';
        $view_data['pageTitle'] = 'Welcome';
        break;
    case 'services':
        $view_data = $serviceController->listServices(); // Gets all data needed for the services page
        $page_content_file = '../templates/pages/services.php';
        // $view_data['pageTitle'] is set by the controller
        break;
    case 'service_detail': // New action for single service view
        $serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($serviceId) {
            $view_data = $serviceController->viewService($serviceId);
            if (isset($view_data['error'])) {
                http_response_code(404);
                $page_content_file = '../templates/pages/404.php'; // Or a specific service_not_found.php
            } else {
                $page_content_file = '../templates/pages/service_detail.php';
            }
        } else {
            http_response_code(400); // Bad request
            $view_data['error_message'] = "Invalid service ID.";
            $page_content_file = '../templates/pages/404.php'; // Or an error page
        }
        break;
    case 'login':
        $page_content_file = '../templates/pages/login.php';
        $view_data['pageTitle'] = 'Login';
        break;
    case 'register':
        $page_content_file = '../templates/pages/register.php';
        $view_data['pageTitle'] = 'Register';
        break;
    case 'dashboard':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login&message=login_required');
            exit;
        }
        $userData = $authController->getUserData($_SESSION['user_id']);
        if (!$userData) {
            // Handle error, e.g. user deleted after session started
            $_SESSION['error_message'] = "Could not retrieve your user data. Please login again.";
            $authController->logout(); // Force logout
            exit;
        }
        $view_data['user'] = $userData;
        $view_data['purchased_services'] = $orderController->getUserPurchasedServices($_SESSION['user_id']);

        $page_content_file = '../templates/pages/dashboard.php';
        $view_data['pageTitle'] = 'User Dashboard';
        break;
    case 'logout':
        $authController->logout(); // Handles redirect
        exit;
    case 'populate_services': // This should be a protected or manual action
        // Simple protection: check for a specific query param or a defined constant
        // In a real app, use proper admin authentication.
        if (!defined('ALLOW_ADMIN_ACTIONS') || ALLOW_ADMIN_ACTIONS !== true) {
             $_SESSION['error_message'] = "Access denied.";
             header('Location: index.php?action=home');
             exit;
        }
        $serviceController->populateServices(); // Handles redirect
        exit;
    case 'test_report': // Temporary route for testing calculation logic
        if (!defined('ALLOW_ADMIN_ACTIONS') || ALLOW_ADMIN_ACTIONS !== true) {
             $_SESSION['error_message'] = "Access denied.";
             header('Location: index.php?action=home');
             exit;
        }
        // Example: index.php?action=test_report&service_id=1&birth_date=1990-05-15
        // Ensure ReportController is instantiated
        $reportController = new Controllers\ReportController();

        $serviceIdToTest = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT);
        // Collect all GET params as potential input data
        $inputDataForTest = $_GET;
        unset($inputDataForTest['action']); // remove action from input data
        unset($inputDataForTest['service_id']); // remove service_id from input data

        if ($serviceIdToTest) {
            $generatedReportData = $reportController->generateReport($serviceIdToTest, $inputDataForTest);
            $view_data['report_data'] = $generatedReportData; // This is the actual generated content
            $view_data['pageTitle'] = 'Test Report: ' . ($generatedReportData['service_name'] ?? 'Unknown Service');
            // Pass some context if it's a test report
            $view_data['is_test_report'] = true;
            $page_content_file = '../templates/pages/report_display.php';
        } else {
            http_response_code(400);
            $view_data['error_message'] = "Service ID is required for testing reports.";
            $page_content_file = '../templates/pages/404.php'; // Or an error page
        }
        break;
    case 'view_user_service_report':
        $view_data = $orderController->viewPurchasedServiceReport(); // This method handles auth and fetches data
        // $view_data will contain 'pageTitle', 'report_data', and 'purchased_service_info'
        // It will also handle redirects internally if issues occur (e.g., not logged in, report not found)
        $page_content_file = '../templates/pages/report_display.php'; // Re-use the same template
        break;
    case 'add_funds':
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?action=login&message=login_required&redirect=add_funds');
            exit;
        }
        $view_data['pageTitle'] = 'Add Funds';
        $page_content_file = '../templates/pages/add_funds.php'; // Create this template
        break;
    default:
        http_response_code(404);
        $page_content_file = '../templates/pages/404.php';
        $view_data['pageTitle'] = 'Page Not Found';
        break;
}

// Include the main layout
if ($page_content_file) {
    // Make $view_data available to the included file
    extract($view_data); // This will make $astrologyServices, $numerologyServices, $pageTitle etc. available
    require_once '../templates/layouts/main.php';
}
?>
