<?php
namespace App\Controllers\Client;

use App\Core\Controller;

class NewsController extends Controller {

    // 1. Hiển thị danh sách tất cả tin tức
    public function index() {
        // Gọi Model News
        $newsModel = $this->model('News');
        
        // Lấy dữ liệu
        $newsList = $newsModel->getAll();

        // Gọi View hiển thị (folder: views/client/news/index.php)
        // 'active' => 'news' dùng để bôi đậm menu trên Header
        $this->view('client/news/index', [
            'newsList' => $newsList,
            'active' => 'news',
            'title' => 'Tin tức & Sự kiện'
        ]);
    }

    // 2. Hiển thị chi tiết một bài tin tức
    public function detail() {
        // Lấy ID từ URL (VD: /news/detail/5)
        $id = $_GET['id'] ?? null;

        if (!$id) {
            // Nếu không có ID, quay về trang danh sách
            header("Location: /news"); 
            exit;
        }

        $newsModel = $this->model('News');
        $news = $newsModel->getById($id);

        // Kiểm tra nếu tin tức không tồn tại (nhập ID bậy)
        if (!$news) {
            // Có thể redirect về trang 404 hoặc quay về danh sách
            header("Location: /news"); 
            exit;
        }

        // Gọi View chi tiết (folder: views/client/news/detail.php)
        $this->view('client/news/detail', [
            'news' => $news,
            'active' => 'news',
            'title' => $news['title'] // Tiêu đề tab trình duyệt theo tên bài viết
        ]);
    }
}
