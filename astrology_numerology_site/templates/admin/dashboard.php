<?php
// This template is included by templates/admin/layouts/main.php
// Expected variables from $view_data (set by AdminDashboardController and extracted in index.php):
// $pageTitle, $adminUsername,
// $dailySignups, $monthlySignups, $totalActiveUsers,
// $dailyRevenue, $monthlyRevenue,
// $ordersToday, $pendingOrders, $totalOrders,
// $popularServices,
// $revenueChartLabels, $revenueChartData (these are JSON encoded)
?>

<div class="row row-deck row-cards">
    <div class="col-12">
        <div class="card card-md">
            <div class="card-body">
                <h3 class="card-title">Welcome, <?php echo htmlspecialchars($adminUsername ?? 'Admin'); ?>!</h3>
                <p class="text-muted">Overview of site activity. Current system time: <?php echo date('Y-m-d H:i:s T'); ?></p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Daily Signups</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($dailySignups ?? 0); ?></div>
            </div>
        </div>
    </div>
     <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Monthly Signups</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($monthlySignups ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Active Users</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($totalActiveUsers ?? 0); ?></div>
            </div>
        </div>
    </div>
     <div class="col-sm-6 col-lg-3"> <!-- Placeholder, can be another metric like total services -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Total Orders</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($totalOrders ?? 0); ?></div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Revenue (Today)</div>
                </div>
                <div class="h1 mb-3">$<?php echo htmlspecialchars(number_format($dailyRevenue ?? 0.00, 2)); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Revenue (This Month)</div>
                </div>
                <div class="h1 mb-3">$<?php echo htmlspecialchars(number_format($monthlyRevenue ?? 0.00, 2)); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Orders Today</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($ordersToday ?? 0); ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="subheader">Pending Orders</div>
                </div>
                <div class="h1 mb-3"><?php echo htmlspecialchars($pendingOrders ?? 0); ?></div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Revenue Last 7 Days</h3>
                <canvas id="revenueChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <!-- Popular Services Section -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Most Popular Services</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($popularServices)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($popularServices as $service): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($service['service_name']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($service['purchase_count']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No purchase data available for popular services yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line', // or 'bar'
            data: {
                labels: <?php echo $revenueChartLabels ?? '[]'; ?>, // From controller, JSON encoded
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: <?php echo $revenueChartData ?? '[]'; ?>, // From controller, JSON encoded
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('revenueChart canvas element not found');
    }
});
</script>
