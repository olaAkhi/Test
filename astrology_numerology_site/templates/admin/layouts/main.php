<?php
// This layout expects $pageTitle and $page_content_file to be set by index.php for admin routes.
// It also expects admin session variables like $_SESSION['admin_username'] to be available for authenticated pages.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Panel'; ?> - AstroNumero</title>

    <!-- Tabler Core CSS -->
    <link rel="stylesheet" href="../public/admin_assets/tabler/css/tabler.min.css">
    <!-- Additional Tabler CSS if needed (e.g., icons, flags) -->
    <!-- <link rel="stylesheet" href="../public/admin_assets/tabler/css/tabler-icons.min.css"> -->
    <!-- <link rel="stylesheet" href="../public/admin_assets/tabler/css/tabler-flags.min.css"> -->
    <!-- <link rel="stylesheet" href="../public/admin_assets/tabler/css/tabler-payments.min.css"> -->

    <!-- Custom Admin Styles (optional) -->
    <link rel="stylesheet" href="../public/admin_assets/css/admin_custom.css">
    <style>
        /* Minimal custom styles for layout if admin_custom.css is not created yet */
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .page-wrapper { flex: 1; }
    </style>
</head>
<body class="layout-fluid">
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="index.php?module=admin&action=dashboard">
                        ✨ AstroNumero Admin
                    </a>
                </h1>
                <div class="navbar-nav flex-row d-lg-none">
                    <!-- Mobile top right items if any -->
                    <li class="nav-item">
                        <a href="index.php?module=admin&action=logout" class="nav-link px-0" title="Logout" aria-label="Logout">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M7 12h14l-3 -3m0 6l3 -3" /></svg>
                        </a>
                    </li>
                </div>
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item <?php echo ($_GET['action'] ?? 'dashboard') === 'dashboard' ? 'active' : ''; ?>">
                            <a class="nav-link" href="index.php?module=admin&action=dashboard">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="5 12 3 12 12 3 21 12 19 12" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo ($_GET['action'] ?? '') === 'list_users' ? 'active' : ''; ?>">
                            <a class="nav-link" href="index.php?module=admin&action=list_users"> <!-- Placeholder link -->
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-users" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"/> <circle cx="9" cy="7" r="4" /> <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /> <path d="M16 3.13a4 4 0 0 1 0 7.75" /> <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /> </svg>
                                </span>
                                <span class="nav-link-title">User Management</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo ($_GET['action'] ?? '') === 'order_oversight' ? 'active' : ''; ?>">
                            <a class="nav-link" href="#?module=admin&action=order_oversight"> <!-- Placeholder link -->
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-truck-delivery" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"/> <circle cx="7" cy="17" r="2" /> <circle cx="17" cy="17" r="2" /> <path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" /> <line x1="3" y1="9" x2="7" y2="9" /> </svg>
                                </span>
                                <span class="nav-link-title">Order Oversight</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo ($_GET['action'] ?? '') === 'service_toggles' ? 'active' : ''; ?>">
                            <a class="nav-link" href="#?module=admin&action=service_toggles"> <!-- Placeholder link -->
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                   <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-toggle-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"/> <circle cx="8" cy="12" r="2" /> <rect x="2" y="6" width="20" height="12" rx="6" /> </svg>
                                </span>
                                <span class="nav-link-title">Service Toggles</span>
                            </a>
                        </li>
                         <li class="nav-item mt-auto"> <!-- Pushes logout to bottom -->
                            <a class="nav-link" href="index.php?module=admin&action=logout">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M7 12h14l-3 -3m0 6l3 -3" /></svg>
                                </span>
                                <span class="nav-link-title">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Page Content -->
        <div class="page-wrapper">
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title">
                                <?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Page'; ?>
                            </h2>
                        </div>
                        <!-- Optional Page actions -->
                        <div class="col-auto ms-auto d-print-none">
                            <div class="btn-list">
                                <!-- Add buttons here if needed, e.g., "New User", "Export" -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-body">
                <div class="container-xl">
                    <?php
                    // Display session messages for admin area
                    if (isset($_SESSION['admin_error_message'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($_SESSION['admin_error_message']); unset($_SESSION['admin_error_message']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['admin_success_message'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($_SESSION['admin_success_message']); unset($_SESSION['admin_success_message']); ?>
                        </div>
                    <?php endif; ?>
                     <?php if (isset($_SESSION['admin_warning_message'])): ?>
                        <div class="alert alert-warning" role="alert">
                            <?php echo htmlspecialchars($_SESSION['admin_warning_message']); unset($_SESSION['admin_warning_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php
                    // $page_content_file is set by public/index.php for the specific admin action
                    if (isset($page_content_file) && file_exists($page_content_file)) {
                        // $view_data has already been extracted in index.php
                        include $page_content_file;
                    } elseif(isset($errorMessage)) { // For admin 404 or other errors passed via $view_data
                         echo '<div class="alert alert-danger">' . htmlspecialchars($errorMessage) . '</div>';
                    } else {
                        echo "<p>Admin page content not found or not specified.</p>";
                    }
                    ?>
                </div>
            </div>
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-lg-auto ms-lg-auto">
                            <!-- Footer links -->
                        </div>
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    Copyright &copy; <?php echo date("Y"); ?>
                                    <a href="." class="link-secondary">AstroNumero Admin</a>.
                                    All rights reserved.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Tabler Core JS -->
    <script src="../public/admin_assets/tabler/js/tabler.min.js"></script>
    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Custom Admin JS (optional) -->
    <!-- <script src="../public/admin_assets/js/admin_custom.js"></script> -->
</body>
</html>
