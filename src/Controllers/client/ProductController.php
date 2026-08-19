<?php
namespace App\Controllers\Client;

use App\Core\Controller;

class ProductController extends Controller {

    public function index() {
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');

        // 1. Lấy danh mục
        $categories = $categoryModel->getAll();

        // 2. Lấy các tham số từ URL
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

        // 3. Gọi Model
        if ($categoryId) {
            $products = $productModel->getProductsByCategory($categoryId, $sort);
            
            // Lấy tên danh mục
            $currentCategoryName = 'Danh mục';
            foreach($categories as $cat) {
                if($cat['id'] == $categoryId) {
                    $currentCategoryName = $cat['name'];
                    break;
                }
            }
            $title = $currentCategoryName;
        } else {
            $products = $productModel->getAllProducts($sort);
            $title = 'Tất cả sản phẩm';
        }

        // 4. ADD FLASH SALE LOGIC TO PRODUCTS
        $fsModel = $this->model('FlashSaleModel');
        $activeSale = $fsModel->getActiveFlashSale();
        
        if ($activeSale) {
            foreach ($products as &$product) {
                $saleItem = $fsModel->checkProductInFlashSale($activeSale['id'], $product['id']);
                if ($saleItem && $saleItem['quantity'] > $saleItem['sold']) {
                    $product['flash_sale_price'] = $saleItem['sale_price'];
                    $product['flash_sale_original_price'] = $product['price'];
                }
            }
        }

        // 5. Xử lý giá hiển thị (Nếu có flash sale thì hiển thị giá sale, nếu không có thì hiển thị giá gốc)
        foreach ($products as &$product) {
            if (!isset($product['flash_sale_price'])) {
                if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']) {
                    $product['display_price'] = $product['discount_price'];
                    $product['original_price'] = $product['price'];
                } else {
                    $product['display_price'] = $product['price'];
                }
            }
        }

        $data = [
            'products' => $products,
            'categories' => $categories,
            'current_category_id' => $categoryId,
            'current_sort' => $sort,
            'page_title' => $title . ' - Camping Shop'
        ];

        $this->view('client/products/index', $data);
    }

    // TRANG CHI TIẾT SẢN PHẨM
    public function detail($id = null) {
        if (!$id) { header("Location: /"); exit; }

        $productModel = $this->model('Product');
        $product = $productModel->findById($id);

        if (!$product) die("Sản phẩm không tồn tại!");

        // 1. Lấy đánh giá của sản phẩm này
        $reviewModel = $this->model('Review');
        $reviews = $reviewModel->getReviewsByProduct($id);

        $avgRating = 0;
        if (count($reviews) > 0) {
            $totalRating = 0;
            foreach ($reviews as $r) {
                $totalRating += $r['rating'];
            }
            $avgRating = round($totalRating / count($reviews), 1);
        }

        // 2. LOGIC CHECK FLASH SALE
        $fsModel = $this->model('FlashSaleModel');
        
        // Lấy đợt sale đang diễn ra
        $activeSale = $fsModel->getActiveFlashSale();
        $saleInfo = null;

        if ($activeSale) {
            // Kiểm tra xem sản phẩm này có nằm trong đợt sale đó không
            $checkItem = $fsModel->checkProductInFlashSale($activeSale['id'], $id);
            
            if ($checkItem) {
                // Kiểm tra số lượng còn lại trong kho Flash Sale (quantity > sold)
                if ($checkItem['quantity'] > $checkItem['sold']) {
                    $saleInfo = $checkItem;
                    // Bổ sung thêm thông tin thời gian kết thúc để làm đồng hồ đếm ngược
                    $saleInfo['end_time'] = $activeSale['end_time'];
                }
            }
        }
        // ================================================================

        $data = [
            'product' => $product,
            'reviews' => $reviews,
            'avg_rating' => $avgRating,
            'page_title' => $product['name'],
            
            // Truyền biến này ra View để hiển thị
            'sale_info' => $saleInfo // Nếu null = không sale, có dữ liệu = đang sale
        ];

        $this->view('client/products/detail', $data);
    }

    // Xử lý tìm kiếm
    public function search() {
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        $products = [];
        $title = "Tìm kiếm sản phẩm";

        if (!empty($keyword)) {
            $productModel = $this->model('Product');
            $products = $productModel->search($keyword);
            $title = "Kết quả tìm kiếm cho: '" . htmlspecialchars($keyword) . "'";
        } else {
            header("Location: /product");
            exit;
        }

        $this->view('client/products/index', [
            'products' => $products,
            'page_title' => $title,
            'categories' => $this->model('Category')->getAll(),
            'current_category_id' => null,
            'current_sort' => 'newest'
        ]);
    }

    // Trang khuyến mãi/coupon
    public function coupons() {
        $couponModel = $this->model('Coupon');
        $coupons = $couponModel->getAllActive();

        $this->view('client/coupons/index', [
            'coupons' => $coupons,
            'page_title' => 'Khuyến mãi - Camping Shop'
        ]);
    }
}