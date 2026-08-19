<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\AIService;

class AdminChatController extends Controller {

    public function sendMessage() {
        // 1. Dọn dẹp buffer để tránh lỗi JSON
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';

            if (empty(trim($message))) {
                echo json_encode(['success' => false, 'reply' => 'Mời sếp hỏi về doanh thu hoặc kho hàng ạ!']);
                return;
            }

            // 2. Load Models
            $orderModel = $this->model('Order');
            $productModel = $this->model('Product');

            // 3. Lấy dữ liệu từ Database (Real-time)
            $finance = $orderModel->getAdminStats();
            $inventory = $productModel->getInventoryStats();

            // 4. Xây dựng CONTEXT (Dữ liệu mớm cho AI)
            // Chúng ta format dữ liệu thành dạng văn bản dễ hiểu cho AI
            $contextData = "";

            // Dữ liệu Tài chính
            if (!empty($finance)) {
                $today = number_format($finance['revenue_today']);
                $month = number_format($finance['revenue_month']);
                $pending = $finance['pending_orders'];
                
                $contextData .= "[BÁO CÁO TÀI CHÍNH]\n";
                $contextData .= "- Doanh thu hôm nay: {$today} VNĐ\n";
                $contextData .= "- Doanh thu tháng này: {$month} VNĐ\n";
                $contextData .= "- Đơn đang chờ duyệt: {$pending} đơn\n";
                
                // Thêm dữ liệu 7 ngày
                if (!empty($finance['chart_data'])) {
                    $contextData .= "- Xu hướng 7 ngày qua: ";
                    foreach ($finance['chart_data'] as $d) {
                        $contextData .= "[{$d['date']}: " . number_format($d['total']) . "] ";
                    }
                    $contextData .= "\n";
                }
            }

            // -- Dữ liệu Kho hàng --
            if (!empty($inventory)) {
                $contextData .= "\n[BÁO CÁO KHO HÀNG]\n";
                $contextData .= "- Tổng số mặt hàng: {$inventory['total_products']}\n";
                
                if (!empty($inventory['low_stock'])) {
                    $contextData .= "- CẢNH BÁO SẮP HẾT: ";
                    foreach ($inventory['low_stock'] as $p) {
                        $contextData .= "{$p['name']} (còn {$p['stock']}), ";
                    }
                    $contextData .= "\n";
                }

                if (!empty($inventory['out_of_stock'])) {
                    $contextData .= "- ĐÃ HẾT HÀNG: ";
                    foreach ($inventory['out_of_stock'] as $p) {
                        $contextData .= "{$p['name']}, ";
                    }
                    $contextData .= "\n";
                }
            }

            // 5. Gọi AI Service
            // Truyền tham số 'admin' để AI biết vai trò
            $aiService = new AIService();
            $reply = $aiService->getChatResponse($message, $contextData, 'admin');

            echo json_encode(['success' => true, 'reply' => $reply]);

        } catch (\Throwable $e) {
            error_log("AdminChat Controller Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'reply' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}