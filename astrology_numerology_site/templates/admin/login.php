<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Login'; ?></title>
    <!-- Link to Bootstrap CSS - assuming it's in the main public/css directory -->
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light grey background */
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 25px;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            background-color: #fff;
        }
        .login-card h1 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-floating label {
            padding-left: 0.5rem; /* Adjust if needed */
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>AstroNumero Admin</h1>

        <?php
        // Display error messages if any are set in the session
        // These would be set by AdminAuthController on failed login attempts
        if (isset($_SESSION['admin_error_message'])):
        ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['admin_error_message']); ?>
            </div>
            <?php unset($_SESSION['admin_error_message']); ?>
        <?php endif; ?>

        <?php
        // Display success messages (e.g., after logout)
        if (isset($_SESSION['admin_success_message'])):
        ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['admin_success_message']); ?>
            </div>
            <?php unset($_SESSION['admin_success_message']); ?>
        <?php endif; ?>

        <form action="index.php?module=admin&action=login_process" method="POST">
            <?php
            // Ensure Core\Csrf class is available or loaded if called statically like this
            // This might require an include or autoloader setup if not already handled by index.php
            // For now, assuming Core\Csrf can be resolved or index.php (which includes it) is the entry point.
            // If running this template standalone, this static call would fail without proper setup.
            // However, it's intended to be rendered via index.php.
            if (class_exists('Core\Csrf')) {
                echo Core\Csrf::getInputField();
            }
            ?>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                <label for="username">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
        </form>
        <p class="mt-4 mb-0 text-center text-muted">
            <small>&copy; <?php echo date("Y"); ?> AstroNumero Admin Panel</small><br>
            <small><a href="index.php">Back to Main Site</a></small>
        </p>
    </div>

    <!-- Optional: Bootstrap JS for any components if needed, though not strictly for this simple form -->
    <!-- <script src="../public/js/bootstrap.bundle.min.js"></script> -->
</body>
</html>
