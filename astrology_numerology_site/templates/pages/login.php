<section class="row justify-content-center">
    <div class="col-md-6">
        <h2>Login</h2>
        <form action="index.php?action=login_process" method="POST">
            <?php echo Core\Csrf::getInputField(); ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Don't have an account? <a href="index.php?action=register">Register here</a>.</p>
    </div>
</section>
