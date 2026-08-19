<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class LoyaltyController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. Hiển thị danh sách gói đổi thưởng
    public function index() {
        $loyaltyModel = $this->model('LoyaltyModel');
        $rewards = $loyaltyModel->getAllRewards();

        $this->viewAdmin('admin/loyalty/index', [
            'rewards' => $rewards ?? [],
            'active' => 'loyalty'
        ]);
    }

    // 2. Xử lý thêm gói đổi thưởng mới
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/loyalty"); exit;
        }

        $name = $_POST['name'] ?? '';
        $points = (int)($_POST['points_required'] ?? 0);
        $value = (int)($_POST['voucher_value'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (empty($name) || $points <= 0 || $value <= 0) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
            header("Location: /admin/loyalty"); exit;
        }

        $loyaltyModel = $this->model('LoyaltyModel');
        if ($loyaltyModel->createReward($name, $points, $value, $status)) {
            $_SESSION['flash_message'] = "Đã thêm gói đổi thưởng mới thành công!";
        } else {
            $_SESSION['error'] = "Thêm gói đổi thưởng thất bại!";
        }

        header("Location: /admin/loyalty"); exit;
    }

    // 3. Update gói đổi thưởng
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/loyalty"); exit;
        }

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $points = (int)($_POST['points_required'] ?? 0);
        $value = (int)($_POST['voucher_value'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        if (!$id || empty($name) || $points <= 0 || $value <= 0) {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
            header("Location: /admin/loyalty"); exit;
        }

        $loyaltyModel = $this->model('LoyaltyModel');
        if ($loyaltyModel->updateReward($id, $name, $points, $value, $status)) {
            $_SESSION['flash_message'] = "Đã cập nhật gói đổi thưởng thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật gói đổi thưởng thất bại!";
        }

        header("Location: /admin/loyalty"); exit;
    }

    // 4. Xóa gói đổi thưởng
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/loyalty"); exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "ID gói quà không hợp lệ!";
            header("Location: /admin/loyalty"); exit;
        }

        $loyaltyModel = $this->model('LoyaltyModel');
        if ($loyaltyModel->deleteReward($id)) {
            $_SESSION['flash_message'] = "Đã xóa gói đổi thưởng thành công!";
        } else {
            $_SESSION['error'] = "Xóa gói đổi thưởng thất bại!";
        }

        header("Location: /admin/loyalty"); exit;
    }
}
