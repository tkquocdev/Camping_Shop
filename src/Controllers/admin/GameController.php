<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class GameController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. Hiển thị danh sách phần thưởng
    public function index() {
        $spinModel = $this->model('LuckySpin');
        $prizes = $spinModel->getAllPrizes();

        $this->viewAdmin('admin/game/index', [
            'prizes' => $prizes,
            'active' => 'game'
        ]);
    }

    // 2. Xử lý Thêm mới giải thưởng (Store)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $percent = $_POST['percent'] ?? 0;
            
            if (empty($name)) {
                $_SESSION['error'] = "Tên giải thưởng không được để trống!";
                header("Location: /admin/game"); exit;
            }

            // Chuẩn bị dữ liệu
            $data = [
                'name'        => $name,
                'coupon_code' => empty($_POST['code']) ? null : trim($_POST['code']),
                'percent'     => $percent,
                'color'       => $_POST['color'] ?? '#000000'
            ];

            $spinModel = $this->model('LuckySpin');
            if ($spinModel->create($data)) {
                $_SESSION['flash_message'] = "Đã thêm giải thưởng mới thành công!";
            } else {
                $_SESSION['error'] = "Thêm thất bại, vui lòng thử lại.";
            }

            header("Location: /admin/game");
            exit;
        }
    }

    // 3. Xử lý Cập nhật giải thưởng (Update)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;

            if (!$id) {
                $_SESSION['error'] = "Không tìm thấy ID giải thưởng!";
                header("Location: /admin/game"); exit;
            }

            // Lấy dữ liệu từ Form (bao gồm cả name)
            $couponCode = trim($_POST['code'] ?? '');
            $data = [
                'name'        => $_POST['name'],
                'percent'     => $_POST['percent'] ?? 0,
                'color'       => $_POST['color'] ?? '#000000',
                'coupon_code' => !empty($couponCode) ? $couponCode : null
            ];

            $spinModel = $this->model('LuckySpin');
            
            if ($spinModel->updatePrize($id, $data)) {
                // Auto-create coupon if code provided and doesn't exist
                if (!empty($couponCode)) {
                    $this->ensureCouponExists($couponCode, 10000); // Default 10k value
                }
                $_SESSION['flash_message'] = "Cập nhật giải thưởng thành công!";
            } else {
                $_SESSION['error'] = "Cập nhật thất bại.";
            }

            header("Location: /admin/game");
            exit;
        }
    }

    // Helper: Đảm bảo mã coupon tồn tại, nếu không sẽ tạo mới với giá trị mặc định
    private function ensureCouponExists($code, $value = 10000) {
        try {
            $couponModel = $this->model('Coupon');
            $couponModel->ensureGameCoupon($code, $value);
        } catch (\Exception $e) {
            error_log("Error creating game coupon: " . $e->getMessage());
            // Không cần thiết phải thông báo lỗi này cho admin, vì nó không ảnh hưởng đến việc 
            // cập nhật giải thưởng. Coupon sẽ được tạo tự động khi người chơi quay trúng giải thưởng.
        }
    }

    // 4. Xử lý Xóa giải thưởng (Delete)
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? null;
            
            if ($id) {
                $spinModel = $this->model('LuckySpin');
                if ($spinModel->delete($id)) {
                    $_SESSION['flash_message'] = "Đã xóa giải thưởng.";
                } else {
                    $_SESSION['error'] = "Xóa thất bại.";
                }
            }
            
            header("Location: /admin/game");
            exit;
        }
    }

    // 5. Reset dữ liệu
    public function reset() {
         if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Logic reset lịch sử quay nếu cần
            $_SESSION['flash_message'] = "Chức năng này đang được bảo trì.";
            header("Location: /admin/game");
            exit;
         }
    }
}