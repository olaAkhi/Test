<?php
// Data for this page ($astrologyServices, $numerologyServices, $pageTitle)
// is expected to be extracted by index.php from $view_data returned by ServiceController::listServices()
?>
<section>
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Our Services'; ?></h1>
    <p>Explore our wide range of astrology and numerology services designed to provide clarity and guidance.</p>

    <div class="row">
        <!-- Astrology Services Column -->
        <div class="col-lg-6 mb-4 border-end pe-4">
            <h2 class="mb-3">Astrology Services</h2>
            <?php if (!empty($astrologyServices)): ?>
                <div class="list-group">
                    <?php foreach ($astrologyServices as $service): ?>
                        <a href="index.php?action=service_detail&id=<?php echo htmlspecialchars($service['id']); ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <small>$<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)) . (strlen($service['description'] ?? '') > 100 ? '...' : ''); ?></p>
                            <small>Category: <?php echo htmlspecialchars($service['category'] ?? 'General'); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No astrology services currently available.</p>
            <?php endif; ?>
        </div>

        <!-- Numerology Services Column -->
        <div class="col-lg-6 mb-4 ps-4">
            <h2 class="mb-3">Numerology Services</h2>
            <?php if (!empty($numerologyServices)): ?>
                <div class="list-group">
                    <?php foreach ($numerologyServices as $service): ?>
                        <a href="index.php?action=service_detail&id=<?php echo htmlspecialchars($service['id']); ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?php echo htmlspecialchars($service['name']); ?></h5>
                                <small>$<?php echo htmlspecialchars(number_format($service['price'], 2)); ?></small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars(substr($service['description'] ?? '', 0, 100)) . (strlen($service['description'] ?? '') > 100 ? '...' : ''); ?></p>
                            <small>Category: <?php echo htmlspecialchars($service['category'] ?? 'General'); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No numerology services currently available.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($astrologyServices) && empty($numerologyServices)): ?>
        <p class="mt-4">Our service list is currently being updated. Please check back soon!</p>
        <!-- Provide a link to populate services for development/admin -->
        <!-- This should be removed or protected in production -->
        <p class="mt-2"><small>Admin: <a href="index.php?action=populate_services">Initialize Services Catalog</a></small></p>
    <?php endif; ?>

    <p class="mt-4">Click on a service to view more details. Services can be purchased after logging in, and reports will be accessible via your personal dashboard.</p>
</section>
