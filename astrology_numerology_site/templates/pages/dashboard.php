<?php
// $user, $purchased_services, $pageTitle are expected to be available
// from $view_data extracted in index.php
?>
<section>
    <h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'User Dashboard'; ?></h2>

    <?php if (isset($user) && !empty($user)): ?>
        <p class="lead">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>

        <div class="card mb-4">
            <div class="card-header">Account Overview</div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Current Balance:</strong> $<?php echo htmlspecialchars(number_format($user['balance'] ?? 0.00, 2)); ?></p>
                <a href="index.php?action=add_funds" class="btn btn-success">Add Funds</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Your Purchased Services</div>
            <div class="card-body">
                <?php if (isset($purchased_services) && !empty($purchased_services)): ?>
                    <div class="list-group">
                        <?php foreach ($purchased_services as $ps): ?>
                            <div class="list-group-item list-group-item-action flex-column align-items-start mb-2">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($ps['service_name'] ?? 'Service Name Missing'); ?></h5>
                                    <small>Purchased: <?php echo htmlspecialchars(date("M d, Y H:i", strtotime($ps['purchase_date'] ?? time()))); ?></small>
                                </div>
                                <p class="mb-1">Price Paid: $<?php echo htmlspecialchars(number_format($ps['service_price'] ?? 0.00, 2)); ?></p>
                                <p class="mb-1">Status: <span class="badge bg-<?php
                                    switch (strtolower($ps['status'] ?? 'pending')) {
                                        case 'completed': echo 'success'; break;
                                        case 'processing': echo 'info'; break;
                                        case 'pending': echo 'warning'; break;
                                        case 'failed': echo 'danger'; break;
                                        default: echo 'secondary';
                                    }
                                ?>"><?php echo htmlspecialchars(ucfirst($ps['status'] ?? 'N/A')); ?></span></p>

                                <?php
                                $inputDataArray = !empty($ps['input_data']) ? json_decode($ps['input_data'], true) : [];
                                if (json_last_error() === JSON_ERROR_NONE && !empty($inputDataArray)) {
                                    echo "<small class='text-muted'>Input: ";
                                    $inputs = [];
                                    foreach($inputDataArray as $key => $value) {
                                        $inputs[] = htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ": " . htmlspecialchars($value);
                                    }
                                    echo implode(', ', $inputs);
                                    echo "</small><br>";
                                }
                                ?>

                                <?php if (strtolower($ps['status'] ?? '') === 'completed' && !empty($ps['result_json_data'])): ?>
                                    <a href="index.php?action=view_user_service_report&user_service_id=<?php echo htmlspecialchars($ps['id']); ?>" class="btn btn-sm btn-primary mt-2">View Report</a>
                                <?php elseif (strtolower($ps['status'] ?? '') === 'completed'): ?>
                                    <small class="text-muted d-block mt-1">Report data is missing, though service is marked completed. Contact support.</small>
                                <?php elseif (strtolower($ps['status'] ?? '') === 'failed_generation'): ?>
                                    <small class="text-danger d-block mt-1">Report generation failed. Please contact support referencing order ID <?php echo htmlspecialchars($ps['id']); ?>.</small>
                                <?php elseif (strtolower($ps['status'] ?? '') === 'pending' || strtolower($ps['status'] ?? '') === 'processing'): ?>
                                    <small class="text-muted d-block mt-1">Your report is being processed.</small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You have not purchased any services yet.</p>
                    <a href="index.php?action=services" class="btn btn-info">Browse Services</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Daily Forecast Subscription</div>
            <div class="card-body">
                <p>Your daily forecast will be sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong>.</p>
                <form action="index.php?action=update_forecast_settings" method="POST">
                    <?php echo Core\Csrf::getInputField(); ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="dailyForecastEnabled" name="wants_daily_forecast" <?php echo (isset($user['wants_daily_forecast']) && $user['wants_daily_forecast'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="dailyForecastEnabled">
                            Yes, I want to receive Daily Forecast Emails.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">Update Settings</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <p class="alert alert-danger">Could not load user data. Please <a href="index.php?action=login">login</a> again.</p>
    <?php endif; ?>
</section>
