<?php
namespace App\Controllers\Client;

use App\Core\Controller;

class CartController extends Controller {

    // 1. XEM GIỎ HÀNG (Logic tính giá Flash Sale hiển thị ở đây)
    public function index() {
        $cartModel = $this->model('Cart');
        $fsModel = $this->model('FlashSaleModel'); // Gọi Model Flash Sale

        $products = [];
        $totalPrice = 0;

        // [A] Lấy dữ liệu thô từ DB hoặc Session
        if (isset($_SESSION['user'])) {
            // Đã đăng nhập
            $userId = $_SESSION['user']['id'];
            $products = $cartModel->getUserCart($userId);
        } else {
            // Khách vãng lai
            $cart = $_SESSION['cart'] ?? [];
            if (!empty($cart)) {
                $products = $cartModel->getProductsByIds(array_keys($cart));
                // Map số lượng từ Session vào mảng sản phẩm
                foreach ($products as &$p) {
                    $p['buy_quantity'] = $cart[$p['id']] ?? 1;
                }
            }
        }

        // [B] LOGIC KIỂM TRA FLASH SALE & TÍNH TỔNG TIỀN
        // Lấy đợt sale đang hoạt động
        $activeSale = $fsModel->getActiveFlashSale();

        foreach ($products as &$product) {
            // 1. Mặc định là giá gốc
            $currentPrice = $product['price'];
            $product['is_flash_sale'] = false; // Cờ đánh dấu để hiện thị UI

            // 2. Nếu có đợt Sale, kiểm tra sản phẩm này có trong đó không
            if ($activeSale) {
                $saleItem = $fsModel->checkProductInFlashSale($activeSale['id'], $product['id']);
                
                // Điều kiện: Có trong list sale VÀ số lượng bán chưa vượt quá giới hạn
                if ($saleItem && $saleItem['quantity'] > $saleItem['sold']) {
                    $currentPrice = $saleItem['sale_price'];
                    $product['is_flash_sale'] = true; // Đánh dấu là đang Sale
                }
            }

            // 3. Cập nhật giá hiển thị và tính tổng
            $product['price'] = $currentPrice; // Ghi đè giá hiển thị
            $product['row_total'] = $currentPrice * $product['buy_quantity'];
            $totalPrice += $product['row_total'];
        }

        // [C] Truyền dữ liệu ra View
        $this->view('client/cart/index', [
            'cart_products' => $products,
            'total_price' => $totalPrice,
            'page_title' => 'Giỏ hàng của bạn'
        ]);
    }

