<?php
namespace App\Controllers\Client;

use App\Core\Controller;

class GameController extends Controller {

    /**
     * 1. Xử lý khi truy cập trực tiếp /game
     * Vì đã chuyển sang Widget popup, ta không cần trang riêng nữa.
     * Redirect về trang chủ để người dùng thấy Widget.
     */
    public function index() {
        header("Location: /"); 
        exit;
    }

    /**
     * 2. API Xử lý quay thưởng (Dành cho AJAX gọi)
     */
    public function spin() {
        // Luôn trả về JSON
        header('Content-Type: application/json');

        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => false, 'msg' => 'Vui lòng đăng nhập để quay thưởng!']);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $spinModel = $this->model('LuckySpin');

        // 2. Kiểm tra lượt chơi hôm nay
        if ($spinModel->checkPlayedToday($userId)) {
            echo json_encode(['status' => false, 'msg' => 'Bạn đã hết lượt quay hôm nay. Quay lại vào ngày mai nhé!']);
            exit;
        }

        // 3. Lấy danh sách quà & Thuật toán Random
        $prizes = $spinModel->getAllPrizes();
        
        // --- Logic Random theo trọng số (%) ---
        $rand = rand(1, 100);
        $cumulative = 0;
        $winner = null;
        $winnerIndex = 0;

        foreach ($prizes as $index => $prize) {
            $cumulative += $prize['percent'];
            if ($rand <= $cumulative) {
                $winner = $prize;
                $winnerIndex = $index;
                break;
            }
        }

        // Fallback an toàn: Nếu config sai % thì lấy giải cuối cùng (thường là Chúc may mắn)
        if (!$winner) {
            $winner = end($prizes);
            $winnerIndex = count($prizes) - 1;
        }

        // 4. Lưu lịch sử & Trừ lượt (Save DB)
        $saved = $spinModel->saveHistory($userId, $winner);
        
        if (!$saved) {
            echo json_encode(['status' => false, 'msg' => 'Lỗi hệ thống, vui lòng thử lại sau!']);
            exit;
        }

        // 5. Trả kết quả thành công
        echo json_encode([
            'status' => true,
            'data' => [
                'index' => $winnerIndex,       // Để JS quay kim đến đúng ô
                'name'  => $winner['name'],    // Tên hiển thị
                'code'  => $winner['coupon_code'] ?? null // Mã code nếu có
            ]
        ]);
    }

    /**
     * 3. API Lấy lịch sử quay thưởng của user
     */
    public function history() {
        header('Content-Type: application/json');

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => false, 'msg' => 'Vui lòng đăng nhập!']);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $spinModel = $this->model('LuckySpin');
        
        // Lấy lịch sử
        $history = $spinModel->getHistory($userId);
        
        echo json_encode([
            'status' => true,
            'data' => $history
        ]);
    }

    /**
     * 4. HELPER: Lấy dữ liệu cho Widget
     * Hàm này được gọi trực tiếp từ View (views/layouts/game_widget.php)
     * Mục đích: Để Widget có data hiển thị ở bất kỳ trang nào mà không cần truyền từ mọi Controller.
     */
    public function getWidgetData() {
        $spinModel = $this->model('LuckySpin');
        
        // Lấy danh sách giải thưởng để vẽ vòng quay
        $prizes = $spinModel->getAllPrizes();
        
        // Kiểm tra user đã chơi chưa (Nếu chưa login thì coi như chưa chơi -> nhưng sẽ chặn lúc bấm quay)
        $hasPlayed = false;
        if (isset($_SESSION['user'])) {
            $hasPlayed = $spinModel->checkPlayedToday($_SESSION['user']['id']);
        }

        return [
            'prizes' => $prizes,
            'has_played' => $hasPlayed
        ];
    }
}