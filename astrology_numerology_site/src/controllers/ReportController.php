<?php

namespace Controllers;

use Services\AstrologyCalculator;
use Services\NumerologyCalculator;
// We'll need ServiceModel to get service details like 'calculation_logic_ref'
use Models\ServiceModel;

class ReportController {
    private $serviceModel;

    public function __construct() {
        $this->serviceModel = new ServiceModel();
    }

    /**
     * Generates report data for a given service and input data.
     * This is a simplified version. In a real app, this would be more robust,
     * potentially storing the result in user_services.result_data_path.
     *
     * @param int $serviceId The ID of the service.
     * @param array $inputData User-provided data (e.g., ['birth_date' => '1990-01-01']).
     * @return array Report data or an error message.
     */
    public function generateReport(int $serviceId, array $inputData): array {
        $service = $this->serviceModel->getServiceById($serviceId);

        if (!$service) {
            return ['error' => 'Service not found.'];
        }

        $logicRef = $service['calculation_logic_ref'];
        if (empty($logicRef)) {
            return ['error' => 'Calculation logic not defined for this service.'];
        }

        $reportContent = ['title' => $service['name']];

        // Example: Call the appropriate calculator based on $logicRef
        // This is a very basic dispatch mechanism. A more advanced system might use a strategy pattern
        // or a mapping of logic_ref to specific class methods.
        try {
            switch ($logicRef) {
                case 'getZodiacSignReading': // Corresponds to "Zodiac Sign Personality Reading"
                    if (!isset($inputData['birth_date'])) {
                        return ['error' => 'Birth date is required for Zodiac Sign Reading.'];
                    }
                    $zodiacInfo = AstrologyCalculator::getZodiacSignInfo($inputData['birth_date']);
                    if (isset($zodiacInfo['error'])) return $zodiacInfo;
                    $reportContent['result_type'] = 'Zodiac Sign Information';
                    $reportContent['sign'] = $zodiacInfo['sign'];
                    $reportContent['personality_snippet'] = $zodiacInfo['personality_snippet'];
                    $reportContent['calculation_details'] = $zodiacInfo['calculation_details'];
                    break;

                case 'calculateLifePathNumber': // Corresponds to "Life Path Number Reading"
                    if (!isset($inputData['birth_date'])) {
                        return ['error' => 'Birth date is required for Life Path Number.'];
                    }
                    $lifePathInfo = NumerologyCalculator::calculateLifePathNumber($inputData['birth_date']);
                    if (isset($lifePathInfo['error'])) return $lifePathInfo;
                    $reportContent['result_type'] = 'Life Path Number Information';
                    $reportContent['life_path_number'] = $lifePathInfo['life_path_number'];
                    $reportContent['description'] = $lifePathInfo['description'];
                    $reportContent['calculation_details'] = $lifePathInfo['calculation_details'];
                    break;

                case 'calculateNatalChart':
                     if (!isset($inputData['birth_date']) || !isset($inputData['birth_time']) || !isset($inputData['birth_place'])) {
                        return ['error' => 'Birth date, birth time, and birth place are required for Natal Chart.'];
                    }
                    $natalChartInfo = AstrologyCalculator::calculateNatalChart(
                        $inputData['birth_date'],
                        $inputData['birth_time'],
                        $inputData['birth_place']
                    );
                    if (isset($natalChartInfo['error'])) return $natalChartInfo;
                    $reportContent['result_type'] = 'Natal Chart (Placeholder)';
                    $reportContent = array_merge($reportContent, $natalChartInfo); // Merge all data from calculation
                    break;

                // Add more cases for other services based on their 'calculation_logic_ref'
                // e.g., case 'calculateDestinyNumber': ...

                default:
                    return ['error' => "No calculation logic implemented for '{$logicRef}'.", 'title' => $service['name'] . ' (Report Not Available)'];
            }
        } catch (\Exception $e) {
            error_log("Error during report generation for service {$serviceId} ({$logicRef}): " . $e->getMessage());
            return ['error' => 'An unexpected error occurred while generating your report. Please try again later.'];
        }

        $reportContent['service_id'] = $serviceId;
        $reportContent['service_name'] = $service['name'];
        $reportContent['input_data_provided'] = $inputData; // For reference in the report

        return $reportContent;
    }
}
?>
