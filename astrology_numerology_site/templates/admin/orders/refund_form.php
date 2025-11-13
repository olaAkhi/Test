<?php
// Expected variables from AdminOrderController::showRefundForm():
// $pageTitle, $order, $maxRefundable, $originalPrice
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <?php echo htmlspecialchars($pageTitle ?? 'Process Refund'); ?>
                </h2>
                <?php if (isset($order)): ?>
                <p class="text-muted">
                    Order ID: #<?php echo htmlspecialchars($order['id']); ?> |
                    User: <?php echo htmlspecialchars($order['site_username'] ?? 'N/A'); ?> |
                    Service: <?php echo htmlspecialchars($order['service_name'] ?? 'N/A'); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="index.php?module=admin&action=view_order_detail&order_id=<?php echo htmlspecialchars($order['id'] ?? ''); ?>" class="btn btn-secondary">
                    Back to Order Detail
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Refund Details for Order #<?php echo htmlspecialchars($order['id'] ?? 'N/A'); ?></h3>
            </div>
            <div class="card-body">
                <?php if (isset($order) && !empty($order)): ?>
                    <p><strong>Original Service Price:</strong> $<?php echo htmlspecialchars(number_format($originalPrice ?? 0.00, 2)); ?></p>
                    <p><strong>Amount Already Refunded:</strong> $<?php echo htmlspecialchars(number_format($order['refunded_amount'] ?? 0.00, 2)); ?></p>
                    <p><strong>Maximum Refundable Amount:</strong> <strong class="text-success">$<?php echo htmlspecialchars(number_format($maxRefundable ?? 0.00, 2)); ?></strong></p>

                    <hr>

                    <?php if (isset($maxRefundable) && $maxRefundable > 0): ?>
                        <form action="index.php?module=admin&action=process_refund_request" method="POST" class="needs-confirmation" data-confirm-message="Are you sure you want to process this refund? This action cannot be easily undone.">
                            <?php echo Core\Csrf::getInputField(); ?>
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">

                            <div class="mb-3">
                                <label for="refund_amount" class="form-label">Refund Amount:</label>
                                <input type="number" name="refund_amount" id="refund_amount" class="form-control"
                                       step="0.01" min="0.01" max="<?php echo htmlspecialchars(number_format($maxRefundable ?? 0.00, 2, '.', '')); ?>"
                                       value="<?php echo htmlspecialchars(number_format($maxRefundable ?? 0.00, 2, '.', '')); ?>" required>
                                <small class="form-hint">Enter the amount to refund. Cannot exceed the maximum refundable amount.</small>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Refund:</label>
                                <textarea name="reason" id="reason" class="form-control" rows="3" required placeholder="e.g., User request, service not delivered as expected, goodwill gesture."></textarea>
                                <small class="form-hint">This reason will be logged.</small>
                            </div>

                            <button type="submit" class="btn btn-danger">Process Refund</button>
                        </form>
                    <?php elseif (isset($maxRefundable) && $maxRefundable <= 0 && $originalPrice > 0): ?>
                        <div class="alert alert-success" role="alert">
                            This order has already been fully refunded. No further refund actions possible.
                        </div>
                    <?php elseif ($originalPrice <= 0): ?>
                         <div class="alert alert-info" role="alert">
                            This order had an original price of $0.00. No refund applicable.
                        </div>
                    <?php else: ?>
                        <p class="text-danger">Cannot determine refund details or order not eligible for refund.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-danger">Order details could not be loaded for refund processing.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
// Re-use confirmation script if not already global in admin layout
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
