<?php
// Expected variables from AdminUserController::viewUserOrders():
// $pageTitle, $user (site user details), $purchased_services
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <?php echo htmlspecialchars($pageTitle ?? 'User Order History'); ?>
                </h2>
                <?php if (isset($user)): ?>
                <p class="text-muted">
                    Viewing orders for user: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (ID: <?php echo htmlspecialchars($user['id']); ?>) <br>
                    Email: <?php echo htmlspecialchars($user['email']); ?> | Current Balance: $<?php echo htmlspecialchars(number_format($user['balance'], 2)); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="index.php?module=admin&action=list_users" class="btn btn-secondary">
                        Back to User List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Purchased Services</h3>
    </div>
    <?php if (!empty($purchased_services)): ?>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Service Name</th>
                        <th>Purchase Date</th>
                        <th>Price Paid</th>
                        <th>Status</th>
                        <th>Input Data</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchased_services as $ps): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ps['id']); ?></td>
                            <td><?php echo htmlspecialchars($ps['service_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($ps['purchase_date']))); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($ps['service_price'] ?? 0.00, 2)); ?></td>
                            <td>
                                <span class="badge bg-<?php
                                    switch (strtolower($ps['status'] ?? 'pending')) {
                                        case 'completed': echo 'success'; break;
                                        case 'processing': echo 'info'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'failed_generation': echo 'danger'; break;
                                        case 'failed': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo htmlspecialchars(ucfirst($ps['status'] ?? 'N/A')); ?></span>
                            </td>
                            <td>
                                <?php
                                $inputDataArray = !empty($ps['input_data']) ? json_decode($ps['input_data'], true) : [];
                                if (json_last_error() === JSON_ERROR_NONE && !empty($inputDataArray)) {
                                    $inputs = [];
                                    foreach($inputDataArray as $key => $value) {
                                        $inputs[] = "<strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> " . htmlspecialchars($value);
                                    }
                                    echo implode('<br>', $inputs);
                                } else {
                                    echo "<small class='text-muted'>N/A</small>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (strtolower($ps['status'] ?? '') === 'completed' && !empty($ps['result_json_data'])): ?>
                                    <!-- Link to view the actual report content - similar to user's view report -->
                                    <a href="index.php?action=view_user_service_report&user_service_id=<?php echo htmlspecialchars($ps['id']); ?>&admin_view=1"
                                       class="btn btn-sm btn-info" target="_blank" title="View report as user would see it (opens new tab)">View Report</a>
                                <?php elseif (strtolower($ps['status'] ?? '') === 'failed_generation' || strtolower($ps['status'] ?? '') === 'pending'): ?>
                                     <button class="btn btn-sm btn-warning disabled" title="Manual Reprocessing - Coming Soon">Reprocess</button>
                                <?php else: ?>
                                    <small class="text-muted">-</small>
                                <?php endif; ?>
                                <!-- Add other actions like 'Cancel Order' or 'Refund' in future phases -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body">
            <p class="text-center">This user has not purchased any services yet.</p>
        </div>
    <?php endif; ?>
</div>
