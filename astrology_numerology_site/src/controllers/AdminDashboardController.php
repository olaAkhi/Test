<?php

namespace Controllers;

// No direct model needed for a simple dashboard placeholder,
// but AdminAuthController::isLoggedInAndActive() is checked by routing in index.php

use Models\AdminDashboardModel; // Add this
use DateTime; // Add this

class AdminDashboardController {
    private $adminDashboardModel;

    public function __construct() {
        // Auth check is handled by routing logic in index.php for admin module
        $this->adminDashboardModel = new AdminDashboardModel();
    }

    public function index() {
        $today = (new DateTime())->format('Y-m-d');
        $currentYear = (int)(new DateTime())->format('Y');
        $currentMonth = (int)(new DateTime())->format('m');

        // Fetch data using the model
        $dailySignups = $this->adminDashboardModel->countUsersRegisteredOnDate($today);
        $monthlySignups = $this->adminDashboardModel->countUsersRegisteredInMonth($currentYear, $currentMonth);
        $totalActiveUsers = $this->adminDashboardModel->getTotalActiveUserCount();

        $dailyRevenue = $this->adminDashboardModel->calculateRevenueOnDate($today);
        $monthlyRevenue = $this->adminDashboardModel->calculateRevenueInMonth($currentYear, $currentMonth);
        $revenueLast7Days = $this->adminDashboardModel->getDailyRevenueForLastNDays(7);

        $ordersToday = $this->adminDashboardModel->countOrdersOnDate($today);
        $pendingOrders = $this->adminDashboardModel->countPendingOrders();
        $totalOrders = $this->adminDashboardModel->countTotalOrders();

        $popularServices = $this->adminDashboardModel->getPopularServices(5);

        // Prepare data for Chart.js (Revenue last 7 days)
        $chartLabels = array_keys($revenueLast7Days);
        $chartData = array_values($revenueLast7Days);
        // Format dates for labels if needed, e.g., 'M d'
        $formattedChartLabels = array_map(function($dateStr) {
            return (new DateTime($dateStr))->format('M d');
        }, $chartLabels);


        // Data for the dashboard view
        $view_data = [
            'pageTitle' => 'Admin Dashboard - AstroNumero',
            'adminUsername' => $_SESSION['admin_username'] ?? 'Admin',

            'dailySignups' => $dailySignups,
            'monthlySignups' => $monthlySignups,
            'totalActiveUsers' => $totalActiveUsers,

            'dailyRevenue' => $dailyRevenue,
            'monthlyRevenue' => $monthlyRevenue,

            'ordersToday' => $ordersToday,
            'pendingOrders' => $pendingOrders,
            'totalOrders' => $totalOrders, // Added for completeness if desired on dashboard

            'popularServices' => $popularServices,

            'revenueChartLabels' => json_encode($formattedChartLabels),
            'revenueChartData' => json_encode($chartData)
        ];

        return $view_data;
    }
}
?>
