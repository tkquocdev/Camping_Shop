<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class StockIssueController extends Controller {

    // 1. Danh sách
    public function index() {
        $model = $this->model('StockIssue');
        $issues = $model->getAllIssues(); // Bạn cần đảm bảo hàm này có trong Model

        $this->view('admin/stock_issue/index', [
            'active' => 'stock_issue',
            'title' => 'Lịch sử xuất kho',
            'issues' => $issues
        ]);
    }

    // 2. Form tạo mới
    public function create() {
        // Gọi ProductModel để lấy danh sách sản phẩm
        $productModel = $this->model('Product');
        $products = $productModel->getAll(); // Hoặc hàm lấy sản phẩm có sẵn của bạn

        $this->view('admin/stock_issue/create', [
            'active' => 'stock_issue',
            'title' => 'Tạo phiếu xuất kho',
            'products' => $products
        ]);
    }

    // 3. Xử lý Lưu (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/StockIssue');
            exit;
        }

        // Lấy dữ liệu từ Form
        $note = $_POST['note'] ?? '';
        $product_ids = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $prices = $_POST['price'] ?? [];
        $user_id = $_SESSION['user']['id'] ?? 1; // Lấy ID user đang login

        // Chuẩn bị dữ liệu chi tiết để gửi sang Model
        $items = [];
        for ($i = 0; $i < count($product_ids); $i++) {
            if (!empty($product_ids[$i]) && isset($quantities[$i]) && $quantities[$i] > 0) {
                $items[] = [
                    'product_id' => $product_ids[$i],
                    'quantity'   => (int)$quantities[$i],
                    'price'      => (int)($prices[$i] ?? 0)
                ];
            }
        }

        // Kiểm tra dữ liệu rỗng
        if (empty($items)) {
            header('Location: /admin/StockIssue/create?error=empty');
            exit;
        }

        // Gọi Model để xử lý Transaction
        $model = $this->model('StockIssue');
        
        // Hàm createIssue sẽ trả về true hoặc false (hoặc ném exception)
        $result = $model->createIssue($user_id, $note, $items);

        if ($result === true) {
            header('Location: /admin/StockIssue?msg=success');
        } else {
            // Nếu model trả về chuỗi lỗi (string), có thể hiển thị nó
            $errorMsg = is_string($result) ? $result : 'failed';
            header('Location: /admin/StockIssue/create?error=' . urlencode($errorMsg));
        }
    }

    // 4. In phiếu
    public function print($id) {
        $model = $this->model('StockIssue');
        
        $issue = $model->getIssueById($id);
        $details = $model->getIssueDetails($id);

        if (!$issue) {
            die("Phiếu không tồn tại");
        }

        // Gọi view in (Lùi thư mục tùy cấu trúc, ở đây dùng view helper cho chuẩn)
        $this->view('admin/stock_issue/print', [
            'issue' => $issue,
            'details' => $details
        ]);
    }
}