<?php

namespace Controllers;

use Models\ServiceModel; // Assuming ServiceModel is in Models namespace

class ServiceController {
    private $serviceModel;

    public function __construct() {
        $this->serviceModel = new ServiceModel();
    }

    /**
     * Prepares data for the services listing page.
     * This method would be called by index.php for the 'services' action.
     * @return array Data to be passed to the view (template).
     */
    public function listServices(): array {
        $services = $this->serviceModel->getAllActiveServices();

        $astrologyServices = [];
        $numerologyServices = [];

        foreach ($services as $service) {
            if ($service['type'] === 'astrology') {
                $astrologyServices[] = $service;
            } elseif ($service['type'] === 'numerology') {
                $numerologyServices[] = $service;
            }
        }

        return [
            'astrologyServices' => $astrologyServices,
            'numerologyServices' => $numerologyServices,
            'pageTitle' => 'Our Services'
        ];
    }

    /**
     * Prepares data for a single service detail page.
     * @param int $serviceId
     * @return array Data for the view, or an error indicator.
     */
    public function viewService(int $serviceId): array {
        $service = $this->serviceModel->getServiceById($serviceId);
        if (!$service) {
            // Handle service not found, e.g., set an error message or prepare for 404
            return ['error' => 'Service not found', 'pageTitle' => 'Error'];
        }

        // The input_fields are stored as JSON, decode them for easier use in the template
        if (!empty($service['input_fields'])) {
            $service['input_fields_array'] = json_decode($service['input_fields'], true);
             if (json_last_error() !== JSON_ERROR_NONE) {
                // Handle JSON decode error, maybe log it
                $service['input_fields_array'] = []; // Default to empty array
            }
        } else {
            $service['input_fields_array'] = [];
        }

        return [
            'service' => $service,
            'pageTitle' => htmlspecialchars($service['name'])
        ];
    }

    /**
     * Handles the one-time population of services.
     * Should be called manually or via a protected admin route.
     */
    public function populateServices() {
        // Add some security here if this were a web-accessible route
        // For example, check if user is admin or if a specific token is passed

        $count = $this->serviceModel->populateInitialServices();
        if ($count !== false) {
            $_SESSION['success_message'] = "Successfully populated {$count} initial services.";
        } else {
            $_SESSION['error_message'] = "Failed to populate services or no new services to add.";
        }
        // Redirect back or to an admin page
        header('Location: index.php?action=services'); // Or some admin page
        exit;
    }
}
?>
