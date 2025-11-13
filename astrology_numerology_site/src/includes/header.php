<header class="mb-4">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php?action=home">✨ AstroNumero</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=services">Services</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <?php
                                $displayName = isset($currentUser) ? $currentUser['username'] : ($_SESSION['username'] ?? 'User');
                                $displayBalance = isset($currentUser) ? ' ($'.number_format($currentUser['balance'], 2).')' : '';
                            ?>
                            <a class="nav-link" href="index.php?action=logout">Logout (<?php echo htmlspecialchars($displayName) . $displayBalance; ?>)</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container mt-3">
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php
    // Display specific messages from URL query parameters, e.g., after redirect
    if (isset($_GET['message'])) {
        $message = '';
        switch ($_GET['message']) {
            case 'login_required':
                $message = 'You need to login to access that page.';
                echo '<div class="alert alert-warning" role="alert">' . htmlspecialchars($message) . '</div>';
                break;
            // Add more cases as needed
        }
    }
    ?>
</div>
