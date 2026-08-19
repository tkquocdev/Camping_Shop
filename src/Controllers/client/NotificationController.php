<?php
namespace App\Controllers\Client;

use App\Core\Controller; 

class NotificationController extends Controller {

    /**
     * PAGE: Hiển thị trang quản lý thông báo đầy đủ
     * URL: index.php?controller=notification&action=index
     */
    public function index() {
        // 1. Kiểm tra đăng nhập
        if (empty($_SESSION['user']['id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $notiModel = $this->model('Notification');

        // 2. Lấy danh sách (Lấy nhiều hơn dropdown, ví dụ 50 tin)
        $data['notifications'] = $notiModel->getNotifications($userId, 50);
        
        // 3. Render View
        $this->view('client/notifications/index', $data);
    }

    /**
     * AJAX: API trả về JSON cho Dropdown trên Header
     */
    public function ajaxList() {
        $this->cleanBuffer();
        
        $response = ['status' => 'error', 'count' => 0, 'html' => ''];

        try {
            if (empty($_SESSION['user']['id'])) {
                $response['html'] = '<li><div class="text-center text-muted py-3">Vui lòng đăng nhập</div></li>';
                echo json_encode($response); exit;
            }

            $userId = $_SESSION['user']['id'];
            $notiModel = $this->model('Notification');

            $list = $notiModel->getNotifications($userId, 10); // Lấy 10 tin mới nhất
            $unreadCount = $notiModel->countUnread($userId);

            ob_start(); 
            if (!empty($list)) {
                foreach ($list as $item) {
                    $this->renderDropdownItem($item);
                }
                echo '<li><a href="/profile/notifications" class="dropdown-item text-center small text-primary fw-bold bg-light py-2">Xem tất cả</a></li>';
            } else {
                echo '<li><div class="text-center text-muted py-4"><i class="fa-regular fa-bell-slash fa-2x mb-2"></i><br>Không có thông báo mới</div></li>';
            }
            $htmlItems = ob_get_clean();

            $response['status'] = 'ok';
            $response['count']  = $unreadCount;
            $response['html']   = $htmlItems;

        } catch (\Exception $e) {
            $response['html'] = '<li><span class="dropdown-item text-center text-danger">Lỗi: ' . $e->getMessage() . '</span></li>';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * ACTION: Đánh dấu đã đọc 1 tin (AJAX)
     */
    public function mark_read() {
        $this->cleanBuffer();
        $userId = $_SESSION['user']['id'] ?? null;
        $notiId = $_POST['id'] ?? null;

        if ($userId && $notiId) {
            $result = $this->model('Notification')->markAsRead($userId, $notiId);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or Missing ID']);
        }
        exit;
    }

    /**
     * ACTION: Đánh dấu tất cả đã đọc (AJAX)
     */
    public function mark_all() {
        $this->cleanBuffer();
        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId) {
            $this->model('Notification')->markAllRead($userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // Hàm phụ render từng item trong dropdown
    private function renderDropdownItem($item) {
        $isRead = ($item['is_read'] == 1);
        $bgClass = $isRead ? 'bg-white' : 'bg-light';
        $textClass = $isRead ? 'text-secondary' : 'fw-bold text-dark';
        // Link chi tiết có thể tùy theo loại thông báo, tạm để # hoặc có thể truyền thêm field 'link' trong DB để dễ quản lý
        $link = '';
        $time = date('H:i d/m', strtotime($item['created_at']));
        ?>
        <li style="border-bottom: 1px solid #f0f0f0;">
            <a href="#" 
               onclick="handleNotifyClick(event, <?= $item['id'] ?>, '')" 
               class="dropdown-item p-3 <?= $bgClass ?>" style="white-space: normal; cursor: pointer;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small text-primary fw-bold"><i class="fa-solid fa-bell me-1"></i> Hệ thống</span>
                    <small class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($time) ?></small>
                </div>
                <div class="<?= $textClass ?> mb-1" style="font-size: 0.9rem;">
                    <?= htmlspecialchars($item['title']) ?>
                    <?= !$isRead ? '<span class="badge bg-danger rounded-pill ms-1" style="font-size:0.5rem">Mới</span>' : '' ?>
                </div>
            </a>
        </li>
        <?php
    }

    /**
     * AJAX: Đánh dấu đã đọc 1 tin (Dùng cho click vào item trong dropdown, không chuyển trang)
     */
    public function ajaxRead() {
        $this->cleanBuffer();
        $response = ['success' => false];

        try {
            if (empty($_SESSION['user']['id'])) {
                echo json_encode($response);
                exit;
            }

            $userId = $_SESSION['user']['id'];
            $data = json_decode(file_get_contents('php://input'), true);
            $notiId = $data['id'] ?? null;

            if ($notiId) {
                $result = $this->model('Notification')->markAsRead($userId, $notiId);
                $response['success'] = $result;
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }

    private function cleanBuffer() {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
    }
}