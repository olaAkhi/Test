<?php
// Expected variables from AdminOrderController::viewOrderDetail():
// $pageTitle, $order, $statusHistory (placeholder), $orderStatuses
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <?php echo htmlspecialchars($pageTitle ?? 'Order Detail'); ?>
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="index.php?module=admin&action=list_orders" class="btn btn-secondary">
                    Back to Order List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mt-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Order #<?php echo htmlspecialchars($order['id'] ?? 'N/A'); ?> Details</h3>
            </div>
            <div class="card-body">
                <?php if (isset($order) && !empty($order)): ?>
                    <p><strong>Site User:</strong> <?php echo htmlspecialchars($order['site_username'] ?? 'N/A'); ?> (ID: <?php echo htmlspecialchars($order['user_id']); ?>, Email: <?php echo htmlspecialchars($order['site_user_email'] ?? 'N/A'); ?>)</p>
                    <p><strong>Service:</strong> <?php echo htmlspecialchars($order['service_name'] ?? 'N/A'); ?> (ID: <?php echo htmlspecialchars($order['service_id']); ?>)</p>
                    <p><strong>Price Paid:</strong> $<?php echo htmlspecialchars(number_format($order['service_price'] ?? ($order['price_paid_from_transaction_log_later'] ?? 0.00), 2)); ?></p> <!-- Need to ensure price paid is accurately retrieved -->
                    <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($order['purchase_date']))); ?></p>
                    <p><strong>Current Status:</strong>
                        <span class="badge bg-<?php
                            switch (strtolower($order['status'] ?? 'pending')) {
                                case 'completed': case 'fulfilled': echo 'success'; break;
                                case 'processing': echo 'info'; break;
                                case 'pending': echo 'warning'; break;
                                case 'failed_generation': case 'failed': case 'cancelled': echo 'danger'; break;
                                default: echo 'secondary';
                            }
                        ?>"><?php echo htmlspecialchars(ucfirst($order['status'] ?? 'N/A')); ?></span>
                    </p>
                    <p><strong>Input Data Provided:</strong></p>
                    <pre><?php echo htmlspecialchars(json_encode(json_decode($order['input_data'] ?? '{}'), JSON_PRETTY_PRINT)); ?></pre>

                    <hr>
                    <h4>Admin Actions</h4>
                    <form action="index.php?module=admin&action=update_order_status" method="POST" class="needs-confirmation" data-confirm-message="Are you sure you want to update the status for this order?">
                        <?php echo Core\Csrf::getInputField(); ?>
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_status" class="form-label">Change Status To:</label>
                                <select name="new_status" id="new_status" class="form-select">
                                    <?php foreach($orderStatuses ?? [] as $statusValue): ?>
                                        <option value="<?php echo htmlspecialchars($statusValue); ?>" <?php echo ($order['status'] == $statusValue) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($statusValue)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="admin_notes" class="form-label">Admin Notes / Reason for Change:</label>
                                <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status & Notes</button>
                        <?php
                        // Calculate max refundable for button display logic
                        $originalPriceForRefundCheck = (float)($order['service_price'] ?? 0);
                        $currentRefundedForRefundCheck = (float)($order['refunded_amount'] ?? 0);
                        $maxRefundableForButton = $originalPriceForRefundCheck - $currentRefundedForRefundCheck;
                        if ($maxRefundableForButton > 0 && !in_array(strtolower($order['status']), ['refunded'])): // Show refund button if there's amount to refund and not already fully 'refunded'
                        ?>
                            <a href="index.php?module=admin&action=show_refund_form&order_id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-warning ms-2">
                                Process Refund
                            </a>
                        <?php elseif(in_array(strtolower($order['status']), ['refunded', 'partially_refunded']) && $maxRefundableForButton <=0): ?>
                             <span class="ms-2 text-success">Fully Refunded</span>
                        <?php elseif(in_array(strtolower($order['status']), ['partially_refunded']) && $maxRefundableForButton > 0): ?>
                             <a href="index.php?module=admin&action=show_refund_form&order_id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-warning ms-2">
                                Process Additional Refund
                            </a>
                        <?php endif; ?>
                    </form>
                    <hr>
                     <p><strong>Last Status Change By:</strong> <?php echo htmlspecialchars($order['last_change_admin_username'] ?? ($order['last_status_change_by_admin_id'] ? 'Admin ID: '.$order['last_status_change_by_admin_id'] : 'System/Initial')); ?></p>
                    <p><strong>Last Status Change At:</strong> <?php echo $order['last_status_change_at'] ? htmlspecialchars(date("F j, Y, g:i a", strtotime($order['last_status_change_at']))) : 'N/A'; ?></p>
                    <p><strong>Amount Refunded:</strong> $<?php echo htmlspecialchars(number_format($order['refunded_amount'] ?? 0.00, 2)); ?></p>


                <?php else: ?>
                    <p class="text-danger">Order details could not be loaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Report Content</h3></div>
            <div class="card-body">
                <?php
                if (!empty($order['result_json_data'])) {
                    $reportContent = json_decode($order['result_json_data'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "<h5>" . htmlspecialchars($reportContent['title'] ?? ($reportContent['service_name'] ?? 'Report')) . "</h5>";
                        if(isset($reportContent['error'])) {
                             echo "<p class='text-danger'>Error in report: " . htmlspecialchars($reportContent['error']) . "</p>";
                        } else {
                            // Display a summary or key parts of the report
                            // This is a generic display, might need tailoring if report structures vary widely
                            echo "<ul class='list-unstyled'>";
                            foreach($reportContent as $key => $value) {
                                if (!in_array($key, ['title', 'service_name', 'service_id', 'input_data_provided']) && is_scalar($value)) {
                                    echo "<li><strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> " . nl2br(htmlspecialchars($value)) . "</li>";
                                } elseif (is_array($value)) {
                                     echo "<li><strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> <pre>" . htmlspecialchars(print_r($value, true)) . "</pre></li>";
                                }
                            }
                            echo "</ul>";
                        }
                    } else {
                        echo "<p class='text-warning'>Report data is stored but could not be decoded (JSON error).</p>";
                        echo "<pre>" . htmlspecialchars($order['result_json_data']) . "</pre>";
                    }
                } elseif (strtolower($order['status'] ?? '') === 'completed') {
                     echo "<p class='text-warning'>Report marked completed, but no report data found.</p>";
                } else {
                    echo "<p class='text-muted'>Report not yet generated or available for this order status ('" . htmlspecialchars($order['status'] ?? '') . "').</p>";
                }
                ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Status Change History (Placeholder)</h3></div>
            <div class="card-body">
                <?php if (!empty($statusHistory)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($statusHistory as $logEntry): ?>
                            <li class="list-group-item">
                                Changed from <strong><?php echo htmlspecialchars($logEntry['old_status']); ?></strong>
                                to <strong><?php echo htmlspecialchars($logEntry['new_status']); ?></strong>
                                by <?php echo htmlspecialchars($logEntry['admin_username'] ?? 'System'); ?>
                                on <?php echo htmlspecialchars(date("Y-m-d H:i", strtotime($logEntry['created_at']))); ?>.
                                <?php if(!empty($logEntry['change_reason'])): ?>
                                    <p class="mb-0 mt-1 fst-italic"><small>Reason: <?php echo htmlspecialchars($logEntry['change_reason']); ?></small></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No status change history recorded for this order yet (or feature not fully implemented).</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
