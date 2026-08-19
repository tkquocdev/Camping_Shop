<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class CouponsController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. Danh sách mã giảm giá
    public function index() {
        $couponModel = $this->model('Coupon');
        $coupons = $couponModel->getAll();

        $this->viewAdmin('admin/coupons/index', [
            'coupons' => $coupons,
            'active' => 'coupons'
        ]);
    }

    // 2. Hiển thị form tạo mới
    public function create() {
        $this->viewAdmin('admin/coupons/create', [
            'active' => 'coupons'
        ]);
    }

    // 3. Xử lý lưu (Store)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Xử lý mã: Viết hoa, xóa khoảng trắng
            $code = strtoupper(trim($_POST['code']));
            $couponModel = $this->model('Coupon');

            // --- CHECK TRÙNG MÃ KHI TẠO ---
            if ($couponModel->exists($code)) {
                $_SESSION['flash_error'] = "Mã '$code' đã tồn tại! Vui lòng chọn mã khác.";
                header("Location: /admin/coupons/create");
                exit;
            }
            
            // Xử lý ngày hết hạn (lấy expiration_date hoặc end_date tùy form gửi lên)
            $endDate = $_POST['end_date'] ?? $_POST['expiration_date'] ?? null;

            // Lấy dữ liệu từ Form
            $data = [
                ':code'             => $code,
                ':name'             => $_POST['name'] ?? $code,
                ':discount_type'    => $_POST['discount_type'],
                ':discount_value'   => $_POST['discount_value'],
                ':min_order_value'  => $_POST['min_order_value'] ?? 0,
                ':quantity'         => $_POST['quantity'] ?? 100,
                ':start_date'       => $_POST['start_date'] ?? date('Y-m-d H:i:s'),
                ':end_date'         => $endDate,
                ':is_private'       => isset($_POST['is_private']) ? 1 : 0, 
                ':status'           => isset($_POST['status']) ? 1 : 0
            ];

            if ($couponModel->create($data)) {
                $_SESSION['flash_message'] = "Tạo mã giảm giá thành công!";
                header("Location: /admin/coupons");
            } else {
                $_SESSION['flash_error'] = "Có lỗi xảy ra, vui lòng thử lại.";
                header("Location: /admin/coupons/create");
            }
            exit;
        }
    }
    
    // 4. Hiển thị form chỉnh sửa
    public function edit() { 
        // Lấy ID từ URL (dạng ?id=1)
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $_SESSION['flash_error'] = "Không tìm thấy ID mã giảm giá!";
            header("Location: /admin/coupons");
            exit;
        }

        $couponModel = $this->model('Coupon');
        $coupon = $couponModel->getById($id);

        if (!$coupon) {
            $_SESSION['flash_error'] = "Mã giảm giá không tồn tại!";
            header("Location: /admin/coupons");
            exit;
        }

        // Gọi view edit
        $this->viewAdmin('admin/coupons/edit', [
            'coupon' => $coupon,
            'active' => 'coupons'
        ]);
    }

    // 5. Xử lý cập nhật (UPDATE) - Đã sửa lỗi
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            
            // Lấy mã mới và làm sạch
            $code = strtoupper(trim($_POST['code']));

            if (!$id) {
                $_SESSION['flash_error'] = "Lỗi dữ liệu: Thiếu ID.";
                header("Location: /admin/coupons");
                exit;
            }

            $couponModel = $this->model('Coupon');

            // --- [QUAN TRỌNG] CHECK TRÙNG MÃ KHI UPDATE ---
            // Nếu người dùng đổi tên mã, phải check xem tên mới có trùng với mã khác không
            if ($couponModel->isCodeExistsForUpdate($code, $id)) {
                $_SESSION['flash_error'] = "Mã '$code' đã tồn tại! Vui lòng chọn mã khác.";
                // Redirect lại trang edit để sửa lại
                header("Location: /admin/coupons/edit?id=$id"); 
                exit;
            }

            // Xử lý ngày hết hạn
            $endDate = $_POST['end_date'] ?? $_POST['expiration_date'] ?? null;

            $data = [
                ':id'               => $id,
                ':code'             => $code, // <--- Cập nhật mã code mới
                ':name'             => $_POST['name'],
                ':discount_type'    => $_POST['discount_type'],
                ':discount_value'   => $_POST['discount_value'],
                ':min_order_value'  => $_POST['min_order_value'] ?? 0,
                ':quantity'         => $_POST['quantity'],
                ':start_date'       => $_POST['start_date'],
                ':end_date'         => $endDate,
                ':is_private'       => isset($_POST['is_private']) ? 1 : 0,
                ':status'           => isset($_POST['status']) ? 1 : 0,
            ];
            
            if ($couponModel->update($data)) {
                $_SESSION['flash_message'] = "Cập nhật mã '$code' thành công!";
                header("Location: /admin/coupons");
            } else {
                $_SESSION['flash_error'] = "Cập nhật thất bại. Vui lòng thử lại.";
                header("Location: /admin/coupons/edit?id=$id");
            }
            exit;
        }
    }

    // 6. Xóa mã
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $couponModel = $this->model('Coupon');
            
            if ($couponModel->delete($id)) {
                $_SESSION['flash_message'] = "Đã xóa mã khuyến mãi.";
            } else {
                $_SESSION['flash_error'] = "Xóa thất bại.";
            }
        }
        header("Location: /admin/coupons");
        exit;
    }
}