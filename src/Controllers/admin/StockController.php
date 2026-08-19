<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class StockController extends Controller {

    public function index() {
        // Kiểm tra quyền Admin (Code check quyền của bạn)
        // if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { ... }

        $model = $this->model('StockImport');
        $imports = $model->getAllImports();
        
        // Gọi thẳng vào View con trong thư mục stock
        $this->view('admin/stock/index', [
            'active' => 'stock', // Biến này để sidebar biết dòng nào đang Active
            'title' => 'Lịch sử nhập kho',
            'imports' => $imports
        ]);
    }

    public function create() {
        $stockModel = $this->model('StockImport');
        $productModel = $this->model('Product'); 

        // Lấy dữ liệu sản phẩm và nhà cung cấp
        $products = $productModel->getAll(); 
        $suppliers = $stockModel->getSuppliers();

        // Gọi thẳng vào View con form tạo
        $this->view('admin/stock/create', [
            'active' => 'stock',
            'title' => 'Tạo phiếu nhập hàng',
            'products' => $products,
            'suppliers' => $suppliers
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplier_id = $_POST['supplier_id'];
            $note = $_POST['note'];
            
            // Xử lý mảng sản phẩm từ Form
            $product_ids = $_POST['product_id'] ?? []; 
            $quantities = $_POST['quantity'] ?? [];   
            $prices = $_POST['price'] ?? [];          

            $items = [];
            for ($i = 0; $i < count($product_ids); $i++) {
                if (!empty($product_ids[$i]) && $quantities[$i] > 0) {
                    $items[] = [
                        'product_id' => $product_ids[$i],
                        'quantity' => $quantities[$i],
                        'price' => $prices[$i]
                    ];
                }
            }

            if (empty($items)) {
                // Báo lỗi: Chưa chọn sản phẩm
                header('Location: /admin/stock/create?error=empty');
                exit;
            }

            $model = $this->model('StockImport');
            if ($model->createImport(['supplier_id' => $supplier_id, 'note' => $note], $items)) {
                header('Location: /admin/stock?msg=success');
            } else {
                header('Location: /admin/stock/create?error=failed');
            }
        }
    }


    // Hàm print phiếu nhập (Bạn cần tạo view admin/stock/print.php để hiển thị đẹp hơn)
    public function print($id) {
        $model = $this->model('StockImport');
        $import = $model->getImportById($id);
        $items = $model->getImportItems($id);

        if (!$import) {
            header('Location: /admin/stock?msg=notfound');
            exit;
        }

        // Gọi view in phiếu nhập
        $this->view('admin/stock/print', [
            'import' => $import,
            'items' => $items
        ]);
    }

    public function delete($id) {
        // Kiểm tra quyền Admin (nếu cần)
        // if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') { header('Location: /admin/login'); exit; }

        $model = $this->model('StockImport');

        if ($model->deleteImport($id)) {
            header('Location: /admin/stock?msg=deleted');
        } else {
            header('Location: /admin/stock?msg=error');
        }
    }
}