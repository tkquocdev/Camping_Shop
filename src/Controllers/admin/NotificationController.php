<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use PDO;

class NotificationController extends Controller {

    public function index() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $notificationModel = $this->model('Notification');
        $notifications = $notificationModel->getAllNotifications();

        $this->view('admin/notifications/index', [
            'notifications' => $notifications,
            'active' => 'notifications'
        ]);
    }

    public function create() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $link = $_POST['link'] ?? null;
            $type = $_POST['type'] ?? 'general';
            $userId = !empty($_POST['user_id']) ? $_POST['user_id'] : null;

            if (empty($title) || empty($message)) {
                $_SESSION['flash_error'] = "Vui lòng điền đầy đủ thông tin!";
                header("Location: /admin/notifications/create");
                exit;
            }

            $notificationModel = $this->model('Notification');
            $notificationModel->createFromArray([
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'type' => $type,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $_SESSION['flash_message'] = "Gửi thông báo thành công!";
            header("Location: /admin/notifications");
            exit;
        }

        $this->view('admin/notifications/create', [
            'active' => 'notifications'
        ]);
    }

    public function delete($id = null) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notifId = $_POST['notification_id'] ?? $id;
            
            $notificationModel = $this->model('Notification');
            if ($notificationModel->delete($notifId)) {
                $_SESSION['flash_message'] = "Xóa thông báo thành công!";
            } else {
                $_SESSION['flash_error'] = "Lỗi xóa thông báo!";
            }
        }

        header("Location: /admin/notifications");
        exit;
    }
}
