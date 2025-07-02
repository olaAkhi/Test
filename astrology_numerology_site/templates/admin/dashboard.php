<?php
// This template is included by templates/admin/layouts/main.php
// Expected variables from $view_data (set by AdminDashboardController and extracted in index.php):
// $pageTitle, $adminUsername, $totalSiteUsers, $totalOrdersToday, $pendingReports, $totalRevenueMonth
?>

<div class="row row-deck row-cards">
    <div class="col-12">
        <div class="card card-md">
            <div class="card-body">
                <h3 class="card-title">Welcome, <?php echo htmlspecialchars($adminUsername ?? 'Admin'); ?>!</h3>
                <p class="text-muted">This is your AstroNumero Admin Dashboard. From here you can manage users, services, and orders.</p>
                <p>Current system time: <?php echo date('Y-m-d H:i:s T'); ?></p>
            </div>
        </div>
    </div>

    <!-- Placeholder Stats Cards -->
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Site Users</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($totalSiteUsers ?? 0); ?></div>
                <div class="d-flex mb-2">
                    <div>Conversion rate</div>
                    <div class="ms-auto">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            0% <!-- Placeholder -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="3 17 9 11 13 15 21 7"></polyline><polyline points="14 7 21 7 21 14"></polyline></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Orders Today</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($totalOrdersToday ?? 0); ?></div>
                <div class="d-flex mb-2">
                    <div>vs Yesterday</div>
                    <div class="ms-auto">
                        <span class="text-red d-inline-flex align-items-center lh-1">
                            -0% <!-- Placeholder -->
                             <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trending-down" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"/> <polyline points="3 7 9 13 13 9 21 17" /> <polyline points="21 10 21 17 14 17" /> </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Pending Reports</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($pendingReports ?? 0); ?></div>
                <div class="d-flex mb-2">
                    <div>Total Pending</div>
                    <div class="ms-auto">
                        <!-- <span class="text-yellow d-inline-flex align-items-center lh-1"> -->
                            <!-- 0% -->
                        <!-- </span> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Revenue (This Month)</div>
                </div>
                <div class="h1 mb-3">$<?php echo htmlspecialchars(number_format($totalRevenueMonth ?? 0.00, 2)); ?></div>
                <div class="d-flex mb-2">
                    <div>vs Last Month</div>
                     <div class="ms-auto">
                        <span class="text-green d-inline-flex align-items-center lh-1">
                            +0% <!-- Placeholder -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><polyline points="3 17 9 11 13 15 21 7"></polyline><polyline points="14 7 21 7 21 14"></polyline></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <p class="text-muted mt-3">
            Note: All statistics above are placeholders. Real data will be populated as models are updated.
        </p>
    </div>
</div>
