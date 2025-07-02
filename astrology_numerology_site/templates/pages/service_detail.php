<?php
// Data for this page ($service, $pageTitle)
// is expected to be extracted by index.php from $view_data returned by ServiceController::viewService()

if (!isset($service) || empty($service)) {
    // This case should ideally be handled by index.php sending to 404.php
    echo "<h1>Service Not Found</h1>";
    echo "<p>The requested service could not be found.</p>";
    echo '<a href="index.php?action=services" class="btn btn-primary">Back to Services</a>';
    return; // Stop further rendering of this template
}
?>

<section class="service-detail">
    <div class="row">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($pageTitle ?? $service['name']); ?></h1>
            <p class="lead"><strong>Category:</strong> <?php echo htmlspecialchars(ucfirst($service['type'] ?? '')); ?> - <?php echo htmlspecialchars($service['category'] ?? 'General'); ?></p>
            <hr>

            <div class="service-description mb-4">
                <h4>Description</h4>
                <p><?php echo nl2br(htmlspecialchars($service['description'] ?? 'No description available.')); ?></p>
            </div>

            <div class="service-price mb-4">
                <h4>Price: <span class="text-success">$<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></span></h4>
            </div>

            <?php if (isset($service['input_fields_array']) && !empty($service['input_fields_array'])): ?>
            <div class="service-requirements mb-4">
                <h4>Information Required for this Service:</h4>
                <ul>
                    <?php foreach ($service['input_fields_array'] as $field): ?>
                        <li><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $field))); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // To display current balance, we need to fetch user data here.
                // This is a bit tricky as templates ideally shouldn't call controllers directly.
                // A better approach would be to pass $currentUser (with balance) from index.php to all views when logged in.
                // For now, let's assume $user variable might be available if index.php is modified for it, or fetch it.
                // Or, more simply, just show the button and let controller handle balance check.
                // For simplicity, we'll just make the form and let controller do all checks.
                // We can enhance this later to show balance dynamically.
                $currentUserBalanceDisplay = '...'; // Placeholder
                $numericBalance = null;

                // $currentUser is now expected to be passed from index.php if user is logged in
                if (isset($currentUser) && isset($currentUser['balance'])) {
                    $currentUserBalanceDisplay = number_format($currentUser['balance'], 2);
                    $numericBalance = (float)$currentUser['balance'];
                }
                ?>
                <form action="index.php?action=purchase_service_process" method="POST">
                    <?php echo Core\Csrf::getInputField(); ?>
                    <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service['id']); ?>">

                    <?php if (isset($service['input_fields_array']) && !empty($service['input_fields_array'])): ?>
                        <h4 class="mt-4 mb-3">Provide Your Details for "<?php echo htmlspecialchars($service['name']); ?>":</h4>
                        <?php foreach ($service['input_fields_array'] as $field_key): ?>
                            <div class="mb-3">
                                <label for="<?php echo htmlspecialchars($field_key); ?>" class="form-label"><strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $field_key))); ?>:</strong></label>
                                <?php
                                $inputType = 'text';
                                $currentValue = ''; // For repopulating form on error (more advanced)
                                if (strpos($field_key, 'date') !== false) $inputType = 'date';
                                elseif (strpos($field_key, 'time') !== false) $inputType = 'time';
                                elseif (strpos($field_key, 'email') !== false) $inputType = 'email';
                                // Add more specific input types if needed (e.g., select for birth_place country/city)
                                ?>
                                <input type="<?php echo $inputType; ?>" class="form-control" id="<?php echo htmlspecialchars($field_key); ?>" name="input_data[<?php echo htmlspecialchars($field_key); ?>]" value="<?php echo htmlspecialchars($currentValue); ?>" required>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <p>Your current balance: <strong>$<?php echo $currentUserBalanceDisplay; ?></strong></p>
                    <?php if ($numericBalance !== null && $numericBalance < (float)$service['price']): ?>
                        <button type="submit" class="btn btn-danger btn-lg" disabled>Purchase (Insufficient Balance)</button>
                        <p class="text-danger">You do not have enough balance to purchase this service.</p>
                        <a href="index.php?action=add_funds" class="btn btn-success mt-2">Add Funds</a>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary btn-lg">Purchase this Service for $<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p class="alert alert-info">Please <a href="index.php?action=login&redirect=service_detail&id=<?php echo htmlspecialchars($service['id']); ?>">login</a> or <a href="index.php?action=register">register</a> to purchase this service.</p>
            <?php endif; ?>

            <div class="mt-4">
                <a href="index.php?action=services" class="btn btn-secondary">Back to Services List</a>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Placeholder for related services or image -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Related Insights</h5>
                    <p class="card-text">Users who viewed this also looked at...</p>
                    <!-- Future: dynamically list related services -->
                    <ul class="list-unstyled">
                        <li><a href="#">Another Service 1</a></li>
                        <li><a href="#">Another Service 2</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
