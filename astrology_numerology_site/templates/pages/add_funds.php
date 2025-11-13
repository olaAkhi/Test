<?php
// $currentUser, $pageTitle are expected if user is logged in
?>
<section>
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Add Funds'; ?></h1>

    <?php if (isset($currentUser)): ?>
        <p class="lead">Your current balance is: <strong>$<?php echo htmlspecialchars(number_format($currentUser['balance'], 2)); ?></strong></p>

        <div class="alert alert-info mt-4" role="alert">
            <h4 class="alert-heading">Payment Gateway Coming Soon!</h4>
            <p>We are currently working on integrating a secure payment gateway to allow you to add funds to your account easily.</p>
            <hr>
            <p class="mb-0">In the meantime, new users receive a complimentary starting balance for testing our services. If you require a balance top-up for further testing, please contact support (this is a demo feature).</p>
        </div>

        <h5 class="mt-4">Simulate Adding Funds (For Testing Only - No Real Transaction)</h5>
        <form action="index.php?action=process_simulated_deposit" method="POST" class="row g-3 align-items-end needs-confirmation" data-confirm-message="This is a test feature. Are you sure you want to simulate adding funds?">
            <?php echo Core\Csrf::getInputField(); ?>
            <div class="col-md-4">
                <label for="amount" class="form-label">Amount to Add:</label>
                <input type="number" class="form-control" id="amount" name="amount" min="5" max="500" step="0.01" value="20.00" required>
            </div>
            <div class="col-md-4">
                 <button type="submit" class="btn btn-primary">Simulate Add Funds</button>
                 <!-- <small class="d-block text-muted">This button is for testing balance updates.</small> -->
            </div>
        </form>

    <?php else: ?>
        <p class="alert alert-warning">You need to be logged in to add funds. Please <a href="index.php?action=login">login</a> or <a href="index.php?action=register">register</a>.</p>
    <?php endif; ?>

    <div class="mt-4">
        <a href="index.php?action=dashboard" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</section>
