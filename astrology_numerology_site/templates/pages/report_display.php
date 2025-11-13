<?php
// $report_data and $pageTitle are expected to be available
// from $view_data extracted in index.php for the 'test_report' action.
?>
<section>
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

    <?php if (isset($purchased_service_info) && !isset($is_test_report)): // Display only for actual user service reports ?>
    <div class="card mb-3 bg-light">
        <div class="card-body">
            <h5 class="card-title">Report Details</h5>
            <p class="card-text">
                <strong>Service:</strong> <?php echo htmlspecialchars($purchased_service_info['service_name'] ?? 'N/A'); ?><br>
                <strong>Purchased Date:</strong> <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($purchased_service_info['purchase_date'] ?? time()))); ?><br>
                <?php
                $inputDataForDisplay = !empty($purchased_service_info['input_data']) ? json_decode($purchased_service_info['input_data'], true) : [];
                if (json_last_error() === JSON_ERROR_NONE && !empty($inputDataForDisplay)) {
                    echo "<strong>Input Provided:</strong> ";
                    $displayInputs = [];
                    foreach($inputDataForDisplay as $key => $value) {
                        $displayInputs[] = htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ": " . htmlspecialchars($value);
                    }
                    echo implode(', ', $displayInputs);
                }
                ?>
            </p>
        </div>
    </div>
    <?php endif; ?>


    <?php if (isset($report_data) && !empty($report_data)): ?>
        <?php if (isset($report_data['error'])): ?>
            <div class="alert alert-danger">
                <strong>Error in report data:</strong> <?php echo htmlspecialchars($report_data['error']); ?>
                 <?php if(isset($report_data['title'])) echo " (For service: ".htmlspecialchars($report_data['title']).")"; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($report_data['title'] ?? ($report_data['service_name'] ?? 'Report Content')); ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($report_data['result_type'])): ?>
                        <h5 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($report_data['result_type']); ?></h5>
                    <?php endif; ?>

                    <?php
                    // Specific fields display
                    if (isset($report_data['sign'])) {
                        echo "<p><strong>Zodiac Sign:</strong> " . htmlspecialchars($report_data['sign']) . "</p>";
                    }
                    if (isset($report_data['personality_snippet'])) {
                        echo "<p><strong>Personality Snippet:</strong> " . nl2br(htmlspecialchars($report_data['personality_snippet'])) . "</p>";
                    }
                    if (isset($report_data['life_path_number'])) {
                        echo "<p><strong>Life Path Number:</strong> " . htmlspecialchars($report_data['life_path_number']) . "</p>";
                    }
                    if (isset($report_data['description']) && !isset($report_data['personality_snippet'])) {
                         echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($report_data['description'])) . "</p>";
                    }
                    if (isset($report_data['title']) && strpos($report_data['title'], 'Natal Chart') !== false) { // Check if it's a Natal Chart report
                        if(isset($report_data['summary'])) echo "<p>" . htmlspecialchars($report_data['summary']) . "</p>";
                        if(isset($report_data['sun_sign'])) echo "<p><strong>Sun Sign:</strong> " . htmlspecialchars($report_data['sun_sign']) . "</p>";
                        if(isset($report_data['moon_sign_placeholder'])) echo "<p><strong>Moon Sign (Placeholder):</strong> " . htmlspecialchars($report_data['moon_sign_placeholder']) . "</p>";
                        if(isset($report_data['rising_sign_placeholder'])) echo "<p><strong>Rising Sign (Placeholder):</strong> " . htmlspecialchars($report_data['rising_sign_placeholder']) . "</p>";
                        if(isset($report_data['details'])) echo "<p><em>" . htmlspecialchars($report_data['details']) . "</em></p>";
                    }

                    // Generic display for other fields
                    $handled_keys = ['error', 'service_name', 'result_type', 'sign', 'personality_snippet', 'life_path_number', 'description', 'title', 'summary', 'sun_sign', 'moon_sign_placeholder', 'rising_sign_placeholder', 'details', 'calculation_details', 'input_data_provided', 'service_id'];
                    foreach ($report_data as $key => $value) {
                        if (!in_array($key, $handled_keys) && is_scalar($value)) {
                            echo "<p><strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> " . htmlspecialchars($value) . "</p>";
                        } elseif (!in_array($key, $handled_keys) && is_array($value)) {
                             echo "<p><strong>" . htmlspecialchars(ucwords(str_replace('_', ' ', $key))) . ":</strong> <pre>" . htmlspecialchars(print_r($value, true)) . "</pre></p>";
                        }
                    }
                    ?>
                </div>
                <?php if (isset($report_data['calculation_details']) || (isset($is_test_report) && isset($report_data['input_data_provided'])) ): ?>
                <div class="card-footer text-muted">
                    <?php if (isset($report_data['calculation_details'])): ?>
                        <small><em>Calculation based on: <?php echo htmlspecialchars($report_data['calculation_details']); ?></em></small><br>
                    <?php endif; ?>
                     <?php if (isset($is_test_report) && isset($report_data['input_data_provided'])): // Show input only for test reports for privacy ?>
                        <small><em>Test Input provided: <?php echo htmlspecialchars(json_encode($report_data['input_data_provided'])); ?></em></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="alert alert-warning">No report data available to display.</p>
        <?php if(isset($is_test_report) && $is_test_report): ?>
            <p>Please ensure you provided a valid service ID and any required input data via GET parameters for the test report (e.g., &birth_date=YYYY-MM-DD).</p>
            <p>Example for Zodiac Sign (Service ID 2): <a href="index.php?action=test_report&service_id=2&birth_date=1985-07-25">Test Zodiac Sign</a></p>
            <p>Example for Life Path (Service ID 21): <a href="index.php?action=test_report&service_id=21&birth_date=1992-11-03">Test Life Path Number</a></p>
        <?php endif; ?>
    <?php endif; ?>

    <div class="mt-4">
        <?php if(isset($is_test_report) && $is_test_report): ?>
            <a href="index.php?action=services" class="btn btn-secondary">Back to Services</a>
        <?php else: ?>
            <a href="index.php?action=dashboard" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</section>
