<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Security;
use App\Config\Database;
use App\Utils\MailHelper;
use Dompdf\Dompdf;

class CheckoutController extends Controller {

    // --- 1. TRANG THANH TOÁN ---
    public function index() {
        if (!isset($_SESSION['user'])) { 
            $_SESSION['flash_error'] = "Vui lòng đăng nhập để thanh toán!";
            header("Location: /auth/login"); 
            exit; 
        }

        // HỨNG DỮ LIỆU TỪ GIỎ HÀNG (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Protection for cart checkout form
            if (!Security::verifyCSRFToken($_POST[Security::TOKEN_FIELD] ?? null)) {
                $_SESSION['flash_error'] = "Lỗi bảo mật. Vui lòng thử lại.";
                header("Location: /cart");
                exit;
            }
            
            if (!empty($_POST['selected_items'])) {
                $_SESSION['checkout_items'] = $_POST['selected_items'];
                $_SESSION['checkout_quantities'] = $_POST['quantities'] ?? [];
                // Xóa session mua ngay cũ để ưu tiên hàng từ giỏ
                if (isset($_SESSION['buy_now_temp'])) unset($_SESSION['buy_now_temp']);
            }
        }

        // XÁC ĐỊNH NGUỒN HÀNG
        $checkoutData = []; 

