<?php
// Expected variables from AdminUserController::listUsers():
// $pageTitle, $users, $totalPages, $currentPage, $perPage, $totalUsers, $filters, $sort_by, $sort_dir
// Also, $adminUsername from the layout if needed.
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Site User Management (<?php echo htmlspecialchars($totalUsers ?? 0); ?> total)</h3>
    </div>
    <div class="card-body border-bottom py-3">
        <form action="index.php" method="GET" class="mb-0">
            <input type="hidden" name="module" value="admin">
            <input type="hidden" name="action" value="list_users">
            <div class="d-flex">
                <div class="text-muted">
                    Show
                    <div class="mx-2 d-inline-block">
                        <input type="number" class="form-control form-control-sm" value="<?php echo htmlspecialchars($perPage ?? 20); ?>" size="3" aria-label="Users per page" disabled>
                        <!-- Make perPage configurable later if needed -->
                    </div>
                    entries
                </div>
                <div class="ms-auto text-muted">
                    Search:
                    <div class="ms-2 d-inline-block">
                        <input type="text" name="filter_username" class="form-control form-control-sm" aria-label="Search by username" placeholder="Username" value="<?php echo htmlspecialchars($filters['username'] ?? ''); ?>">
                    </div>
                    <div class="ms-2 d-inline-block">
                        <input type="text" name="filter_email" class="form-control form-control-sm" aria-label="Search by email" placeholder="Email" value="<?php echo htmlspecialchars($filters['email'] ?? ''); ?>">
                    </div>
                     <div class="ms-2 d-inline-block">
                        <select name="filter_is_active" class="form-select form-select-sm">
                            <option value="">Any Status</option>
                            <option value="1" <?php echo (isset($filters['is_active']) && $filters['is_active'] === 1) ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo (isset($filters['is_active']) && $filters['is_active'] === 0) ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <button class="btn btn-sm btn-primary ms-2" type="submit">Search</button>
                    <a href="index.php?module=admin&action=list_users" class="btn btn-sm btn-secondary ms-1">Reset</a>
                </div>
            </div>
            <!-- More filters like date range can be added here -->
        </form>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <!-- Basic sort links, can be enhanced with icons -->
                    <th><a href="<?php echo build_sort_url('id'); ?>">ID</a></th>
                    <th><a href="<?php echo build_sort_url('username'); ?>">Username</a></th>
                    <th><a href="<?php echo build_sort_url('email'); ?>">Email</a></th>
                    <th><a href="<?php echo build_sort_url('balance'); ?>">Balance</a></th>
                    <th>Forecast?</th>
                    <th><a href="<?php echo build_sort_url('is_active'); ?>">Status</a></th>
                    <th><a href="<?php echo build_sort_url('created_at'); ?>">Registered</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($user['balance'], 2)); ?></td>
                            <td><?php echo $user['wants_daily_forecast'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success me-1"></span> Active
                                <?php else: ?>
                                    <span class="badge bg-danger me-1"></span> Suspended
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($user['created_at']))); ?></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="index.php?module=admin&action=view_user_orders&user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">Orders</a>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#adjustBalanceModal_<?php echo $user['id']; ?>">
                                        Adjust Balance
                                    </button>
                                    <form action="index.php?module=admin&action=toggle_user_status" method="POST" class="d-inline needs-confirmation" data-confirm-message="Are you sure you want to <?php echo $user['is_active'] ? 'suspend' : 'activate'; ?> this user: <?php echo htmlspecialchars($user['username']); ?>?">
                                        <?php echo Core\Csrf::getInputField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                            <?php echo $user['is_active'] ? 'Suspend' : 'Activate'; ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Adjust Balance Modal for each user -->
                        <div class="modal fade" id="adjustBalanceModal_<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="adjustBalanceModalLabel_<?php echo $user['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="index.php?module=admin&action=adjust_user_balance" method="POST">
                                        <?php echo Core\Csrf::getInputField(); ?>
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="adjustBalanceModalLabel_<?php echo $user['id']; ?>">Adjust Balance for <?php echo htmlspecialchars($user['username']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Current Balance: <strong>$<?php echo htmlspecialchars(number_format($user['balance'], 2)); ?></strong></p>
                                            <div class="mb-3">
                                                <label for="adjustment_type_<?php echo $user['id']; ?>" class="form-label">Adjustment Type:</label>
                                                <select name="adjustment_type" id="adjustment_type_<?php echo $user['id']; ?>" class="form-select">
                                                    <option value="add">Add to Balance</option>
                                                    <option value="subtract">Subtract from Balance</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="adjustment_amount_<?php echo $user['id']; ?>" class="form-label">Amount:</label>
                                                <input type="number" name="adjustment_amount" id="adjustment_amount_<?php echo $user['id']; ?>" class="form-control" step="0.01" min="0.01" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="reason_<?php echo $user['id']; ?>" class="form-label">Reason:</label>
                                                <textarea name="reason" id="reason_<?php echo $user['id']; ?>" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-muted">Showing <span><?php echo count($users); ?></span> of <span><?php echo htmlspecialchars($totalUsers ?? 0); ?></span> entries</p>
        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <ul class="pagination m-0 ms-auto">
            <?php if ($currentPage > 1): ?>
                <li class="page-item"><a class="page-link" href="<?php echo build_pagination_url($currentPage - 1); ?>">Prev</a></li>
            <?php else: ?>
                <li class="page-item disabled"><a class="page-link" href="#">Prev</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo build_pagination_url($i); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="<?php echo build_pagination_url($currentPage + 1); ?>">Next</a></li>
            <?php else: ?>
                <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function to build URLs for pagination and sorting, preserving existing filters
function build_base_query_string() {
    $queryParams = $_GET; // Current GET params
    unset($queryParams['page']); // Remove page for base URL
    unset($queryParams['sort_by']); // Remove sort_by
    unset($queryParams['sort_dir']); // Remove sort_dir
    // Ensure module and action are set for admin links
    if (!isset($queryParams['module'])) $queryParams['module'] = 'admin';
    if (!isset($queryParams['action'])) $queryParams['action'] = 'list_users';
    return http_build_query($queryParams);
}

function build_pagination_url(int $pageNumber) {
    $baseQuery = build_base_query_string();
    $sortBy = htmlspecialchars($_GET['sort_by'] ?? 'id');
    $sortDir = htmlspecialchars($_GET['sort_dir'] ?? 'DESC');
    return "index.php?" . $baseQuery . "&page={$pageNumber}&sort_by={$sortBy}&sort_dir={$sortDir}";
}

function build_sort_url(string $column) {
    $baseQuery = build_base_query_string();
    $currentSortBy = $_GET['sort_by'] ?? 'id';
    $currentSortDir = strtoupper($_GET['sort_dir'] ?? 'DESC');
    $newSortDir = ($currentSortBy === $column && $currentSortDir === 'ASC') ? 'DESC' : 'ASC';
    return "index.php?" . $baseQuery . "&sort_by={$column}&sort_dir={$newSortDir}";
}
?>

<script>
// Simple confirmation for actions
document.addEventListener('DOMContentLoaded', function() {
    const formsToConfirm = document.querySelectorAll('.needs-confirmation');
    formsToConfirm.forEach(form => {
        form.addEventListener('submit', function(event) {
            const message = this.dataset.confirmMessage || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
</script>
