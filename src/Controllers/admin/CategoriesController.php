<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class CategoriesController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }
    }

    public function index() {
        $categoryModel = $this->model('Category');
        $categories = $categoryModel->getAllCategories();

        $this->view('admin/categories/index', [
            'categories' => $categories,
            'active' => 'categories'
        ]);
    }

    // New edit method to show edit modal
    public function edit($id = null) {
        if (!$id) {
            $_SESSION['flash_error'] = "ID danh mục không hợp lệ.";
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');
        $category = $categoryModel->findById($id);

        if (!$category) {
            $_SESSION['flash_error'] = "Danh mục không tồn tại.";
            header('Location: /admin/categories');
            exit;
        }

        // Return JSON for modal
        header('Content-Type: application/json');
        echo json_encode($category);
        exit;
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/categories');
            exit;
        }

        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$name) {
            $_SESSION['flash_error'] = "Tên danh mục không được để trống.";
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');
        if ($categoryModel->create(['name' => $name, 'description' => $description])) {
            $_SESSION['flash_message'] = "Tạo danh mục thành công";
        } else {
            $_SESSION['flash_error'] = "Tạo danh mục thất bại";
        }
        header('Location: /admin/categories');
        exit;
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/categories');
            exit;
        }

        // Nếu gọi qua form POST, id sẽ là trong $_POST
        if (!$id) {
            $id = $_POST['id'] ?? null;
        }

        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$id || !$name) {
            $_SESSION['flash_error'] = "Dữ liệu không hợp lệ.";
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');
        if ($categoryModel->update($id, ['name' => $name, 'description' => $description])) {
            $_SESSION['flash_message'] = "Cập nhật danh mục thành công";
        } else {
            $_SESSION['flash_error'] = "Cập nhật danh mục thất bại";
        }

        header('Location: /admin/categories');
        exit;
    }

    public function delete($id = null) {
        // Nếu gọi qua form POST, id sẽ là trong $_POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
        }

        if (!$id) {
            $_SESSION['flash_error'] = "ID danh mục không hợp lệ.";
            header('Location: /admin/categories');
            exit;
        }

        $categoryModel = $this->model('Category');
        if ($categoryModel->delete($id)) {
            $_SESSION['flash_message'] = "Xóa danh mục thành công";
        } else {
            $_SESSION['flash_error'] = "Xóa danh mục thất bại";
        }
        header('Location: /admin/categories');
        exit;
    }
}