        // Ưu tiên 1: Mua ngay
        if (isset($_SESSION['buy_now_temp'])) {
            $checkoutData = $_SESSION['buy_now_temp'];
        } 
        // Ưu tiên 2: Từ giỏ hàng
        elseif (isset($_SESSION['checkout_items'])) {
            $selectedIds = $_SESSION['checkout_items'];
            $quantities = $_SESSION['checkout_quantities'] ?? [];

            // Nếu đã đăng nhập -> Lấy từ DB để chính xác
            if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user']['id'];
                $cartModel = $this->model('Cart');
                $dbCartItems = $cartModel->getUserCart($userId); 

                foreach ($dbCartItems as $item) {
                    if (in_array($item['id'], $selectedIds)) {
                        // Ưu tiên lấy quantity từ form POST, nếu không có thì lấy từ DB
                        $qty = isset($quantities[$item['id']]) ? (int)$quantities[$item['id']] : ($item['buy_quantity'] ?? $item['quantity'] ?? 1);
                        $checkoutData[$item['id']] = $qty;
                    }
                }
            } 
            // Fallback: Lấy từ Session (nếu chưa login hoặc lỗi DB)
            elseif (isset($_SESSION['cart'])) {
                foreach ($selectedIds as $id) {
                    if (isset($_SESSION['cart'][$id])) {
                        // Ưu tiên lấy quantity từ form POST, nếu không có thì lấy từ session
                        $qty = isset($quantities[$id]) ? (int)$quantities[$id] : $_SESSION['cart'][$id];
                        $checkoutData[$id] = $qty;
                    }
                }
            }
        }

        // Kiểm tra lại lần cuối
        if (empty($checkoutData)) {
            // $_SESSION['flash_error'] = "Vui lòng chọn sản phẩm để thanh toán!";
            header("Location: /cart/index");
            exit;
        }

        // LẤY DỮ LIỆU HIỂN THỊ
        $addrModel = $this->model('UserAddress');
        $userAddresses = $addrModel->getAllByUserId($_SESSION['user']['id']);

        $productModel = $this->model('Product');
        $products = $productModel->getProductsByIds(array_keys($checkoutData));
        if (isset($products['id'])) { $products = [$products]; }

        // LOGIC TÍNH GIÁ VỚI FLASH SALE
        $fsModel = $this->model('FlashSaleModel');
        $activeSale = $fsModel->getActiveFlashSale();
        $subTotal = 0;
        
        foreach ($products as &$p) {
            if (!isset($p['id'])) continue;
            $rawData = $checkoutData[$p['id']];
            $qty = is_array($rawData) ? ($rawData['buy_quantity'] ?? 1) : $rawData;
            
            // Lưu quantity để hiển thị trong view
            $p['buy_quantity'] = (int)$qty;
            
            // Kiểm tra thứ tự ưu tiên giá:
            // 1. Flash sale (nếu có)
            // 2. Discount price (giá sale thường)
            // 3. Price (giá gốc)
            $currentPrice = $p['price'];
            $p['is_flash_sale'] = false;
            $p['original_price'] = $p['price'];
            $actualRowTotal = 0;
            
            if ($activeSale) {
                $saleItem = $fsModel->checkProductInFlashSale($activeSale['id'], $p['id']);
                if ($saleItem && $saleItem['quantity'] > $saleItem['sold']) {
                    // Tính số lượng còn có thể mua với giá sale
                    $saleQtyAvailable = $saleItem['quantity'] - $saleItem['sold'];
                    $qty_int = (int)$qty;
                    
                    if ($qty_int <= $saleQtyAvailable) {
                        // Toàn bộ với giá sale
                        $currentPrice = $saleItem['sale_price'];
                        $p['is_flash_sale'] = true;
                        $p['sale_info'] = $saleItem;
                        $actualRowTotal = $currentPrice * $qty_int;
                    } else if ($saleQtyAvailable > 0) {
                        // Một phần sale, một phần normal
                        $salePrice = $saleItem['sale_price'];
                        $normalPrice = $p['price'];
                        $normalQty = $qty_int - $saleQtyAvailable;
                        
                        $actualRowTotal = ($salePrice * $saleQtyAvailable) + ($normalPrice * $normalQty);
                        $currentPrice = $actualRowTotal / $qty_int; // Giá trung bình để hiển thị
                        $p['is_flash_sale'] = true;
                        $p['sale_info'] = $saleItem;
                        $p['mixed_price_info'] = [
                            'sale_qty' => $saleQtyAvailable,
                            'sale_price' => $salePrice,
                            'normal_qty' => $normalQty,
                            'normal_price' => $normalPrice
                        ];
                    } else {
                        $actualRowTotal = $p['price'] * (int)$qty;
                    }
                }
            }
            
            if ($actualRowTotal === 0) {
                $actualRowTotal = $currentPrice * (int)$qty;
            }
            
            if (empty($p['discount_price']) || $p['discount_price'] >= $p['price']) {
                $p['original_price'] = $p['price'];
            }
            
            $p['display_price'] = $currentPrice;
            $subTotal += $actualRowTotal;
        }

        // Phí ship & Coupon
        if (!isset($_SESSION['shipping_fee'])) { $_SESSION['shipping_fee'] = 30000; }
        $shippingFee = $_SESSION['shipping_fee'];

        $discountAmount = 0;
        if (isset($_SESSION['coupon'])) {
            $coupon = $_SESSION['coupon'];
            if ($subTotal >= $coupon['min_order_value']) {
                $discountAmount = ($coupon['discount_type'] == 'fixed') ? $coupon['discount_value'] : ($subTotal * $coupon['discount_value'] / 100);
            } else {
                unset($_SESSION['coupon']);
            }
        }

        $totalPrice = max(0, $subTotal + $shippingFee - $discountAmount);
        $_SESSION['temp_view_cart'] = $checkoutData; 

        $this->view('client/checkout/index', [
            'products' => $products,
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'user' => $_SESSION['user'],
            'coupons' => $this->model('Coupon')->getAllActive(),
            'user_addresses' => $userAddresses
        ]);
    }

    // --- 2. XỬ LÝ ĐẶT HÀNG (SUBMIT) ---
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: /"); exit; }

        try {
            // CSRF Protection - Form token
            if (!Security::verifyCSRFToken($_POST[Security::TOKEN_FIELD] ?? null)) {
                $_SESSION['flash_error'] = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
                header("Location: /checkout");
                exit;
            }

            // Kiểm tra lại đăng nhập lần cuối trước khi xử lý đơn hàng
            if (!isset($_SESSION['user'])) {
                $_SESSION['flash_error'] = "Vui lòng đăng nhập để hoàn tất đơn hàng.";
                header("Location: /auth/login");
                exit;
            }

            // A. CHUẨN BỊ DỮ LIỆU
            $checkoutData = [];
            $isBuyNow = false;

            if (isset($_SESSION['buy_now_temp'])) {
                $checkoutData = $_SESSION['buy_now_temp'];
                $isBuyNow = true;
            } elseif (isset($_SESSION['checkout_items'])) {
                $selectedIds = $_SESSION['checkout_items'];
                
                // Lấy lại dữ liệu giỏ hàng mới nhất từ DB
                if (isset($_SESSION['user'])) {
                    $userId = $_SESSION['user']['id'];
                    $cartModel = $this->model('Cart');
                    $dbCartItems = $cartModel->getUserCart($userId);

                    foreach ($dbCartItems as $item) {
                        if (in_array($item['id'], $selectedIds)) {
                            $qty = $item['buy_quantity'] ?? $item['quantity'] ?? 1;
                            $checkoutData[$item['id']] = $qty;
                        }
                    }
                } 
                // Fallback: Lấy từ Session nếu chưa login hoặc lỗi DB
                elseif (isset($_SESSION['cart'])) {
                    foreach ($selectedIds as $id) {
                        if (isset($_SESSION['cart'][$id])) {
                            $checkoutData[$id] = $_SESSION['cart'][$id];
                        }
                    }
                }
            }

            if (empty($checkoutData)) throw new \Exception("Phiên làm việc hết hạn, vui lòng thao tác lại.");

            // B. XỬ LÝ ĐỊA CHỈ
            $selection = $_POST['address_selection'] ?? 'new';
            $finalName = ''; $finalPhone = ''; $finalAddress = '';
            $userId = $_SESSION['user']['id'];

            if ($selection === 'new') {
                $finalName = trim($_POST['new_name']);
                $finalPhone = trim($_POST['new_phone']);
                $finalAddress = trim($_POST['new_address']);
                
                if (empty($finalName) || empty($finalPhone) || empty($finalAddress)) {
                    throw new \Exception("Vui lòng nhập đầy đủ tên, số điện thoại và địa chỉ.");
                }
                

                $shouldSaveAddress = isset($_POST['save_address']) && !empty($_POST['save_address']);
                
                if ($shouldSaveAddress) {
                    try {
                        $addressModel = $this->model('UserAddress');
                        // Check if this is the first address (set as default)
                        $existingAddresses = $addressModel->getAllByUserId($userId);
                        $isDefault = empty($existingAddresses) ? true : false;
                        
                        $result = $addressModel->create($userId, $finalName, $finalPhone, $finalAddress, $isDefault);
                        if ($result) {
                            $_SESSION['flash_message'] = "Địa chỉ đã được lưu thành công.";
                        }
                        // Nếu lưu không được, không dừng quy trình đặt hàng, chỉ ghi log
                        if (!$result) {
                            error_log("Failed to save address for user $userId: create returned false");
                        }
                    } catch (\Exception $e) {
                        error_log("Error saving address: " . $e->getMessage());
                        // Không dừng quy trình
                    }
                }
            } else {
                $addr = $this->model('UserAddress')->getById($selection);
                if (!$addr) throw new \Exception("Địa chỉ không tồn tại.");
                $finalName = $addr['recipient_name'];
                $finalPhone = $addr['phone'];
                $finalAddress = $addr['address'];
            }
            $fullAddressInfo = "$finalAddress (Người nhận: $finalName)";

            // C. TÍNH TOÁN LẠI (ĐÃ CÓ FLASH SALE)
            $productModel = $this->model('Product');
            $products = $productModel->getProductsByIds(array_keys($checkoutData));
            if (isset($products['id'])) { $products = [$products]; }

            // Kiểm tra flash sale cho tính toán lại
            $fsModel = $this->model('FlashSaleModel');
            $activeSale = $fsModel->getActiveFlashSale();
            $subTotal = 0;
            $itemsProcessed = []; 
            $flashSaleData = []; // Lưu info flash sale để update sold quantity

            foreach ($products as &$p) {
                if(!isset($p['id'])) continue;
                $rawData = $checkoutData[$p['id']];
                $qty = (int)(is_array($rawData) ? ($rawData['buy_quantity'] ?? 1) : $rawData);

                if ($p['stock'] < $qty) throw new \Exception("Sản phẩm '{$p['name']}' không đủ hàng.");
                
                // Kiểm tra flash sale
                $currentPrice = $p['price'];
                $saleItemId = null;
                $actualSubtotal = 0; // Tính từng phần để chuẩn xác
                
                if ($activeSale) {
                    $saleItem = $fsModel->checkProductInFlashSale($activeSale['id'], $p['id']);
                    if ($saleItem && $saleItem['quantity'] > $saleItem['sold']) {
                        // Tính số lượng có thể mua với giá sale
                        $saleQtyAvailable = $saleItem['quantity'] - $saleItem['sold'];
                        
                        if ($qty <= $saleQtyAvailable) {
                            // Mua toàn bộ với giá sale
                            $currentPrice = $saleItem['sale_price'];
                            $saleItemId = $saleItem['id'];
                            $actualSubtotal = $saleItem['sale_price'] * $qty;
                            $flashSaleData[$p['id']] = ['item_id' => $saleItemId, 'sold_qty' => $qty];
                        } else if ($saleQtyAvailable > 0) {
                            // Một phần sale, một phần giá thường
                            $saleQtyBought = $saleQtyAvailable;
                            $regularQtyBought = $qty - $saleQtyAvailable;
                            $salePrice = $saleItem['sale_price'];
                            $regularPrice = $p['price'];
                            
                            // Lưu info chi tiết để xử lý khi lưu order item
                            $saleItemId = $saleItem['id'];
                            $flashSaleData[$p['id']] = [
                                'item_id' => $saleItemId,
                                'sale_qty' => $saleQtyBought,
                                'sale_price' => $salePrice,
                                'regular_qty' => $regularQtyBought,
                                'regular_price' => $regularPrice
                            ];
                            // Tính tổng thực tế (không dùng giá trung bình)
                            $actualSubtotal = ($salePrice * $saleQtyBought) + ($regularPrice * $regularQtyBought);
                        }
                    }
                }
                
                if ($actualSubtotal === 0) {
                    $actualSubtotal = $currentPrice * $qty;
                }
                
                $itemsProcessed[$p['id']] = $qty;
                $p['display_price'] = $currentPrice;
                $subTotal += $actualSubtotal;
            }

            $shippingFee = $_SESSION['shipping_fee'] ?? 30000;
            $discountAmount = 0;
            if (isset($_SESSION['coupon'])) {
                $c = $_SESSION['coupon'];
                $discountAmount = ($c['discount_type'] == 'fixed') ? $c['discount_value'] : ($subTotal * $c['discount_value'] / 100);
            }
            $totalAmount = max(0, $subTotal + $shippingFee - $discountAmount);

            // D. TRANSACTION (Lưu đơn hàng - KHÔNG TÍCH ĐIỂM Ở ĐÂY)
            $conn = Database::getConnection();
            $conn->beginTransaction();

            try {
                $orderModel = $this->model('Order');

                // 1. Tạo đơn
                $orderId = $orderModel->createOrder(
                    $userId, $totalAmount, $fullAddressInfo, $finalPhone, 
                    $_POST['note'] ?? '', $_POST['payment_method'] ?? 'cod', 
                    $discountAmount, $shippingFee, $_SESSION['coupon']['code'] ?? null
                );

                // 2. Chi tiết đơn & Trừ kho & Cập nhật flash sale
                foreach ($products as $p) {
                    if(!isset($p['id'])) continue;
                    $qty = $itemsProcessed[$p['id']];
                    
                    // Kiểm tra nếu là mixed price (một phần sale, một phần normal)
                    if (isset($flashSaleData[$p['id']]) && isset($flashSaleData[$p['id']]['sale_qty'])) {
                        $fsData = $flashSaleData[$p['id']];
                        // Tạo hai order item: một cho sale, một cho regular
                        $orderModel->createOrderItem($orderId, $p['id'], $fsData['sale_qty'], $fsData['sale_price']);
                        $orderModel->createOrderItem($orderId, $p['id'], $fsData['regular_qty'], $fsData['regular_price']);
                        
                        // Trừ kho toàn bộ số lượng
                        $stmt = $conn->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid AND stock >= :qty");
                        $stmt->execute([':qty' => $qty, ':pid' => $p['id']]);
                        if ($stmt->rowCount() === 0) throw new \Exception("Sản phẩm {$p['name']} vừa hết hàng.");
                        
                        // Cập nhật flash sale sold quantity
                        $fsModel->updateSoldQuantity($fsData['item_id'], $fsData['sale_qty']);
                    } else {
                        // Trường hợp thông thường (hoặc toàn bộ mua sale)
                        $orderModel->createOrderItem($orderId, $p['id'], $qty, $p['display_price']);
                        
                        // Trừ kho (An toàn)
                        $stmt = $conn->prepare("UPDATE products SET stock = stock - :qty WHERE id = :pid AND stock >= :qty");
                        $stmt->execute([':qty' => $qty, ':pid' => $p['id']]);
                        if ($stmt->rowCount() === 0) throw new \Exception("Sản phẩm {$p['name']} vừa hết hàng.");
                        
                        // Cập nhật flash sale sold quantity nếu có
                        if (isset($flashSaleData[$p['id']]) && isset($flashSaleData[$p['id']]['sold_qty'])) {
                            $fsData = $flashSaleData[$p['id']];
                            $fsModel->updateSoldQuantity($fsData['item_id'], $fsData['sold_qty']);
                        }
                    }
                }

                // 3. Trừ coupon
                if (isset($_SESSION['coupon'])) {
                    $this->model('Coupon')->decrementQuantity($_SESSION['coupon']['code']);
                }

                $conn->commit();

            } catch (\Exception $ex) {
                $conn->rollBack();
                throw $ex;
            }

            // E. GỬI MAIL & DỌN DẸP (Chỉ gửi nếu có email)
            $targetEmail = trim($_POST['order_email'] ?? '');
            
            // Nếu không có email từ form, dùng email từ session (nếu có)
            if (empty($targetEmail) && !empty($_SESSION['user']['email'])) {
                $targetEmail = $_SESSION['user']['email'];
            }
            
            // Chỉ gửi mail nếu có email hợp lệ
            if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                $mailData = [
                    'id' => $orderId,
                    'customer_name' => $finalName,
                    'phone' => $finalPhone,
                    'address' => $fullAddressInfo,
                    'products' => $products,
                    'cart' => $itemsProcessed,
                    'subTotal' => $subTotal,
                    'shipping' => $shippingFee,
                    'discount' => $discountAmount,
                    'total' => $totalAmount,
                    'payment_method' => $_POST['payment_method'] ?? 'cod',
                    'note' => $_POST['note'] ?? ''
                ];
                
                try {
                    $mailHelper = new MailHelper();
                    $mailHelper->sendOrderConfirmation($targetEmail, $mailData);
                } catch (\Exception $e) { }
            }

            // Xóa session & cart DB
            $cartModel = $this->model('Cart');
            if ($isBuyNow) {
                unset($_SESSION['buy_now_temp']);
            } else {
                foreach (array_keys($checkoutData) as $id) {
                    unset($_SESSION['cart'][$id]);
                    if (isset($_SESSION['user'])) {
                        $cartModel->removeItem($_SESSION['user']['id'], $id);
                    }
                }
                unset($_SESSION['checkout_items']);
            }
            unset($_SESSION['coupon'], $_SESSION['shipping_fee'], $_SESSION['temp_view_cart']);
            
            $_SESSION['last_order_id'] = $orderId;
            header("Location: /checkout/success");
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Lỗi đặt hàng: " . $e->getMessage();
            header("Location: /checkout");
            exit;
        }
    }
    
    // --- 3. APPLY COUPON ---
    public function apply_coupon() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /checkout");
            exit;
        }

        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        $couponModel = $this->model('Coupon');
        $coupon = $couponModel->findByCode($code);

        $success = false;
        $message = '';

        // Nếu coupon rỗng -> xoá coupon
        if ($code === '') {
            unset($_SESSION['coupon']);
            $message = 'Đã bỏ mã giảm giá.';
        } elseif ($coupon) {
            // Kiểm tra user có được phép dùng mã này không
            // Với mã công khai: quantity > 0
            // Với mã riêng tư: số lần dùng <= số lần trúng
            if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user']['id'];
                if ($couponModel->canUserUse($userId, $coupon)) {
                    $_SESSION['coupon'] = $coupon;
                    $message = 'Áp dụng mã thành công!';
                    $success = true;
                } else {
                    unset($_SESSION['coupon']);
                    $message = 'Bạn không được phép dùng mã này hoặc đã hết lần sử dụng!';
                }
            } else {
                // Chưa đăng nhập -> cho phép công khai, từ chối riêng tư
                if ($coupon['is_private'] == 0) {
                    $_SESSION['coupon'] = $coupon;
                    $message = 'Áp dụng mã thành công!';
                    $success = true;
                } else {
                    unset($_SESSION['coupon']);
                    $message = 'Vui lòng đăng nhập để dùng mã này!';
                }
            }
        } else {
            unset($_SESSION['coupon']);
            $message = 'Mã không hợp lệ!';
        }

        $totals = $this->calculateCheckoutTotals();

        $response = array_merge($totals, [
            'success' => $success,
            'message' => $message,
            'coupon' => $success ? $coupon : null
        ]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }

    // Helper: tính lại tổng tiền khi thay đổi coupon
    private function calculateCheckoutTotals() {
        $checkoutData = [];

        if (isset($_SESSION['buy_now_temp'])) {
            $checkoutData = $_SESSION['buy_now_temp'];
        } elseif (isset($_SESSION['checkout_items'])) {
            $selectedIds = $_SESSION['checkout_items'];

            if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user']['id'];
                $cartModel = $this->model('Cart');
                $dbCartItems = $cartModel->getUserCart($userId);
                foreach ($dbCartItems as $item) {
                    if (in_array($item['id'], $selectedIds)) {
                        $qty = $item['buy_quantity'] ?? $item['quantity'] ?? 1;
                        $checkoutData[$item['id']] = $qty;
                    }
                }
            } elseif (isset($_SESSION['cart'])) {
                foreach ($selectedIds as $id) {
                    if (isset($_SESSION['cart'][$id])) {
                        $checkoutData[$id] = $_SESSION['cart'][$id];
                    }
                }
            }
        } elseif (isset($_SESSION['cart'])) {
            $checkoutData = $_SESSION['cart'];
        }

        // Tính tổng tiền - SỬ DỤNG DISPLAY PRICE (CÓ FLASH SALE)
        $subTotal = 0;
        if (!empty($checkoutData)) {
            $productModel = $this->model('Product');
            $products = $productModel->getProductsByIds(array_keys($checkoutData));
            if (isset($products['id'])) { $products = [$products]; }

            // Lấy flash sale info
            $fsModel = $this->model('FlashSaleModel');
            $activeSale = $fsModel->getActiveFlashSale();

            foreach ($products as $p) {
                if (!isset($p['id'])) continue;
                $qty = is_array($checkoutData[$p['id']]) ? ($checkoutData[$p['id']]['buy_quantity'] ?? 1) : $checkoutData[$p['id']];
                
                // Kiểm tra flash sale
                $currentPrice = $p['price'];
                if ($activeSale) {
                    $saleItem = $fsModel->checkProductInFlashSale($activeSale['id'], $p['id']);
                    if ($saleItem && $saleItem['quantity'] > $saleItem['sold']) {
                        $currentPrice = $saleItem['sale_price'];
                    }
                } else if (!empty($p['discount_price']) && $p['discount_price'] < $p['price']) {
                    $currentPrice = $p['discount_price'];
                }
                
                $subTotal += $currentPrice * (int)$qty;
            }
        }

        $shippingFee = $_SESSION['shipping_fee'] ?? 30000;
        $discountAmount = 0;

        if (isset($_SESSION['coupon'])) {
            $c = $_SESSION['coupon'];
            if ($subTotal >= $c['min_order_value']) {
                $discountAmount = ($c['discount_type'] == 'fixed')
                    ? $c['discount_value']
                    : ($subTotal * $c['discount_value'] / 100);
            } else {
                unset($_SESSION['coupon']);
            }
        }

        $totalPrice = max(0, $subTotal + $shippingFee - $discountAmount);

        return [
            'sub_total' => $subTotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
            'total_formatted' => number_format($totalPrice, 0, ',', '.') . ' đ',
            'discount_formatted' => number_format($discountAmount, 0, ',', '.') . ' đ',
        ];
    }

    // --- 4. SUCCESS ---
    public function success() {
        $orderId = $_SESSION['last_order_id'] ?? null;
        $this->view('client/checkout/success', ['order_id' => $orderId]);
    }

    // --- 5. INVOICE ---
    public function invoice() {
        $orderId = $_GET['id'] ?? null;
        if (!$orderId) {
            header("Location: /");
            exit;
        }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
            die("Access Denied");
        }

        $items = $orderModel->getOrderItems($orderId);

        $this->view('client/checkout/invoice', [
            'order' => $order,
            'items' => $items
        ]);
    }

    // --- 5. XUẤT PDF HÓA ĐƠN ---
    public function generateInvoicePDF() {
        $orderId = (int)($_GET['id'] ?? 0);
        if (!$orderId) {
            header("Location: /");
            exit;
        }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($orderId);

        if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
            die("Access Denied");
        }

        $items = $orderModel->getOrderItems($orderId);

        // Tạo HTML từ template
        ob_start();
        include ROOT_PATH . '/views/client/checkout/invoice.php';
        $html = ob_get_clean();

        // Tạm thời tắt error reporting để tránh lỗi khi render PDF (nếu có)
        $oldErrorReporting = error_reporting();
        error_reporting(0);

        // Tạo PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        @$dompdf->render();

        // Khôi phục lại mức error reporting cũ
        error_reporting($oldErrorReporting);

        // Xuất PDF
        $dompdf->stream('hoa-don-' . $orderId . '.pdf', array('Attachment' => 0));
        exit;
    }
}
