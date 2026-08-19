<?php
namespace App\Controllers\Client; // <--- ĐÃ SỬA: Thêm \Client vào namespace

use App\Core\Controller;
use App\Utils\ImageUploader;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProfileController extends Controller {

    // Kiểm tra đăng nhập
    public function __construct() {
        // Kiểm tra session user
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }

    // 1. DASHBOARD
    public function index() {
        $userId = $_SESSION['user']['id'];
        
        // Gọi Model User
        $userModel = $this->model('User');
        $currentUser = $userModel->findById($userId); 

        // Nếu user bị xóa trong DB mà session vẫn còn -> logout
        if (!$currentUser) {
            session_destroy();
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $this->view('client/profile/index', [
            'page_title' => 'Hồ sơ cá nhân',
            'user' => $currentUser
        ]);
    }

    // 2. QUẢN LÝ THÔNG BÁO
    public function notifications() {
        $userId = $_SESSION['user']['id'];
        
        $notiModel = $this->model('Notification');
        // Lấy 50 thông báo gần nhất
        $notifications = $notiModel->getNotifications($userId, 50);

        $this->view('client/profile/notifications', [
            'page_title' => 'Thông báo của tôi',
            'notifications' => $notifications
        ]);
    }

    // 2.5. CÀI ĐẶT
    public function settings() {
        header("Location: /profile/index");
        exit;
    }

    // 3. LỊCH SỬ ĐƠN HÀNG
    public function history() {
        $userId = $_SESSION['user']['id'];
        $orderModel = $this->model('Order');

        $orders = $orderModel->getOrdersByUserId($userId);
        
        // Lấy items cho mỗi đơn hàng để hiển thị trong lịch sử
        foreach ($orders as &$order) {
            $order['items'] = $orderModel->getOrderItems($order['id']);
        }

        $this->view('client/profile/order_history', [
            'page_title' => 'Lịch sử đơn hàng',
            'orders' => $orders
        ]);
    }

    // 4. CHI TIẾT ĐƠN HÀNG
    public function order_detail() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            header("Location: /profile/history");
            exit;
        }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);

        // Bảo mật: Chỉ xem đơn của chính mình
        if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
            $_SESSION['flash_error'] = "Bạn không có quyền xem đơn hàng này!";
            header("Location: /profile/history");
            exit;
        }

        $items = $orderModel->getOrderItems($id);

        $this->view('client/profile/order_detail', [
            'page_title' => 'Chi tiết đơn hàng #' . $id,
            'order' => $order,
            'items' => $items
        ]);
    }

    // 5. XUẤT HÓA ĐƠN PDF
    public function export_invoice() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: /profile/history"); exit; }

        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);
        
        if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
            die("Access Denied");
        }

        $items = $orderModel->getOrderItems($id);

        // Bắt đầu buffer để lấy HTML
        ob_start();
        // Đảm bảo đường dẫn file template đúng
        require_once ROOT_PATH . '/views/client/profile/invoice_template.php';
        $html = ob_get_clean();

        // Tắt báo lỗi của Dompdf (nếu có) để tránh lỗi hiển thị PDF
        $oldErrorReporting = error_reporting();
        error_reporting(0);

        // Cấu hình Dompdf
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans'); 
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Khôi phục lại mức báo lỗi cũ
        error_reporting($oldErrorReporting);
        
        // Xuất file
        $dompdf->stream("Hoa_don_CampingShop_#{$id}.pdf", ["Attachment" => false]);
    }

    // 6. UPLOAD AVATAR
    public function upload_avatar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            try {
                // Sử dụng ImageUploader để xử lý upload và trả về tên file đã lưu
                $uploader = new \App\Utils\ImageUploader(); 
                $fileName = $uploader->upload($_FILES['avatar'], 'users');

                if ($fileName) {
                    $userId = $_SESSION['user']['id'];
                    // Tạo đường dẫn đầy đủ: uploads/users/filename
                    $avatarPath = 'uploads/users/' . $fileName;

                    // Lưu vào database và chỉ báo thành công nếu lưu được
                    $updated = $this->model('User')->updateAvatar($userId, $avatarPath);
                    if ($updated) {
                        // Cập nhật session (Avatar dùng để hiển thị nhanh)
                        $_SESSION['user']['avatar'] = $avatarPath;
                        $_SESSION['flash_message'] = "Đổi ảnh đại diện thành công!";
                    } else {
                        $_SESSION['flash_error'] = "Không thể lưu ảnh vào hồ sơ. Vui lòng thử lại.";
                    }
                } else {
                    $_SESSION['flash_error'] = "Upload thất bại.";
                }

            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Lỗi: " . $e->getMessage();
            }
        } else {
            $_SESSION['flash_error'] = "Vui lòng chọn ảnh để tải lên.";
        }

        header("Location: /profile/index");
        exit;
    }

    // 6.5. CẬP NHẬT THÔNG TIN CÁ NHÂN
    public function update_info() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fullName = trim($_POST['full_name']);
            $phone = trim($_POST['phone']);
            $userId = $_SESSION['user']['id'];

            if (empty($fullName)) {
                $_SESSION['flash_error'] = "Họ và tên không được để trống!";
            } else {
                $userModel = $this->model('User');
                $updated = $userModel->updateProfile($userId, $fullName, $phone);

                if ($updated) {
                    // Cập nhật session
                    $_SESSION['user']['full_name'] = $fullName;
                    $_SESSION['user']['name'] = $fullName;
                    $_SESSION['user']['phone'] = $phone;
                    $_SESSION['flash_message'] = "Cập nhật thông tin thành công!";
                } else {
                    $_SESSION['flash_error'] = "Không thể cập nhật thông tin. Vui lòng thử lại hoặc liên hệ Admin.";
                }
            }
        }
        header("Location: /profile/index");
        exit;
    }

    // 7. ĐỔI MẬT KHẨU
    public function change_password() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $currentPass = $_POST['current_password'];
            $newPass = $_POST['new_password'];
            $confirmPass = $_POST['confirm_password'];
            $userId = $_SESSION['user']['id'];

            $userModel = $this->model('User');
            $user = $userModel->getUserById($userId);

            if (!password_verify($currentPass, $user['password'])) {
                $_SESSION['flash_error'] = "Mật khẩu hiện tại không đúng!";
            } elseif ($newPass !== $confirmPass) {
                $_SESSION['flash_error'] = "Mật khẩu xác nhận không khớp!";
            } elseif (strlen($newPass) < 3) {
                $_SESSION['flash_error'] = "Mật khẩu quá ngắn!";
            } else {
                $userModel->changePassword($userId, $newPass);
                $_SESSION['flash_message'] = "Đổi mật khẩu thành công!";
            }
        }
        header("Location: /profile/index");
        exit;
    }

    // 8. QUẢN LÝ ĐỊA CHỈ (List)
    public function addresses() {
        $addrModel = $this->model('UserAddress');
        $addresses = $addrModel->getAllByUserId($_SESSION['user']['id']);

        $this->view('client/profile/addresses', [
            'page_title' => 'Sổ địa chỉ',
            'addresses' => $addresses
        ]);
    }

    // 9. THÊM ĐỊA CHỈ
    public function add_address() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['recipient_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $addr = trim($_POST['address'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            if (empty($name) || empty($phone) || empty($addr)) {
                $_SESSION['flash_error'] = "Vui lòng điền đầy đủ thông tin địa chỉ!";
            } else {
                $result = $this->model('UserAddress')->create($_SESSION['user']['id'], $name, $phone, $addr, $isDefault);
                if ($result) {
                    $_SESSION['flash_message'] = "Thêm địa chỉ thành công!";
                } else {
                    $_SESSION['flash_error'] = "Lỗi khi thêm địa chỉ. Vui lòng thử lại!";
                }
            }
        }
        header("Location: /profile/addresses");
        exit;
    }

    // 10. SỬA ĐỊA CHỈ
    public function update_address() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['address_id'] ?? null;
            $name = trim($_POST['recipient_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $addr = trim($_POST['address'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            $userId = $_SESSION['user']['id'];

            if (!$id || empty($name) || empty($phone) || empty($addr)) {
                $_SESSION['flash_error'] = "Vui lòng điền đầy đủ thông tin địa chỉ!";
            } else {
                $result = $this->model('UserAddress')->updateAddress($id, $userId, $name, $phone, $addr, $isDefault);
                if ($result) {
                    $_SESSION['flash_message'] = "Cập nhật địa chỉ thành công!";
                } else {
                    $_SESSION['flash_error'] = "Lỗi khi cập nhật địa chỉ. Vui lòng thử lại!";
                }
            }
        }
        header("Location: /profile/addresses");
        exit;
    }

    // 11. SET MẶC ĐỊNH
    public function set_default_address() {
        $id = $_GET['id'] ?? null;
        if($id) {
            $this->model('UserAddress')->setDefault($id, $_SESSION['user']['id']);
        }
        header("Location: /profile/addresses");
        exit;
    }

    // 12. XÓA ĐỊA CHỈ
    public function delete_address() {
        $id = $_GET['id'] ?? null;
        if($id) {
            $this->model('UserAddress')->deleteAddress($id, $_SESSION['user']['id']);
        }
        header("Location: /profile/addresses");
        exit;
    }

    // 12.5. LẤY THÔNG TIN ĐỊA CHỈ (AJAX)
    public function get_address() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
            exit;
        }

        $address = $this->model('UserAddress')->getById($id);
        if (!$address || $address['user_id'] != $_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Address not found']);
            exit;
        }

        echo json_encode(['success' => true, 'address' => $address]);
        exit;
    }

    // 13. HỦY ĐƠN HÀNG
    public function cancel_order() {
        $id = $_GET['id'] ?? null;
        if (!$id) { 
            header("Location: /profile/history"); 
            exit; 
        }

        if ($this->model('Order')->cancelOrder($id, $_SESSION['user']['id'])) {
            $_SESSION['flash_message'] = "Hủy đơn hàng #$id thành công.";
        } else {
            $_SESSION['flash_error'] = "Không thể hủy đơn hàng này.";
        }
        header("Location: /profile/history");
        exit;
    }

    // 16. ĐIỂM THƯỞNG & ĐỔI QUÀ
    public function loyalty() {
        $loyaltyModel = $this->model('LoyaltyModel');
        $userId = $_SESSION['user']['id'];

        // Lấy thông tin điểm của user
        $userPoints = $loyaltyModel->getUserPoints($userId);
        $pointHistory = $loyaltyModel->getHistory($userId, 10); // 10 giao dịch gần nhất
        $availableRewards = $loyaltyModel->getActiveRewards();

        $this->view('client/profile/loyalty', [
            'page_title' => 'Điểm thưởng & Đổi quà',
            'user_points' => $userPoints,
            'history' => $pointHistory,
            'rewards' => $availableRewards
        ]);
    }

    // 17. MÃ GIẢM GIÁ CỦA TÔI
    public function coupons() {
        $couponModel = $this->model('Coupon');
        $userId = $_SESSION['user']['id'];

        // Lấy coupons của user (bao gồm cả private coupons từ lucky spin)
        $userCoupons = $couponModel->getUserCoupons($userId);

        $this->view('client/profile/coupons', [
            'page_title' => 'Mã giảm giá của tôi',
            'coupons' => $userCoupons
        ]);
    }

    // 18. ĐỔI QUÀ (AJAX)
    public function exchange_reward() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $rewardId = (int)($_POST['reward_id'] ?? 0);
        $userId = $_SESSION['user']['id'];

        $loyaltyModel = $this->model('LoyaltyModel');
        $result = $loyaltyModel->exchangeReward($userId, $rewardId);

        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'new_points' => $result['new_points']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
        exit;
    }

    // 14. YÊU CẦU TRẢ HÀNG (Form)
    public function return_order() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header("Location: index.php?controller=profile&action=history"); exit; }

        $order = $this->model('Order')->getOrderById($id);

        if (!$order || $order['user_id'] != $_SESSION['user']['id']) {
            $_SESSION['flash_error'] = "Đơn hàng không hợp lệ.";
            header("Location: index.php?controller=profile&action=history"); 
            exit;
        }

        $this->view('client/profile/return_form', [
            'page_title' => 'Yêu cầu Trả hàng #' . $id,
            'order' => $order
        ]);
    }

    // 15. XỬ LÝ TRẢ HÀNG (Post)
    public function store_return_request() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderId = $_POST['order_id'];
            $reasonKey = $_POST['reason'];
            $description = $_POST['description'];

            $reasons = [
                'damaged' => 'Sản phẩm lỗi',
                'wrong_item' => 'Giao sai hàng',
                'missing' => 'Thiếu hàng',
                'other' => 'Khác'
            ];
            $reasonText = $reasons[$reasonKey] ?? 'Khác';

            $result = $this->model('Order')->requestReturn($orderId, $reasonText, $description);

            if ($result) {
                $_SESSION['flash_message'] = "Gửi yêu cầu thành công!";
            } else {
                $_SESSION['flash_error'] = "Lỗi xử lý yêu cầu.";
            }

            header("Location: index.php?controller=profile&action=history");
            exit;
        }
    }
}