    // 2. THÊM SẢN PHẨM VÀO GIỎ HÀNG
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit;
        }

        // Mua lại từ đơn hàng cũ
        $reorderId = (int)($_POST['reorder_id'] ?? 0);
        if ($reorderId > 0) {
            if (!isset($_SESSION['user'])) {
                $_SESSION['flash_message'] = "Vui lòng đăng nhập để mua lại!";
                header('Location: /auth/login');
                exit;
            }

            // Gọi Model Order để lấy chi tiết đơn hàng cũ
            $orderModel = $this->model('Order');
            $orderItems = $orderModel->getOrderItems($reorderId);

            if (empty($orderItems)) {
                $_SESSION['flash_message'] = "Không tìm thấy đơn hàng hoặc đơn hàng không có sản phẩm!";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Kiểm tra đơn hàng có thuộc về user hiện tại không
            $order = $orderModel->getOrderById($reorderId);
            if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
                $_SESSION['flash_message'] = "Bạn không có quyền mua lại đơn hàng này!";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Chuẩn bị dữ liệu sản phẩm để chuyển thẳng vào checkout (giống mua ngay)
            $buyNowItems = [];
            $productModel = $this->model('Product');
            $itemsAdded = 0;

            foreach ($orderItems as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                // Kiểm tra sản phẩm còn tồn tại và đủ hàng không
                $product = $productModel->findById($productId);
                if ($product && $quantity > 0) {
                    $buyNowItems[$productId] = $quantity;
                    $itemsAdded++;
                }
            }

            if ($itemsAdded > 0) {
                // Xóa session mua ngay cũ nếu có
                if (isset($_SESSION['buy_now_temp'])) {
                    unset($_SESSION['buy_now_temp']);
                }
                
                // Tạo session mới chỉ chứa các sản phẩm này
                $_SESSION['buy_now_temp'] = $buyNowItems;
                
                // Chuyển hướng sang Checkout
                header('Location: /checkout');
            } else {
                $_SESSION['flash_message'] = "Không có sản phẩm nào có thể mua lại!";
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            }
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $action = $_POST['action'] ?? 'add';

        if ($productId <= 0 || $quantity <= 0) {
            $_SESSION['flash_message'] = "Dữ liệu không hợp lệ!";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Kiểm tra sản phẩm tồn tại
        $productModel = $this->model('Product');
        $product = $productModel->findById($productId);
        if (!$product) {
            $_SESSION['flash_message'] = "Sản phẩm không tồn tại!";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Kiểm tra tồn kho
        if ($quantity > $product['stock']) {
            $_SESSION['flash_message'] = "Không đủ hàng trong kho!";
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // --- TRƯỜNG HỢP 1: MUA NGAY (Chuyển đến checkout) ---
        if ($action === 'buy_now') {
            // Xóa session mua ngay cũ
            if (isset($_SESSION['buy_now_temp'])) {
                unset($_SESSION['buy_now_temp']);
            }

            // Tạo session mới chỉ chứa sản phẩm này
            $_SESSION['buy_now_temp'] = [
                $productId => $quantity
            ];

            // Chuyển hướng sang Checkout
            header("Location: /checkout");
            exit;
        }

        // --- TRƯỜNG HỢP 2: THÊM VÀO GIỎ HÀNG ---
        if (isset($_SESSION['user'])) {
            // Kiểm tra user có tồn tại trong DB không
            $userModel = $this->model('User');
            $userExists = $userModel->findById($_SESSION['user']['id']);
            
            if (!$userExists) {
                // User không tồn tại, xóa session và chuyển về trang login
                session_destroy();
                $_SESSION['flash_message'] = "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!";
                header('Location: /auth/login');
                exit;
            }
            
            // User đã đăng nhập - lưu vào DB
            $cartModel = $this->model('Cart');
            $cartModel->addToUserCart($_SESSION['user']['id'], $productId, $quantity);
        } else {
            // Khách vãng lai - lưu vào session
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
        }

        $_SESSION['flash_message'] = "Đã thêm " . $product['name'] . " vào giỏ hàng!";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // 2.5. THÊM VÀO GIỎ HÀNG (AJAX)
    public function add_ajax() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ!']);
            exit;
        }

        // Kiểm tra sản phẩm tồn tại
        $productModel = $this->model('Product');
        $product = $productModel->findById($productId);
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tồn tại!']);
            exit;
        }

        // Kiểm tra tồn kho
        if ($quantity > $product['stock']) {
            echo json_encode(['status' => 'error', 'message' => 'Không đủ hàng trong kho!']);
            exit;
        }

        // Thêm vào giỏ hàng
        if (isset($_SESSION['user'])) {
            // Kiểm tra user có tồn tại trong DB không
            $userModel = $this->model('User');
            $userExists = $userModel->findById($_SESSION['user']['id']);
            
            if (!$userExists) {
                echo json_encode(['status' => 'error', 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!', 'redirect' => '/auth/login']);
                exit;
            }
            
            // User đã đăng nhập - lưu vào DB
            $cartModel = $this->model('Cart');
            $cartModel->addToUserCart($_SESSION['user']['id'], $productId, $quantity);
        } else {
            // Khách vãng lai - lưu vào session
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
        }

        echo json_encode([
            'status' => 'success', 
            'message' => "Đã thêm " . $product['name'] . " vào giỏ hàng!",
            'product_name' => $product['name']
        ]);
        exit;
    }

    // 3. CẬP NHẬT SỐ LƯỢNG (AJAX)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? '')) {
                die("Lỗi bảo mật: CSRF Token không hợp lệ! Vui lòng tải lại trang.");
            }
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];

            if ($quantity < 1) $quantity = 1;

            if (isset($_SESSION['user'])) {
                $this->model('Cart')->updateQuantity($_SESSION['user']['id'], $productId, $quantity);
            } else {
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] = $quantity;
                }
            }
            
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['status' => 'success', 'message' => 'Cập nhật giỏ hàng thành công']);
                exit;
            }
        }
        header("Location: /cart");
        exit;
    }

    // 4. XÓA SẢN PHẨM
    public function remove($id) {
        if (isset($_SESSION['user'])) {
            $this->model('Cart')->removeItem($_SESSION['user']['id'], $id);
        } else {
            if (isset($_SESSION['cart'][$id])) {
                unset($_SESSION['cart'][$id]);
            }
        }
        header("Location: /cart"); 
        exit;
    }

    // 5. XÓA TẤT CẢ
    public function clear() {
        if (isset($_SESSION['user'])) {
            $this->model('Cart')->clearCart($_SESSION['user']['id']);
        } else {
            unset($_SESSION['cart']);
        }
        header("Location: /cart");
        exit;
    }

    // 6. MUA LẠI TỪ ĐƠN HÀNG CŨ (Đã khôi phục)
    public function repurchase() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
            if (!\App\Core\Csrf::verify($_POST['csrf_token'] ?? '')) {
                die("Lỗi bảo mật: CSRF Token không hợp lệ! Vui lòng tải lại trang.");
            }
            $orderId = $_POST['order_id'];
            
            // Gọi Model Order để lấy chi tiết đơn hàng cũ
            $orderModel = $this->model('Order');
            $items = $orderModel->getOrderItems($orderId); 

            if ($items) {
                $cartModel = $this->model('Cart');

                foreach ($items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];

                    // Thêm từng sản phẩm vào giỏ hiện tại
                    if (isset($_SESSION['user'])) {
                        $cartModel->addToUserCart($_SESSION['user']['id'], $productId, $quantity);
                    } else {
                        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
                        
                        if (isset($_SESSION['cart'][$productId])) {
                            $_SESSION['cart'][$productId] += $quantity;
                        } else {
                            $_SESSION['cart'][$productId] = $quantity;
                        }
                    }
                }
                
                $_SESSION['flash_message'] = "Đã thêm các sản phẩm vào giỏ hàng!";
                header("Location: /cart");
                exit;
            }
        }
        // Nếu thất bại hoặc không có POST, quay về lịch sử đơn hàng
        header("Location: /profile/history");
        exit;
    }

    // 7. LẤY SỐ LƯỢNG GIỎ HÀNG (AJAX)
    public function get_count() {
        $count = 0;

        if (isset($_SESSION['user'])) {
            // User đã đăng nhập - lấy từ DB
            $cartModel = $this->model('Cart');
            $count = $cartModel->countItems($_SESSION['user']['id']);
        } else {
            // Khách vãng lai - lấy từ session
            $count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        }

        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }
}
