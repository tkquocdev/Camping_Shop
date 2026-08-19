<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class ProductsController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }
    }

    public function index() {
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');
        $products = $productModel->getAllProducts();
        $categories = $categoryModel->getAllCategories();

        $data = [
            'products' => $products,
            'categories' => $categories,
            'active' => 'products'
        ];

        $this->view('admin/products/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit;
        }

        $productModel = $this->model('Product');

        $data = [
            'category_id' => $_POST['category_id'] ?? null,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'stock' => $_POST['stock'] ?? 0,
            'image' => null
        ];

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/products/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $data['image'] = $fileName;
            }
        }

        if ($productModel->create($data)) {
            $_SESSION['flash_message'] = "Tạo sản phẩm thành công";
        } else {
            $_SESSION['flash_error'] = "Tạo sản phẩm thất bại";
        }

        header('Location: /admin/products');
        exit;
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit;
        }

        $productModel = $this->model('Product');
        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: /admin/products');
            exit;
        }

        $existing = $productModel->findById($id);
        if (!$existing) {
            header('Location: /admin/products');
            exit;
        }

        $data = [
            'category_id' => $_POST['category_id'] ?? $existing['category_id'],
            'name' => $_POST['name'] ?? $existing['name'],
            'description' => $_POST['description'] ?? $existing['description'],
            'price' => $_POST['price'] ?? $existing['price'],
            'stock' => $_POST['stock'] ?? $existing['stock'],
            'image' => $existing['image'] ?? null
        ];

        // Handle image upload (optional)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/products/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $targetDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $data['image'] = $fileName;
            }
        }

        if ($productModel->update($id, $data)) {
            $_SESSION['flash_message'] = "Cập nhật sản phẩm thành công";
        } else {
            $_SESSION['flash_error'] = "Cập nhật sản phẩm thất bại";
        }

        header('Location: /admin/products');
        exit;
    }

    public function delete($id) {
        $productModel = $this->model('Product');
        if ($productModel->delete($id)) {
            $_SESSION['flash_message'] = "Xóa sản phẩm thành công";
        } else {
            $_SESSION['flash_error'] = "Xóa sản phẩm thất bại";
        }

        header('Location: /admin/products');
        exit;
    }
}
