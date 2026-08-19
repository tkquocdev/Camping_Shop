<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Khởi động session ngay đầu tiên để dùng cho Login, Giỏ hàng...
session_start();

// Định nghĩa đường dẫn gốc của dự án để dễ include file
define('ROOT_PATH', dirname(__DIR__));

// Nạp file Autoload của Composer (Giúp tự động load class Class)
require_once ROOT_PATH . '/vendor/autoload.php';

// Load biến môi trường từ file .env (nếu có)
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->safeLoad();

// SET SECURITY HEADERS (PHẢI LÀM SỚM HẾT, TRƯỚC KHI OUTPUT HTML)
\App\Core\Security::setSecureHeaders();

// Sử dụng namespace của App Core
use App\Core\App;
use App\Controllers\Client\HomeController;
use App\Controllers\Client\AuthController;
use App\Utils\Settings;

// Kiểm tra chế độ bảo trì
$maintenanceMode = Settings::get('maintenance_mode', false);
$maintenanceExemptPaths = ['/auth/login', '/auth/logout', '/auth/register', '/auth/forgot-password', '/auth/reset-password'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$shouldBypassMaintenance = false;
foreach ($maintenanceExemptPaths as $exempt) {
    if (stripos($path, $exempt) === 0) {
        $shouldBypassMaintenance = true;
        break;
    }
}

// Allow static assets to load while in maintenance
$staticPrefixes = ['/assets', '/uploads', '/favicon.ico', '/css', '/js'];
foreach ($staticPrefixes as $prefix) {
    if (stripos($path, $prefix) === 0) {
        $shouldBypassMaintenance = true;
        break;
    }
}

if ($maintenanceMode && !$shouldBypassMaintenance && !(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin')) {
    http_response_code(503);
    $message = Settings::get('maintenance_message', 'Hệ thống đang bảo trì. Vui lòng quay lại sau.');
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hệ thống đang bảo trì</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .maintenance-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 60px 40px;
                text-align: center;
                max-width: 600px;
                width: 90%;
            }
            .maintenance-icon {
                font-size: 80px;
                color: #667eea;
                margin-bottom: 20px;
                animation: float 3s ease-in-out infinite;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .maintenance-title {
                font-size: 2.5rem;
                font-weight: 700;
                color: #333;
                margin-bottom: 20px;
            }
            .maintenance-message {
                font-size: 1.1rem;
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            .maintenance-info {
                background: #f0f2f5;
                border-left: 4px solid #667eea;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 30px;
                text-align: left;
                font-size: 0.95rem;
            }
            .maintenance-info strong {
                color: #667eea;
            }
            .maintenance-contact {
                background: #e7f3ff;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .maintenance-contact h6 {
                font-weight: 600;
                color: #0066cc;
                margin-bottom: 12px;
            }
            .contact-item {
                font-size: 0.95rem;
                color: #333;
                margin-bottom: 8px;
            }
            .contact-item i {
                color: #667eea;
                width: 20px;
            }
        </style>
    </head>
    <body>
        <div class="maintenance-container">
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h1 class="maintenance-title">Hệ thống đang bảo trì</h1>
            <p class="maintenance-message"><?= htmlspecialchars($message) ?></p>
            
            <div class="maintenance-info">
                <strong><i class="fas fa-info-circle me-2"></i>Thông tin:</strong>
                <p class="mb-0" style="margin-top: 8px;">Chúng tôi đang nâng cấp và cải thiện dịch vụ để mang lại trải nghiệm tốt nhất cho quý khách. Xin vui lòng quay lại sau ít phút.</p>
            </div>

            <div class="maintenance-contact">
                <h6><i class="fas fa-headset me-2"></i>Cần giúp đỡ?</h6>
                <div class="contact-item">
                    <i class="fas fa-phone"></i> Hotline: <strong>0868.285.284</strong>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i> Email: <strong>support@campingshop.com</strong>
                </div>
            </div>

            <p style="color: #999; font-size: 0.9rem; margin: 0;">
                <i class="fas fa-sync-alt fa-spin me-2"></i>Tự động làm mới trang trong 60 giây...
            </p>
        </div>

        <script>
            // Auto-refresh page every 60 seconds
            setTimeout(function() {
                location.reload();
            }, 60000);
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Khởi tạo ứng dụng
$app = new App();

// Lấy router
$router = $app->router;

// Define routes
$router->get('/', [HomeController::class, 'index']);

// Auth routes
$router->get('/auth/login', [AuthController::class, 'login']);
$router->post('/auth/login', [AuthController::class, 'login']);
$router->get('/auth/register', [AuthController::class, 'register']);
$router->post('/auth/register', [AuthController::class, 'register']);
$router->get('/auth/logout', [AuthController::class, 'logout']);
$router->get('/auth/forgot-password', [AuthController::class, 'forgot']);
$router->post('/auth/forgot-password', [AuthController::class, 'forgot']);
$router->get('/auth/verify-otp', [AuthController::class, 'verify_otp']);
$router->post('/auth/verify-otp', [AuthController::class, 'verify_otp']);
$router->get('/auth/reset-password', [AuthController::class, 'reset_password']);
$router->post('/auth/reset-password', [AuthController::class, 'reset_password']);
$router->get('/auth/google', [AuthController::class, 'google']);
$router->get('/auth/google_callback', [AuthController::class, 'google_callback']);

// Client routes
$router->get('/product', [\App\Controllers\Client\ProductController::class, 'index']);
$router->get('/product/detail/{id}', [\App\Controllers\Client\ProductController::class, 'detail']);
$router->get('/product/search', [\App\Controllers\Client\ProductController::class, 'search']);
$router->get('/coupon', [\App\Controllers\Client\ProductController::class, 'coupons']);
$router->get('/news', [\App\Controllers\Client\NewsController::class, 'index']);
$router->get('/news/detail', [\App\Controllers\Client\NewsController::class, 'detail']);
$router->get('/about', [\App\Controllers\Client\ContactController::class, 'about']);
$router->get('/contact', [\App\Controllers\Client\ContactController::class, 'index']);
$router->post('/contact/sendRequest', [\App\Controllers\Client\ContactController::class, 'sendRequest']);
$router->get('/cart', [\App\Controllers\Client\CartController::class, 'index']);
$router->get('/cart/index', [\App\Controllers\Client\CartController::class, 'index']);
$router->post('/cart/add', [\App\Controllers\Client\CartController::class, 'add']);
$router->post('/cart/add_ajax', [\App\Controllers\Client\CartController::class, 'add_ajax']);
$router->post('/cart/update', [\App\Controllers\Client\CartController::class, 'update']);
$router->get('/cart/get_count', [\App\Controllers\Client\CartController::class, 'get_count']);
$router->get('/checkout', [\App\Controllers\Client\CheckoutController::class, 'index']);
$router->get('/checkout/index', [\App\Controllers\Client\CheckoutController::class, 'index']);
$router->post('/checkout', [\App\Controllers\Client\CheckoutController::class, 'index']);
$router->post('/checkout/submit', [\App\Controllers\Client\CheckoutController::class, 'submit']);
$router->post('/checkout/apply_coupon', [\App\Controllers\Client\CheckoutController::class, 'apply_coupon']);
$router->get('/checkout/success', [\App\Controllers\Client\CheckoutController::class, 'success']);
$router->get('/checkout/invoice', [\App\Controllers\Client\CheckoutController::class, 'invoice']);
$router->get('/checkout/invoice_pdf', [\App\Controllers\Client\CheckoutController::class, 'generateInvoicePDF']);
$router->get('/profile/index', [\App\Controllers\Client\ProfileController::class, 'index']);
$router->get('/profile/addresses', [\App\Controllers\Client\ProfileController::class, 'addresses']);
$router->get('/profile/addresses', [\App\Controllers\Client\ProfileController::class, 'addresses']);
$router->get('/profile/history', [\App\Controllers\Client\ProfileController::class, 'history']);
$router->get('/profile/order_detail', [\App\Controllers\Client\ProfileController::class, 'order_detail']);
$router->get('/profile/cancel_order', [\App\Controllers\Client\ProfileController::class, 'cancel_order']);
$router->get('/profile/notifications', [\App\Controllers\Client\ProfileController::class, 'notifications']);
$router->get('/profile/settings', [\App\Controllers\Client\ProfileController::class, 'settings']);
$router->get('/profile/loyalty', [\App\Controllers\Client\ProfileController::class, 'loyalty']);
$router->get('/profile/coupons', [\App\Controllers\Client\ProfileController::class, 'coupons']);
$router->post('/profile/exchange_reward', [\App\Controllers\Client\ProfileController::class, 'exchange_reward']);
$router->post('/profile/add_address', [\App\Controllers\Client\ProfileController::class, 'add_address']);
$router->post('/profile/update_address', [\App\Controllers\Client\ProfileController::class, 'update_address']);
$router->get('/profile/set_default_address', [\App\Controllers\Client\ProfileController::class, 'set_default_address']);
$router->get('/profile/delete_address', [\App\Controllers\Client\ProfileController::class, 'delete_address']);
$router->get('/profile/get_address', [\App\Controllers\Client\ProfileController::class, 'get_address']);
$router->post('/profile/update_info', [\App\Controllers\Client\ProfileController::class, 'update_info']);
$router->post('/profile/upload_avatar', [\App\Controllers\Client\ProfileController::class, 'upload_avatar']);
$router->post('/profile/change_password', [\App\Controllers\Client\ProfileController::class, 'change_password']);

// Review routes
$router->post('/review/store', [\App\Controllers\Client\ReviewController::class, 'store']);
$router->post('/review/update', [\App\Controllers\Client\ReviewController::class, 'update']);
$router->post('/review/delete', [\App\Controllers\Client\ReviewController::class, 'delete']);

// AJAX routes
$router->get('/notification/ajaxList', [\App\Controllers\Client\NotificationController::class, 'ajaxList']);
$router->post('/notification/ajaxRead', [\App\Controllers\Client\NotificationController::class, 'ajaxRead']);
$router->post('/notification/markAllRead', [\App\Controllers\Client\NotificationController::class, 'mark_all']);

// Chat & Game routes (Client)
$router->post('/chat/sendMessage', [\App\Controllers\Client\ChatController::class, 'sendMessage']);
$router->get('/game/spin', [\App\Controllers\Client\GameController::class, 'spin']);
$router->get('/game/history', [\App\Controllers\Client\GameController::class, 'history']);

// Admin routes (nếu cần)
$router->get('/admin/dashboard', [\App\Controllers\Admin\AdminController::class, 'dashboard']);

// Categories
$router->get('/admin/categories', [\App\Controllers\Admin\CategoriesController::class, 'index']);
$router->get('/admin/categories/edit/{id}', [\App\Controllers\Admin\CategoriesController::class, 'edit']);
$router->post('/admin/categories/store', [\App\Controllers\Admin\CategoriesController::class, 'store']);
$router->post('/admin/categories/update/{id}', [\App\Controllers\Admin\CategoriesController::class, 'update']);
$router->post('/admin/categories/delete', [\App\Controllers\Admin\CategoriesController::class, 'delete']);

// Products
$router->get('/admin/products', [\App\Controllers\Admin\ProductsController::class, 'index']);
$router->post('/admin/products/store', [\App\Controllers\Admin\ProductsController::class, 'store']);
$router->post('/admin/products/update', [\App\Controllers\Admin\ProductsController::class, 'update']);
$router->get('/admin/products/delete/{id}', [\App\Controllers\Admin\ProductsController::class, 'delete']);

// Coupons
$router->get('/admin/coupons', [\App\Controllers\Admin\CouponsController::class, 'index']);
$router->get('/admin/coupons/create', [\App\Controllers\Admin\CouponsController::class, 'create']);
$router->post('/admin/coupons/store', [\App\Controllers\Admin\CouponsController::class, 'store']);
$router->get('/admin/coupons/edit', [\App\Controllers\Admin\CouponsController::class, 'edit']);
$router->post('/admin/coupons/update', [\App\Controllers\Admin\CouponsController::class, 'update']);
$router->post('/admin/coupons/delete', [\App\Controllers\Admin\CouponsController::class, 'delete']);

// Stock Import
$router->get('/admin/stock', [\App\Controllers\Admin\StockController::class, 'index']);
$router->get('/admin/stock/create', [\App\Controllers\Admin\StockController::class, 'create']);
$router->post('/admin/stock/store', [\App\Controllers\Admin\StockController::class, 'store']);
$router->get('/admin/stock/print/{id}', [\App\Controllers\Admin\StockController::class, 'print']);
$router->get('/admin/stock/delete/{id}', [\App\Controllers\Admin\StockController::class, 'delete']);

// Stock Issue
$router->get('/admin/StockIssue', [\App\Controllers\Admin\StockIssueController::class, 'index']);
$router->get('/admin/StockIssue/create', [\App\Controllers\Admin\StockIssueController::class, 'create']);
$router->post('/admin/StockIssue/store', [\App\Controllers\Admin\StockIssueController::class, 'store']);
$router->get('/admin/StockIssue/print/{id}', [\App\Controllers\Admin\StockIssueController::class, 'print']);

// Orders
$router->get('/admin/orders', [\App\Controllers\Admin\OrdersController::class, 'index']);
$router->get('/admin/orders/detail/{id}', [\App\Controllers\Admin\OrdersController::class, 'detail']);
$router->post('/admin/orders/update/{id}', [\App\Controllers\Admin\OrdersController::class, 'update']);
$router->get('/admin/orders/delete/{id}', [\App\Controllers\Admin\OrdersController::class, 'delete']);
$router->get('/admin/orders/export_invoice/{id}', [\App\Controllers\Admin\OrdersController::class, 'export_invoice']);
$router->get('/admin/orders/returns', [\App\Controllers\Admin\OrdersController::class, 'returns']);

// Users
$router->get('/admin/users', [\App\Controllers\Admin\UsersController::class, 'index']);
$router->get('/admin/users/detail/{id}', [\App\Controllers\Admin\UsersController::class, 'detail']);
$router->get('/admin/users/edit/{id}', [\App\Controllers\Admin\UsersController::class, 'edit']);
$router->post('/admin/users/edit/{id}', [\App\Controllers\Admin\UsersController::class, 'edit']);
$router->get('/admin/users/delete/{id}', [\App\Controllers\Admin\UsersController::class, 'delete']);
$router->post('/admin/users/delete/{id}', [\App\Controllers\Admin\UsersController::class, 'delete']);
$router->get('/admin/users/ban/{id}', [\App\Controllers\Admin\UsersController::class, 'ban']);
$router->get('/admin/users/unban/{id}', [\App\Controllers\Admin\UsersController::class, 'unban']);

// Reviews
$router->get('/admin/reviews', [\App\Controllers\Admin\ReviewsController::class, 'index']);
$router->get('/admin/reviews/detail/{id}', [\App\Controllers\Admin\ReviewsController::class, 'detail']);
$router->post('/admin/reviews/delete/{id}', [\App\Controllers\Admin\ReviewsController::class, 'delete']);

// Notification
$router->get('/admin/notifications', [\App\Controllers\Admin\NotificationController::class, 'index']);
$router->get('/admin/notifications/create', [\App\Controllers\Admin\NotificationController::class, 'create']);
$router->post('/admin/notifications/create', [\App\Controllers\Admin\NotificationController::class, 'create']);
$router->get('/admin/notifications/delete/{id}', [\App\Controllers\Admin\NotificationController::class, 'delete']);
$router->post('/admin/notifications/delete/{id}', [\App\Controllers\Admin\NotificationController::class, 'delete']);

// News
$router->get('/admin/news', [\App\Controllers\Admin\NewsController::class, 'index']);
$router->get('/admin/news/create', [\App\Controllers\Admin\NewsController::class, 'create']);
$router->post('/admin/news/store', [\App\Controllers\Admin\NewsController::class, 'store']);
$router->get('/admin/news/edit', [\App\Controllers\Admin\NewsController::class, 'edit']);
$router->post('/admin/news/update', [\App\Controllers\Admin\NewsController::class, 'update']);
$router->post('/admin/news/delete', [\App\Controllers\Admin\NewsController::class, 'delete']);

// Suppliers (Quản lý Nhà cung cấp)
$router->get('/admin/suppliers', [\App\Controllers\Admin\SuppliersController::class, 'index']);
$router->get('/admin/suppliers/create', [\App\Controllers\Admin\SuppliersController::class, 'create']);
$router->post('/admin/suppliers/store', [\App\Controllers\Admin\SuppliersController::class, 'store']);
$router->get('/admin/suppliers/edit/{id}', [\App\Controllers\Admin\SuppliersController::class, 'edit']);
$router->post('/admin/suppliers/update/{id}', [\App\Controllers\Admin\SuppliersController::class, 'update']);
$router->get('/admin/suppliers/delete/{id}', [\App\Controllers\Admin\SuppliersController::class, 'delete']);

// Flash Sale (Quản lý Flash Sale)
$router->get('/admin/flash_sale', [\App\Controllers\Admin\FlashSaleController::class, 'index']);
$router->get('/admin/flash_sale/create', [\App\Controllers\Admin\FlashSaleController::class, 'create']);
$router->post('/admin/flash_sale/store', [\App\Controllers\Admin\FlashSaleController::class, 'store']);
$router->get('/admin/flash_sale/items/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'items']);
$router->get('/admin/flash_sale/edit/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'edit']);
$router->post('/admin/flash_sale/update/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'update']);
$router->post('/admin/flash_sale/delete/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'delete']);
$router->get('/admin/flash_sale/delete/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'delete']);
$router->get('/admin/flash_sale/get_products', [\App\Controllers\Admin\FlashSaleController::class, 'get_products']);
$router->get('/admin/flash_sale/get_categories', [\App\Controllers\Admin\FlashSaleController::class, 'get_categories']);
$router->post('/admin/flash_sale/add_item', [\App\Controllers\Admin\FlashSaleController::class, 'addItem']);
$router->post('/admin/flash_sale/remove_item', [\App\Controllers\Admin\FlashSaleController::class, 'removeItem']);

// Flash Sale Alias Routes (support flashsale without underscore)
$router->get('/admin/flashsale', [\App\Controllers\Admin\FlashSaleController::class, 'index']);
$router->get('/admin/flashsale/create', [\App\Controllers\Admin\FlashSaleController::class, 'create']);
$router->post('/admin/flashsale/store', [\App\Controllers\Admin\FlashSaleController::class, 'store']);
$router->get('/admin/flashsale/items/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'items']);
$router->get('/admin/flashsale/edit/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'edit']);
$router->post('/admin/flashsale/update/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'update']);
$router->post('/admin/flashsale/delete/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'delete']);
$router->get('/admin/flashsale/delete/{id}', [\App\Controllers\Admin\FlashSaleController::class, 'delete']);
$router->get('/admin/flashsale/get_products', [\App\Controllers\Admin\FlashSaleController::class, 'get_products']);
$router->get('/admin/flashsale/get_categories', [\App\Controllers\Admin\FlashSaleController::class, 'get_categories']);
$router->post('/admin/flashsale/add_item', [\App\Controllers\Admin\FlashSaleController::class, 'addItem']);
$router->post('/admin/flashsale/remove_item', [\App\Controllers\Admin\FlashSaleController::class, 'removeItem']);

// Loyalty (Quản lý Đổi thưởng)
$router->get('/admin/loyalty', [\App\Controllers\Admin\LoyaltyController::class, 'index']);
$router->post('/admin/loyalty/store', [\App\Controllers\Admin\LoyaltyController::class, 'store']);
$router->post('/admin/loyalty/update', [\App\Controllers\Admin\LoyaltyController::class, 'update']);
$router->post('/admin/loyalty/delete', [\App\Controllers\Admin\LoyaltyController::class, 'delete']);

// Game & Customer Care
$router->get('/admin/game', [\App\Controllers\Admin\GameController::class, 'index']);
$router->post('/admin/game/store', [\App\Controllers\Admin\GameController::class, 'store']);
$router->post('/admin/game/update', [\App\Controllers\Admin\GameController::class, 'update']);
$router->post('/admin/game/delete', [\App\Controllers\Admin\GameController::class, 'delete']);
$router->get('/admin/customercare', [\App\Controllers\Admin\CustomerCareController::class, 'index']);
$router->get('/admin/customercare/customer/{id}', [\App\Controllers\Admin\CustomerCareController::class, 'customer']);
$router->post('/admin/customercare/ticket/{id}', [\App\Controllers\Admin\CustomerCareController::class, 'updateTicket']);
$router->post('/admin/customercare/store/{customerId}', [\App\Controllers\Admin\CustomerCareController::class, 'store']);
$router->post('/admin/customercare/info/{customerId}', [\App\Controllers\Admin\CustomerCareController::class, 'update_info']);
$router->post('/admin/customercare/delete', [\App\Controllers\Admin\CustomerCareController::class, 'delete']);
$router->get('/admin/settings', [\App\Controllers\Admin\SettingsController::class, 'index']);
$router->post('/admin/settings', [\App\Controllers\Admin\SettingsController::class, 'save']);

// Run the application
try {
    $app->run();
} catch (Exception $e) {
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        // Return JSON error for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
    } else {
        // Show error page for regular requests
        echo "App error: " . $e->getMessage();
    }
}

// // Sử dụng namespace của App Core
// use App\Core\App;

// // Khởi tạo ứng dụng bình thường (cho các trang khác)
// $app = new App();