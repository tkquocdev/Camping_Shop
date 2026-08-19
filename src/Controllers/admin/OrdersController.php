<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrdersController extends Controller {

    public function __construct() {
        // Kiểm tra quyền Admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login"); exit;
        }
    }

    // 1. QUẢN LÝ ĐƠN HÀNG CHUNG

    // Hiển thị danh sách tất cả đơn hàng
    public function index() {
        $orderModel = $this->model('Order');
        $orders = $orderModel->getAllOrders();

        $this->viewAdmin('admin/orders/index', [
            'orders' => $orders,
            'active' => 'orders'
        ]);
    }

    // Xem chi tiết một đơn hàng
    public function detail($id) {
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);
        $items = $orderModel->getOrderItems($id);

        if (!$order) {
            $_SESSION['flash_error'] = "Đơn hàng không tồn tại.";
            header("Location: /admin/orders"); exit;
        }

        $this->viewAdmin('admin/orders/detail', [
            'order' => $order,
            'items' => $items,
            'active' => 'orders'
        ]);
    }

    // Cập nhật trạng thái đơn hàng
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/orders"); exit;
        }

        $status = $_POST['status'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (empty($status)) {
            $errorMsg = "Vui lòng chọn trạng thái.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $_SESSION['flash_error'] = $errorMsg;
            header("Location: /admin/orders"); exit;
        }

        $orderModel = $this->model('Order');
        $currentOrder = $orderModel->getOrderById($id);

        if (!$currentOrder) {
            $errorMsg = "Đơn hàng không tồn tại.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $_SESSION['flash_error'] = $errorMsg;
            header("Location: /admin/orders"); exit;
        }

        // Cập nhật trạng thái
        if ($orderModel->updateStatus($id, $status)) {
            // Logic tích điểm như trong update_status method
            if ($status === 'completed' && $currentOrder['status'] !== 'completed') {
                if ($currentOrder && $currentOrder['user_id']) {
                    // Lấy giá gốc sản phẩm (không tính discount/shipping)
                    $originalSubtotal = $orderModel->getOrderItemSubtotal($id);
                    $pointsEarned = floor($originalSubtotal / 10000);
                    if ($pointsEarned > 0) {
                        $this->model('LoyaltyModel')->addPoints(
                            $currentOrder['user_id'], 
                            $pointsEarned, 
                            'purchase',
                            "Thưởng đơn hàng #$id"
                        );
                    }
                }
            }

            $successMsg = "Cập nhật trạng thái đơn #$id thành công.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $successMsg]);
                exit;
            }
            $_SESSION['flash_message'] = $successMsg;
        } else {
            $errorMsg = "Lỗi cập nhật trạng thái.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $_SESSION['flash_error'] = $errorMsg;
        }

        header("Location: /admin/orders"); exit;
    }

    // Xóa đơn hàng
    public function delete($id) {
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);

        if (!$order) {
            $_SESSION['flash_error'] = "Đơn hàng không tồn tại.";
            header("Location: /admin/orders"); exit;
        }

        // Chỉ cho phép xóa đơn hàng ở trạng thái pending hoặc cancelled
        if (!in_array($order['status'], ['pending', 'cancelled'])) {
            $_SESSION['flash_error'] = "Không thể xóa đơn hàng đã xử lý.";
            header("Location: /admin/orders"); exit;
        }

        // Xóa order items trước
        $orderModel->db->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);

        // Xóa order
        if ($orderModel->db->prepare("DELETE FROM orders WHERE id = ?")->execute([$id])) {
            $_SESSION['flash_message'] = "Xóa đơn hàng #$id thành công.";
        } else {
            $_SESSION['flash_error'] = "Lỗi xóa đơn hàng.";
        }

        header("Location: /admin/orders"); exit;
    }

    // Cập nhật trạng thái & Tích điểm
    public function update_status() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $orderId = $_POST['order_id'];
            $newStatus = $_POST['status'];

            $orderModel = $this->model('Order');
            
            // 1. Lấy thông tin đơn hàng TRƯỚC khi cập nhật
            // (Để lấy user_id, tổng tiền và trạng thái cũ)
            $currentOrder = $orderModel->getOrderById($orderId);

            // 2. Thực hiện cập nhật trạng thái
            if ($orderModel->updateStatus($orderId, $newStatus)) {
                
                // --- LOGIC TÍCH ĐIỂM ---
                // Chỉ chạy khi:
                // a. Trạng thái mới là 'completed'
                // b. Trạng thái cũ KHÁC 'completed' (Tránh cộng điểm nhiều lần nếu bấm nút nhiều lần)
                if ($newStatus === 'completed' && $currentOrder['status'] !== 'completed') {
                    
                    if ($currentOrder && $currentOrder['user_id']) {
                        // Công thức: 10.000 VNĐ = 1 điểm (làm tròn xuống)
                        // Lấy giá gốc sản phẩm (không tính discount/shipping)
                        $originalSubtotal = $orderModel->getOrderItemSubtotal($orderId);
                        $pointsEarned = floor($originalSubtotal / 10000);

                        if ($pointsEarned > 0) {
                            // Gọi LoyaltyModel để cộng điểm và ghi lịch sử
                            $this->model('LoyaltyModel')->addPoints(
                                $currentOrder['user_id'], 
                                $pointsEarned, 
                                'purchase', // Loại giao dịch
                                "Thưởng đơn hàng #$orderId" // Nội dung hiển thị
                            );
                        }
                    }
                }

                $_SESSION['flash_message'] = "Cập nhật trạng thái đơn #$orderId thành công.";
            } else {
                $_SESSION['flash_error'] = "Lỗi cập nhật.";
            }
        }
        
        // Quay lại trang danh sách đơn hàng
        header("Location: /admin/orders");
        exit;
    }

    // Xuất hóa đơn PDF (Admin)
    public function export_invoice($id) {
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);
        $items = $orderModel->getOrderItems($id);
        
        if (!$order) { header("Location: /admin/orders"); exit; }

        // Lấy thông tin khách hàng từ database
        $userModel = $this->model('User');
        $customer = $userModel->findById($order['user_id']);
        $customerName = $customer['full_name'] ?? 'N/A';
        
        // Tạo HTML từ template giống như client
        ob_start();
        include ROOT_PATH . '/views/client/checkout/invoice.php';
        $html = ob_get_clean();

        // Suppress all warnings from dompdf and font libraries during PDF generation
        $oldErrorReporting = error_reporting();
        error_reporting(0);

        // Cấu hình DomPDF
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        @$dompdf->render();

        // Restore error reporting
        error_reporting($oldErrorReporting);
        
        // Xuất file PDF
        $dompdf->stream("Invoice_#{$id}.pdf", ["Attachment" => false]);
    }

    // 2. QUẢN LÝ YÊU CẦU TRẢ HÀNG

    // Hiển thị danh sách các đơn đang yêu cầu trả hàng
    public function returns() {
        $customerCareModel = $this->model('CustomerCare');
        
        // Lấy tất cả yêu cầu trả hàng từ customer_care_logs
        $requests = $customerCareModel->getReturnRequests();

        $this->viewAdmin('admin/orders/returns', [
            'requests' => $requests,
            'active'   => 'returns'
        ]);
    }

    // 1. DUYỆT YÊU CẦU (Đồng ý nhận lại hàng)
    public function approveReturn($id) {
        $orderModel = $this->model('Order');
        $productModel = $this->model('Product'); 

        // 1. Lấy danh sách sản phẩm trong đơn hàng này
        $items = $orderModel->getOrderItems($id);

        // 2. Cộng lại số lượng tồn kho cho từng sản phẩm
        foreach ($items as $item) {
            $productModel->increaseStock($item['product_id'], $item['quantity']);
        }

        // 3. Cập nhật trạng thái đơn thành 'returned'
        $orderModel->updateStatus($id, 'returned');
        
        // Lưu ý: Nếu muốn TRỪ ĐIỂM khi khách trả hàng, LoyaltyModel ở đây với số âm
        // Ví dụ: $this->model('LoyaltyModel')->addPoints($userId, -$points, 'return', 'Thu hồi điểm đơn #$id');

        $_SESSION['flash_message'] = "Đã duyệt trả hàng và hoàn nhập kho thành công.";
        header('Location: /admin/orders/returns');
        exit;
    }

    // 2. TỪ CHỐI YÊU CẦU (Không đồng ý)
    public function rejectReturn($id) {
        if (empty($id)) {
            header('Location: /admin/orders/returns'); exit;
        }

        $orderModel = $this->model('Order');
        
        // Cập nhật trạng thái quay lại 'completed'
        // LƯU Ý: Vì hàm update_status ở trên có logic cộng điểm khi chuyển sang 'completed',
        // nên nếu bạn dùng hàm đó ở đây, điểm có thể bị cộng lại lần nữa.
        // Tuy nhiên, ở đây ta gọi trực tiếp model->updateStatus nên logic controller ở trên KHÔNG chạy -> AN TOÀN.
        $result = $orderModel->updateStatus($id, 'completed'); 

        if ($result) {
            $_SESSION['flash_error'] = "Đã TỪ CHỐI yêu cầu trả hàng đơn #$id. Đơn hàng trở về trạng thái Hoàn thành.";
        } else {
            $_SESSION['flash_error'] = "Lỗi hệ thống.";
        }

        header('Location: /admin/orders/returns');
        exit;
    }

    // Cập nhật trạng thái yêu cầu trả hàng từ contact
    public function updateReturnStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/orders/returns'); exit;
        }

        $status = $_POST['status'] ?? '';
        $note = trim($_POST['note'] ?? '');

        if (!in_array($status, ['Resolved', 'Rejected'])) {
            $_SESSION['flash_error'] = "Trạng thái không hợp lệ.";
            header('Location: /admin/orders/returns'); exit;
        }

        $customerCareModel = $this->model('CustomerCare');
        $staffId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $result = $customerCareModel->updateTicketStatus((int)$id, $status, $staffId, $note);

        if ($result) {
            $message = $status === 'Resolved' ? "Đã xử lý yêu cầu #$id thành công." : "Đã từ chối yêu cầu #$id.";
            $_SESSION['flash_message'] = $message;
        } else {
            $_SESSION['flash_error'] = "Lỗi cập nhật trạng thái.";
        }

        header('Location: /admin/orders/returns');
        exit;
    }
}