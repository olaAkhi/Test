<?php

namespace Controllers;

use Models\AdminServiceModel;

class AdminServiceController {
    // Auth check is handled by routing logic in index.php for admin module.
    private $adminServiceModel;

    public function __construct() {
        $this->adminServiceModel = new AdminServiceModel();
    }

    public function listServices() {
        $services = $this->adminServiceModel->getAllServicesWithStatus();
        return [
            'pageTitle' => 'Manage Services',
            'services' => $services
        ];
    }

    public function toggleServiceStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['service_id'])) {
            $_SESSION['admin_error_message'] = 'Invalid request.';
            header('Location: index.php?module=admin&action=list_services_admin'); // Changed action name
            exit;
        }
        // CSRF is checked globally

        $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
        // It's good to fetch the service to know its current status before toggling,
        // or the new status can be passed directly if the button sends it.
        // For a simple toggle, we can fetch current status or assume the button means "toggle".
        // Let's assume we get the current status from the service list or fetch it.
        // For simplicity, the form could send the *new* desired state (0 or 1)
        $newIsActive = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false;
        // This requires the form to send 'is_active=0' or 'is_active=1'.
        // A simpler toggle might just invert current status. Let's refine this.

        // Fetch current service to toggle its status
        $allServices = $this->adminServiceModel->getAllServicesWithStatus(); // Not ideal to fetch all for one
        $currentService = null;
        foreach($allServices as $s) {
            if ($s['id'] == $serviceId) {
                $currentService = $s;
                break;
            }
        }

        if (!$serviceId || !$currentService) {
            $_SESSION['admin_error_message'] = 'Service not found.';
            header('Location: index.php?module=admin&action=list_services_admin');
            exit;
        }

        $toggledIsActive = !(bool)$currentService['is_active'];

        if ($this->adminServiceModel->updateServiceIsActive($serviceId, $toggledIsActive)) {
            $_SESSION['admin_success_message'] = 'Service "' . htmlspecialchars($currentService['name']) . '" status updated to ' . ($toggledIsActive ? 'Active' : 'Inactive') . '.';
        } else {
            $_SESSION['admin_error_message'] = 'Failed to update service status for "' . htmlspecialchars($currentService['name']) . '".';
        }
        header('Location: index.php?module=admin&action=list_services_admin');
        exit;
    }

    public function manageGlobalSettings() {
        $emergencyPause = $this->adminServiceModel->getGlobalSetting('emergency_pause_all_purchases');
        $maintenanceMode = $this->adminServiceModel->getGlobalSetting('site_maintenance_mode');
        // Add more settings as needed

        return [
            'pageTitle' => 'Global Application Settings',
            'settings' => [
                'emergency_pause_all_purchases' => $emergencyPause ?? '0', // Default to '0' if not set
                'site_maintenance_mode' => $maintenanceMode ?? '0'
            ]
        ];
    }

    public function updateGlobalSetting() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['setting_key'])) {
            $_SESSION['admin_error_message'] = 'Invalid request.';
            header('Location: index.php?module=admin&action=manage_global_settings');
            exit;
        }
        // CSRF checked globally

        $settingKey = trim(filter_input(INPUT_POST, 'setting_key', FILTER_SANITIZE_STRING));
        // For boolean-like settings, value is '1' if checkbox is checked, not present if unchecked.
        $settingValue = isset($_POST['setting_value']) ? '1' : '0';

        if (empty($settingKey)) {
            $_SESSION['admin_error_message'] = 'Setting key is missing.';
            header('Location: index.php?module=admin&action=manage_global_settings');
            exit;
        }

        // Validate known settings to prevent arbitrary key submissions
        $validSettings = ['emergency_pause_all_purchases', 'site_maintenance_mode'];
        if (!in_array($settingKey, $validSettings)) {
             $_SESSION['admin_error_message'] = 'Invalid setting key specified.';
            header('Location: index.php?module=admin&action=manage_global_settings');
            exit;
        }


        if ($this->adminServiceModel->updateGlobalSetting($settingKey, $settingValue)) {
            $_SESSION['admin_success_message'] = 'Setting "' . htmlspecialchars(ucwords(str_replace('_', ' ', $settingKey))) . '" updated successfully to ' . ($settingValue === '1' ? 'Enabled' : 'Disabled') . '.';
        } else {
            $_SESSION['admin_error_message'] = 'Failed to update setting "' . htmlspecialchars(ucwords(str_replace('_', ' ', $settingKey))) . '".';
        }
        header('Location: index.php?module=admin&action=manage_global_settings');
        exit;
    }
}
?>
