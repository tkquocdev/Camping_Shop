<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Models\LoyaltyModel;

class LoyaltyController extends Controller {

    private $loyaltyModel;

    public function __construct() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header("Location: /auth/login");
            exit;
        }

        // 2. Khởi tạo Model 1 lần dùng chung
        $this->loyaltyModel = new LoyaltyModel();
    }

    // Trang chủ Loyalty: /profile/loyalty
    public function index() {
        $userId = $_SESSION['user']['id'];
        
        // Lấy dữ liệu
        $data = [
            'points'  => $this->loyaltyModel->getUserPoints($userId),
            'history' => $this->loyaltyModel->getHistory($userId),
            'rewards' => $this->loyaltyModel->getActiveRewards()
        ];

        $this->view('client/profile/loyalty', $data);
    }

    // Xử lý đổi quà: POST /client/loyalty/exchange
    public function exchange() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rewardId = $_POST['reward_id'] ?? null;
            $userId   = $_SESSION['user']['id'];

            if (!$rewardId) {
                $_SESSION['error'] = "Vui lòng chọn quà cần đổi.";
                header("Location: /profile/loyalty");
                exit;
            }

            // Gọi Model xử lý
            $result = $this->loyaltyModel->redeemReward($userId, $rewardId);

            if ($result['status']) {
                // Thành công: Thông báo + Mã Voucher
                $_SESSION['flash_message'] = "Đổi quà thành công! Mã Voucher: <b>" . $result['code'] . "</b>";
            } else {
                // Thất bại: Thông báo lỗi
                $_SESSION['error'] = $result['msg'];
            }

            // Redirect về lại trang Profile Loyalty
            header("Location: /profile/loyalty");
            exit;
        }
        
        // Nếu truy cập trực tiếp bằng GET thì đẩy về trang danh sách
        header("Location: /profile/loyalty");
    }
}
