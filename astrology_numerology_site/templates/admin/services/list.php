<?php
// Expected variables from AdminServiceController::listServices():
// $pageTitle, $services
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?php echo htmlspecialchars($pageTitle ?? 'Manage Services'); ?></h3>
        <div class="col-auto ms-auto d-print-none">
            <a href="index.php?module=admin&action=manage_global_settings" class="btn btn-outline-primary">
                Global Settings (e.g., Emergency Pause)
            </a>
            <!-- Future: <a href="#" class="btn btn-primary">Add New Service</a> -->
        </div>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['id']); ?></td>
                            <td><?php echo htmlspecialchars($service['name']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($service['type'])); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></td>
                            <td>
                                <?php if ($service['is_active']): ?>
                                    <span class="badge bg-success me-1"></span> Active
                                <?php else: ?>
                                    <span class="badge bg-danger me-1"></span> Inactive
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="index.php?module=admin&action=toggle_service_status" method="POST" class="d-inline needs-confirmation"
                                      data-confirm-message="Are you sure you want to <?php echo $service['is_active'] ? 'deactivate' : 'activate'; ?> the service '<?php echo htmlspecialchars($service['name']); ?>'?">
                                    <?php echo Core\Csrf::getInputField(); ?>
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <!-- The controller will toggle current status, so no need to send new status directly -->
                                    <button type="submit" class="btn btn-sm <?php echo $service['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <?php echo $service['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                </form>
                                <!-- Future: <a href="#" class="btn btn-sm btn-outline-secondary ms-1">Edit</a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No services found. You might need to run the seeder or add services.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- No pagination for services list for now, assuming it's not excessively long -->
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
