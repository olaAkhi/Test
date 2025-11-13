<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | AstroNumero' : 'Astrology & Numerology Services'; ?></title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    // $view_data (including $pageTitle, $astrologyServices etc) is extracted in index.php
    // $page_content_file is also set by index.php
    include __DIR__ . '/../../src/includes/header.php';
    ?>

    <main class="container mt-4">
        <?php
        if (isset($page_content_file) && file_exists($page_content_file)) {
            // Variables like $astrologyServices, $service, $pageTitle (if set by controller and extracted in index.php)
            // are available in the scope of the included file.
            include $page_content_file;
        } elseif (isset($error_message)) { // For specific errors passed through $view_data
             echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
        } else {
            // Fallback if $page_content_file is not set, though index.php should always set it or handle 404.
            echo "<p>Page content could not be loaded. This is unexpected.</p>";
            include __DIR__ . '/../pages/404.php';
        }
        ?>
    </main>

    <?php include __DIR__ . '/../../src/includes/footer.php'; ?>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
