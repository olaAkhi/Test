<?php
// Expected variables from AdminOrderController::listOrders():
// $pageTitle, $orders, $totalPages, $currentPage, $perPage, $totalOrders,
// $filters, $sort_by, $sort_dir, $allServices, $orderStatuses
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Order Oversight (<?php echo htmlspecialchars($totalOrders ?? 0); ?> total orders)</h3>
    </div>
    <div class="card-body border-bottom py-3">
        <form action="index.php" method="GET">
            <input type="hidden" name="module" value="admin">
            <input type="hidden" name="action" value="list_orders">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label for="filter_user_search" class="form-label">User (ID, Name, Email):</label>
                    <input type="text" name="filter_user_search" id="filter_user_search" class="form-control form-control-sm" placeholder="Search User..." value="<?php echo htmlspecialchars($filters['user_search'] ?? ''); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="filter_service_id" class="form-label">Service:</label>
                    <select name="filter_service_id" id="filter_service_id" class="form-select form-select-sm">
                        <option value="">All Services</option>
                        <?php foreach ($allServices ?? [] as $service): ?>
                            <option value="<?php echo htmlspecialchars($service['id']); ?>" <?php echo (isset($filters['service_id']) && $filters['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($service['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="filter_status" class="form-label">Status:</label>
                    <select name="filter_status" id="filter_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach ($orderStatuses ?? [] as $status): ?>
                            <option value="<?php echo htmlspecialchars($status); ?>" <?php echo (isset($filters['status']) && $filters['status'] == $status) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($status)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label for="filter_date_from" class="form-label">Date From:</label>
                    <input type="date" name="filter_date_from" id="filter_date_from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <label for="filter_date_to" class="form-label">Date To:</label>
                    <input type="date" name="filter_date_to" id="filter_date_to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                </div>
            </div>
            <div class="mt-2">
                <button class="btn btn-sm btn-primary" type="submit">Filter Orders</button>
                <a href="index.php?module=admin&action=list_orders" class="btn btn-sm btn-secondary ms-1">Reset Filters</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <th><a href="<?php echo build_admin_sort_url('us.id', 'list_orders', $filters); ?>">Order ID</a></th>
                    <th><a href="<?php echo build_admin_sort_url('u.username', 'list_orders', $filters); ?>">User</a></th>
                    <th><a href="<?php echo build_admin_sort_url('s.name', 'list_orders', $filters); ?>">Service</a></th>
                    <th><a href="<?php echo build_admin_sort_url('us.purchase_date', 'list_orders', $filters); ?>">Purchase Date</a></th>
                    <th><a href="<?php echo build_admin_sort_url('us.status', 'list_orders', $filters); ?>">Status</a></th>
                    <th>Last Changed By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['site_username'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($order['site_user_email'] ?? 'N/A'); ?>)
                                <br><small class="text-muted">User ID: <?php echo htmlspecialchars($order['user_id']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($order['service_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($order['purchase_date']))); ?></td>
                            <td>
                                <span class="badge bg-<?php
                                    switch (strtolower($order['status'] ?? 'pending')) {
                                        case 'completed': case 'fulfilled': echo 'success'; break;
                                        case 'processing': echo 'info'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'failed_generation': case 'failed': case 'cancelled': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo htmlspecialchars(ucfirst($order['status'] ?? 'N/A')); ?></span>
                            </td>
                            <td>
                                <?php if ($order['last_status_change_by_admin_id']): ?>
                                    <?php echo htmlspecialchars($order['admin_username'] ?? 'Admin'); ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($order['last_status_change_at']))); ?></small>
                                <?php else: echo "<small class='text-muted'>System/Initial</small>"; endif; ?>
                            </td>
                            <td>
                                <a href="index.php?module=admin&action=view_order_detail&order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No orders found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">Showing <span><?php echo count($orders); ?></span> of <span><?php echo htmlspecialchars($totalOrders ?? 0); ?></span> entries</p>
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <ul class="pagination m-0 ms-auto">
            <?php if ($currentPage > 1): ?>
                <li class="page-item"><a class="page-link" href="<?php echo build_admin_pagination_url($currentPage - 1, 'list_orders', $filters, $sort_by, $sort_dir); ?>">Prev</a></li>
            <?php else: ?>
                <li class="page-item disabled"><a class="page-link" href="#">Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo build_admin_pagination_url($i, 'list_orders', $filters, $sort_by, $sort_dir); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="<?php echo build_admin_pagination_url($currentPage + 1, 'list_orders', $filters, $sort_by, $sort_dir); ?>">Next</a></li>
            <?php else: ?>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper functions for admin table URLs - could be moved to a helper file
if (!function_exists('build_admin_base_query_string')) {
    function build_admin_base_query_string(array $currentFilters, string $actionName) {
        $queryParams = $currentFilters; // Start with existing filters
        $queryParams['module'] = 'admin';
        $queryParams['action'] = $actionName;
        unset($queryParams['page']);
        unset($queryParams['sort_by']);
        unset($queryParams['sort_dir']);
        return http_build_query($queryParams);
    }
}

if (!function_exists('build_admin_pagination_url')) {
    function build_admin_pagination_url(int $pageNumber, string $actionName, array $currentFilters, string $currentSortBy, string $currentSortDir) {
        $baseQuery = build_admin_base_query_string($currentFilters, $actionName);
        return "index.php?" . $baseQuery . "&page={$pageNumber}&sort_by=" . urlencode($currentSortBy) . "&sort_dir=" . urlencode($currentSortDir);
    }
}

if (!function_exists('build_admin_sort_url')) {
    function build_admin_sort_url(string $column, string $actionName, array $currentFilters) {
        $baseQuery = build_admin_base_query_string($currentFilters, $actionName);
        $currentSortBy = $_GET['sort_by'] ?? 'us.id'; // Default sort
        $currentSortDir = strtoupper($_GET['sort_dir'] ?? 'DESC');
        $newSortDir = ($currentSortBy === $column && $currentSortDir === 'ASC') ? 'DESC' : 'ASC';
        return "index.php?" . $baseQuery . "&sort_by=" . urlencode($column) . "&sort_dir={$newSortDir}";
    }
}
?>
