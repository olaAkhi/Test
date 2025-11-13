<?php
// Expected variables from AdminServiceController::manageGlobalSettings():
// $pageTitle, $settings (array like ['emergency_pause_all_purchases' => '0', 'site_maintenance_mode' => '0'])
?>
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <?php echo htmlspecialchars($pageTitle ?? 'Global Application Settings'); ?>
                </h2>
            </div>
             <div class="col-auto ms-auto d-print-none">
                <a href="index.php?module=admin&action=list_services_admin" class="btn btn-secondary">
                    Back to Service List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards mt-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Global Settings</h3>
            </div>
            <div class="card-body">
                <form action="index.php?module=admin&action=update_global_setting" method="POST" class="needs-confirmation" data-confirm-message="Are you sure you want to update this global setting? This might affect site operation.">
                    <?php echo Core\Csrf::getInputField(); ?>
                    <input type="hidden" name="setting_key" value="emergency_pause_all_purchases">
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="setting_value" value="1"
                                   <?php echo (isset($settings['emergency_pause_all_purchases']) && $settings['emergency_pause_all_purchases'] === '1') ? 'checked' : ''; ?>>
                            <span class="form-check-label">Emergency Pause All Purchases</span>
                        </label>
                        <small class="form-hint">If checked, users will not be able to make any new service purchases on the main site.</small>
                    </div>
                    <button type="submit" class="btn btn-warning">Update Emergency Pause</button>
                </form>

                <hr class="my-4">

                <form action="index.php?module=admin&action=update_global_setting" method="POST" class="needs-confirmation" data-confirm-message="Are you sure you want to update site maintenance mode? This will affect user access.">
                    <?php echo Core\Csrf::getInputField(); ?>
                    <input type="hidden" name="setting_key" value="site_maintenance_mode">
                     <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="setting_value" value="1"
                                   <?php echo (isset($settings['site_maintenance_mode']) && $settings['site_maintenance_mode'] === '1') ? 'checked' : ''; ?>>
                            <span class="form-check-label">Site Maintenance Mode</span>
                        </label>
                        <small class="form-hint">If checked, the main user-facing site will display a maintenance page (feature to be fully implemented in site router).</small>
                    </div>
                    <button type="submit" class="btn btn-danger">Update Maintenance Mode</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Setting Explanations</h3></div>
            <div class="card-body">
                <p><strong>Emergency Pause All Purchases:</strong><br>
                Use this switch in critical situations to immediately prevent any new orders from being placed across the entire site. Existing orders and user access will not be affected.</p>

                <p><strong>Site Maintenance Mode:</strong><br>
                When enabled, this will (eventually) make the main website inaccessible to regular users, showing a "Under Maintenance" page instead. Admins will still be able to access the admin panel. <em>(Note: The actual display of a maintenance page on the user site needs to be implemented in the main `index.php` router by checking this setting.)</em></p>
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
