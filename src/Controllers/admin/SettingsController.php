<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Utils\Settings;

class SettingsController extends Controller {

    public function index() {
        // Check if admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $data = [
            'active' => 'settings',
            'settings' => Settings::all()
        ];

        $this->view('admin/settings/index', $data);
    }

    public function save() {
        // Check if admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $siteName = $_POST['site_name'] ?? '';
        $maintenanceMode = isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] === '1';
        $maintenanceMessage = $_POST['maintenance_message'] ?? '';
        $contactEmail = $_POST['contact_email'] ?? '';
        $storePhone = $_POST['store_phone'] ?? '';

        Settings::set('site_name', trim($siteName));
        Settings::set('maintenance_mode', $maintenanceMode);
        Settings::set('maintenance_message', trim($maintenanceMessage));
        Settings::set('contact_email', trim($contactEmail));
        Settings::set('store_phone', trim($storePhone));

        $_SESSION['flash_message'] = 'Cập nhật cài đặt thành công.';
        header('Location: /admin/settings');
        exit;
    }
}
