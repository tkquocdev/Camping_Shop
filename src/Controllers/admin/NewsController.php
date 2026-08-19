<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class NewsController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. Hiển thị danh sách tin tức
    public function index() {
        $newsModel = $this->model('News');
        $newsList = $newsModel->getAll();

        $this->viewAdmin('admin/news/index', [
            'newsList' => $newsList,
            'active' => 'news'
        ]);
    }

    // 2. Hiển thị form thêm mới
    public function create() {
        $this->viewAdmin('admin/news/create', [
            'active' => 'news'
        ]);
    }

    // 3. Xử lý lưu tin mới (Action Store)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'] ?? '';
            $summary = $_POST['summary'] ?? '';
            $content = $_POST['content'] ?? '';

            // Xử lý upload ảnh
            $imageName = ''; // Chỉ lưu tên file
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Đường dẫn thư mục thực tế trên server (dùng dấu / để tránh lỗi trên Linux/Mac)
                $targetDir = "uploads/news/"; 
                
                // Tạo thư mục nếu chưa có (trong folder public)
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                // Đặt tên file: time + tên gốc
                $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $imageName = $fileName; // Chỉ lưu tên file vào DB
                }
            }

            // Gọi Model để lưu
            $newsModel = $this->model('News');
            $newsModel->create([
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'image' => $imageName
            ]);

            $_SESSION['flash_message'] = "Thêm tin tức thành công!";
            header("Location: /admin/news");
            exit;
        }
    }

    // 4. Hiển thị form sửa tin tức (FIX LỖI ArgumentCountError)
    // Xóa tham số $id, lấy id từ $_GET
    public function edit() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: /admin/news"); exit;
        }

        $newsModel = $this->model('News');
        $news = $newsModel->getById($id);

        if (!$news) {
            header("Location: /admin/news"); exit;
        }

        $this->viewAdmin('admin/news/edit', [
            'news' => $news,
            'active' => 'news'
        ]);
    }

    // 5. Xử lý cập nhật tin tức (FIX LỖI ArgumentCountError)
    // Xóa tham số $id, lấy id từ $_POST (form gửi lên)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null; // Lấy ID từ input hidden
            
            if (!$id) {
                header("Location: /admin/news"); exit;
            }

            $newsModel = $this->model('News');
            $currentNews = $newsModel->getById($id);

            $title = $_POST['title'];
            $summary = $_POST['summary'];
            $content = $_POST['content'];
            
            // Mặc định giữ tên ảnh cũ
            $imageName = $currentNews['image']; 

            // Nếu có upload ảnh mới
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $targetDir = "uploads/news/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES["image"]["name"]);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $imageName = $fileName; // Cập nhật tên ảnh mới
                    
                    // (Tùy chọn) Xóa file ảnh cũ vật lý nếu cần
                    // $oldFile = $targetDir . $currentNews['image'];
                    // if (!empty($currentNews['image']) && file_exists($oldFile)) unlink($oldFile);
                }
            }

            $newsModel->update($id, [
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'image' => $imageName
            ]);

            $_SESSION['flash_message'] = "Cập nhật tin tức thành công.";
            header("Location: /admin/news");
            exit;
        }
    }

    // 6. Xóa tin tức (FIX LỖI ArgumentCountError)
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;

            if ($id) {
                $newsModel = $this->model('News');
                // Logic xóa ảnh vật lý nếu muốn thêm ở đây
                $newsModel->delete($id);
                $_SESSION['flash_message'] = "Đã xóa tin tức.";
            }
        }
        
        header("Location: /admin/news");
        exit;
    }
}
