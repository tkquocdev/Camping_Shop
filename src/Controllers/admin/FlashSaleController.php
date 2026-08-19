<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\FlashSaleModel;
use App\Models\ProductModel;
use PDO;

class FlashSaleController extends Controller {

    private $flashSaleModel;
    private $productModel;

    public function __construct() {
        // 1. Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); 
            exit;
        }

        // 2. Khởi tạo Models
        $this->flashSaleModel = $this->model('FlashSaleModel');
        $this->productModel = $this->model('Product');
    }

    // 1. QUẢN LÝ DANH SÁCH FLASH SALE
    public function index() {
        // Lấy page từ URL, mặc định là 1
        $page = $_GET['page'] ?? 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Lấy danh sách Flash Sale
        $data = $this->flashSaleModel->getAll($limit, $offset);
        $total = $this->flashSaleModel->countAll();

        $this->view('admin/flash_sale/index', [
            'title' => 'Quản lý Flash Sale',
            'flash_sales' => $data,
            'totalPages' => ceil($total / $limit),
            'page' => $page
        ]);
    }

    // 2. TẠO MỚI
    public function create() {
        // Hiển thị form.php ở chế độ "tạo mới" (không có $sale)
        $this->view('admin/flash_sale/form', [
            'title' => 'Tạo Flash Sale mới'
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $status = $_POST['status'] ?? 0;

            // Validate cơ bản
            if (strtotime($end) <= strtotime($start)) {
                $_SESSION['flash_error'] = 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu!';
                header("Location: /admin/flash_sale/create");
                exit;
            }

            // Insert vào DB
            $id = $this->flashSaleModel->create([
                'name' => $name,
                'start_time' => $start,
                'end_time' => $end,
                'status' => $status
            ]);

            if ($id) {
                // Xử lý sản phẩm nếu có
                $productsJson = $_POST['products'] ?? '[]';
                $products = json_decode($productsJson, true);

                if (is_array($products) && count($products) > 0) {
                    foreach ($products as $product) {
                        $this->flashSaleModel->addItem([
                            'flash_sale_id' => $id,
                            'product_id' => $product['product_id'] ?? 0,
                            'sale_price' => $product['sale_price'] ?? 0,
                            'quantity' => $product['quantity'] ?? 0,
                            'sold' => 0
                        ]);
                    }
                    $_SESSION['flash_message'] = 'Tạo chương trình thành công và đã thêm sản phẩm!';
                } else {
                    $_SESSION['flash_message'] = 'Tạo chương trình thành công! Hãy thêm sản phẩm ngay.';
                }
                
                // Redirect sang trang edit để quản lý sản phẩm
                header("Location: /admin/flash_sale/edit/$id"); 
            } else {
                $_SESSION['flash_error'] = 'Lỗi hệ thống, vui lòng thử lại.';
                header("Location: /admin/flash_sale/create");
            }
            exit;
        }
    }

    // 3. QUẢN LÝ SẢN PHẨM TRONG SALE (Items)
    
    // Hiển thị trang items.php
    public function items($id) {
        $sale = $this->flashSaleModel->getById($id);

        if (!$sale) {
            $_SESSION['flash_error'] = 'Không tìm thấy đợt Flash Sale này!';
            header("Location: /admin/flash_sale");
            exit;
        }

        // Lấy danh sách SP đã có trong đợt sale này (kèm thông tin ảnh, tên SP gốc)
        $saleItems = $this->flashSaleModel->getItemsBySaleId($id);

        // Lấy tất cả sản phẩm trong kho để đổ vào dropdown
        $allProducts = $this->productModel->getAll(); 

        $this->viewAdmin('admin/flash_sale/items', [
            'title' => 'Sản phẩm Flash Sale',
            'flash_sale' => $sale,
            'sale_items' => $saleItems,
            'all_products' => $allProducts,
            'active' => 'flash_sale'
        ]);
    }

    // Thêm 1 sản phẩm vào Sale
    public function addItem() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $saleId = $_POST['flash_sale_id'] ?? 0;
            $productId = $_POST['product_id'] ?? 0;
            $salePrice = $_POST['sale_price'] ?? 0;
            $quantity = $_POST['quantity'] ?? 0;

            if (empty($saleId) || empty($productId)) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                exit;
            }

            // Validate: check xem sản phẩm này đã có trong đợt sale này chưa
            if ($this->flashSaleModel->checkProductInSale($saleId, $productId)) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm này đã tồn tại trong danh sách!']);
                exit;
            }

            // Insert
            $result = $this->flashSaleModel->addItem([
                'flash_sale_id' => $saleId,
                'product_id' => $productId,
                'sale_price' => $salePrice,
                'quantity' => $quantity,
                'sold' => 0
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm thành công!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm sản phẩm.']);
            }
            exit;
        }
    }

    // Xóa sản phẩm khỏi Sale
    public function removeItem() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $itemId = $_POST['item_id'] ?? 0;

            if (empty($itemId)) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                exit;
            }

            // Cần lấy sale_id trước khi xóa để redirect về đúng chỗ
            $item = $this->flashSaleModel->getItemDetail($itemId);
            
            if ($item) {
                $this->flashSaleModel->deleteItem($itemId);
                echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi đợt Sale.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại.']);
            }
            exit;
        }
    }

    // 4. SỬA & XÓA FLASH SALE

    public function edit($id) {
        $sale = $this->flashSaleModel->getById($id);
        if (!$sale) {
            $_SESSION['flash_error'] = 'Không tìm thấy flash sale';
            header("Location: /admin/flash_sale"); 
            exit;
        }

        // Lấy danh sách SP đã có trong đợt sale này
        $sale_items = $this->flashSaleModel->getItemsBySaleId($id);

        // Lấy tất cả sản phẩm trong kho để đổ vào dropdown
        $all_products = $this->productModel->getAll();

        // Sử dụng form.php unified (edit mode - có $sale)
        $this->view('admin/flash_sale/form', [
            'title' => 'Cập nhật Flash Sale',
            'sale' => $sale,
            'sale_items' => $sale_items,
            'all_products' => $all_products
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $status = $_POST['status'] ?? 0;

            if (strtotime($end) <= strtotime($start)) {
                $_SESSION['flash_error'] = 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu!';
                header("Location: /admin/flash_sale/edit/$id");
                exit;
            }

            $this->flashSaleModel->update($id, [
                'name' => $name,
                'start_time' => $start,
                'end_time' => $end,
                'status' => $status
            ]);

            $_SESSION['flash_message'] = 'Cập nhật thông tin thành công!';
            header("Location: /admin/flash_sale");
            exit;
        }
    }

    public function delete($id) {
        $this->flashSaleModel->delete($id);
        $_SESSION['flash_message'] = 'Đã xóa chương trình Flash Sale.';
        header("Location: /admin/flash_sale");
        exit;
    }

    // 5. API METHODS - Hỗ trợ AJAX
    
    public function get_products() {
        header('Content-Type: application/json');
        
        $categoryId = $_GET['category_id'] ?? null;
        
        try {
            $db = (new \App\Config\Database())->getConnection();
            
            // Lấy sản phẩm theo danh mục
            if ($categoryId) {
                $stmt = $db->prepare("
                    SELECT id, name, price FROM products WHERE category_id = ? ORDER BY name ASC
                ");
                $stmt->execute([$categoryId]);
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Nếu không có category_id, trả về mảng rỗng
                $products = [];
            }
            
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function get_categories() {
        header('Content-Type: application/json');
        
        try {
            // Lấy tất cả danh mục từ database
            $db = (new \App\Config\Database())->getConnection();
            $stmt = $db->prepare("SELECT id, name FROM categories ORDER BY name ASC");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'categories' => $categories]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
}
