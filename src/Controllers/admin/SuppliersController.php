<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class SuppliersController extends Controller {

    public function __construct() {
        // Check quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. Danh sách
    public function index() {
        $supplierModel = $this->model('Supplier');
        $suppliers = $supplierModel->getAll();

        $this->viewAdmin('admin/suppliers/index', [
            'suppliers' => $suppliers,
            'active' => 'suppliers' // Để active menu
        ]);
    }

    // 2. Form Thêm mới
    public function create() {
        $this->viewAdmin('admin/suppliers/create', [
            'active' => 'suppliers'
        ]);
    }

    // 3. Xử lý lưu (Store)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $address = $_POST['address'] ?? '';

            if (empty($name)) {
                $_SESSION['flash_error'] = "Tên nhà cung cấp không được để trống";
                header("Location: /admin/suppliers/create"); exit;
            }

            $model = $this->model('Supplier');
            if ($model->create($name, $phone, $email, $address)) {
                $_SESSION['flash_message'] = "Thêm nhà cung cấp thành công!";
                header("Location: /admin/suppliers");
            } else {
                $_SESSION['flash_error'] = "Lỗi hệ thống, vui lòng thử lại.";
                header("Location: /admin/suppliers/create");
            }
        }
    }

    // 4. Form Sửa
    public function edit($id) {
        $model = $this->model('Supplier');
        $supplier = $model->getById($id);

        if (!$supplier) {
            header("Location: /admin/suppliers"); exit;
        }

        $this->viewAdmin('admin/suppliers/edit', [
            'supplier' => $supplier,
            'active' => 'suppliers'
        ]);
    }

    // 5. Xử lý cập nhật
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $address = $_POST['address'];

            $model = $this->model('Supplier');
            if ($model->update($id, $name, $phone, $email, $address)) {
                $_SESSION['flash_message'] = "Cập nhật thành công!";
                header("Location: /admin/suppliers");
            } else {
                $_SESSION['flash_error'] = "Lỗi khi cập nhật.";
                header("Location: /admin/suppliers/edit/$id");
            }
        }
    }

    // 6. Xóa
    public function delete($id) {
        $model = $this->model('Supplier');
        try {
            $model->delete($id);
            $_SESSION['flash_message'] = "Đã xóa nhà cung cấp.";
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Không thể xóa NCC này vì đã có dữ liệu nhập kho liên quan.";
        }
        header("Location: /admin/suppliers");
    }
}