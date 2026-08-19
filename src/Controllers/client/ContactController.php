<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Security;

class ContactController extends Controller {

    // 1. Hiển thị trang Liên hệ (Interface HTML bạn gửi)
    public function index() {
        $userRequests = [];
        
        // Nếu user đã đăng nhập, lấy lịch sử yêu cầu
        if (isset($_SESSION['user'])) {
            $customerCareModel = $this->model('CustomerCare');
            $userRequests = $customerCareModel->getUserRequests($_SESSION['user']['id']);
        }

        // Load view contact/index
        $this->view('client/contact/index', [
            'title' => 'Liên hệ & Hỗ trợ',
            'csrf_token' => Security::getCSRFToken(),
            'user_requests' => $userRequests
        ]);
    }

    // Trang giới thiệu
    public function about() {
        $this->view('client/about/index', [
            'title' => 'Về chúng tôi'
        ]);
    }

    // 2. Xử lý Chatbot (Logic quan trọng)
    public function sendChat() {
        // Tắt hiển thị lỗi để tránh làm hỏng JSON
        ini_set('display_errors', 0);
        
        // Xóa bộ nhớ đệm (buffer) để đảm bảo JSON sạch
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json; charset=utf-8');

        try {
            // CSRF Protection (AJAX header)
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Security::verifyCSRFToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['reply' => 'CSRF token invalid']);
                return;
            }

            // A. Nhận dữ liệu từ JS
            $input = json_decode(file_get_contents('php://input'), true);
            $userMessage = trim($input['message'] ?? '');

            if (empty($userMessage)) {
                echo json_encode(['reply' => 'Vui lòng nhập câu hỏi của bạn.']);
                return;
            }

            // XSS Protection - Sanitize input
            $userMessage = Security::sanitizeInput($userMessage);
            if (Security::hasInjectionPatterns($userMessage)) {
                echo json_encode(['reply' => 'Dữ liệu không hợp lệ']);
                return;
            }

            // B. Lấy dữ liệu sản phẩm để AI có kiến thức (Optional)
            // Nếu bạn muốn AI tư vấn sản phẩm, hãy lấy list sản phẩm từ Model
            $productContext = "";
            try {
                $productModel = $this->model('Product');
                if (method_exists($productModel, 'getAllProducts')) {
                    $products = $productModel->getAllProducts(5); // Lấy 5 sản phẩm mới nhất
                    foreach ($products as $p) {
                        $price = number_format($p['price']);
                        $productContext .= "- {$p['name']} (Giá: {$price}đ)\n";
                    }
                }
            } catch (\Exception $e) {
                // Bỏ qua nếu lỗi DB
            }

            // C. Tạo ngữ cảnh (Context) cho Khách hàng
            $context = "Bạn là nhân viên hỗ trợ khách hàng của 'Camping Shop'.\n\n";
            $context .= "NHIỆM VỤ:\n";
            $context .= "1. Trả lời câu hỏi về sản phẩm, giá cả, tình trạng kho.\n";
            $context .= "2. Hỗ trợ về quy trình đặt hàng, thanh toán, giao hàng.\n";
            $context .= "3. Hỗ trợ về quy trình trả hàng - hoàn tiền.\n";
            $context .= "4. Hỗ trợ các câu hỏi thường gặp.\n\n";
            $context .= "THÔNG TIN LIÊN HỆ:\n";
            $context .= "- Hotline: 0868 285 824\n";
            $context .= "- Địa chỉ: ĐH Cần Thơ\n\n";
            $context .= "PHONG CÁCH: Thân thiện, nhiệt tình, dùng emoji nhẹ nhàng.\n";
            $context .= "ĐỊNH DẠNG: Khi liệt kê danh mục hoặc sản phẩm, hãy xuống dòng cho dễ nhìn.\n\n";
            
            if (!empty($productContext)) {
                $context .= "SẢN PHẨM NỔI BẬT:\n" . $productContext;
            }
            
            $context .= "\nQUY TRÌNH TRẢ HÀNG: Khách hàng có thể yêu cầu trả hàng trong vòng 7 ngày kể từ khi nhận hàng nếu sản phẩm có lỗi hoặc không đúng mô tả. Vui lòng liên hệ Hotline để xử lý.";

            // D. Gọi AI Service
            // Đảm bảo đường dẫn file Service đúng
            require_once __DIR__ . '/../../Services/AIService.php';
            $aiService = new \App\Services\AIService();

            // Gọi AI (Mode 'client' để giọng văn thân thiện hơn)
            $reply = $aiService->getChatResponse($userMessage, $context, 'client');

            // E. TRẢ VỀ JSON (QUAN TRỌNG: Key phải là 'reply')
            echo json_encode([
                'success' => true,
                'reply'   => nl2br($reply) // Khớp với lệnh data.reply trong JS
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'reply'   => 'Xin lỗi, hệ thống đang bận. Vui lòng gọi Hotline: 0868 285 824.'
            ]);
        }
        exit;
    }

    // 3. Xử lý Form gửi yêu cầu (action="/contact/sendRequest")
    public function sendRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /contact");
            exit;
        }

        try {
            // Get input data
            $topic = trim($_POST['topic'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');

            // Validate
            if (empty($topic) || empty($content)) {
                throw new \Exception("Vui lòng nhập đầy đủ thông tin.");
            }

            // Get user info
            if (isset($_SESSION['user'])) {
                $userId = $_SESSION['user']['id'];
                $userEmail = $_SESSION['user']['email'];
                $userName = $_SESSION['user']['full_name'] ?? $_SESSION['user']['name'] ?? 'Người dùng';
                $userPhone = $phone; // Use submitted phone or user's phone
            } else {
                $userId = null; // Anonymous
                if (empty($name)) {
                    throw new \Exception("Vui lòng cung cấp họ tên.");
                }
                // Email and phone are optional for guests
                $userEmail = $email ?? null;
                $userName = $name;
                $userPhone = $phone ?? null;
            }

            // Lưu vào database (Bảng customer_care_logs)
            $db = new \App\Config\Database();
            $conn = $db->getConnection();

            // Sử dụng prepared statement để tránh SQL Injection
            $stmt = $conn->prepare("
                INSERT INTO customer_care_logs (customer_id, name, email, phone, interaction_type, content, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')
            ");
            $result = $stmt->execute([
                $userId,
                $userName,
                $userEmail,
                $userPhone,
                $topic,
                $content
            ]);

            if ($result) {
                $_SESSION['flash_message'] = "Gửi yêu cầu thành công! Admin sẽ xử lý yêu cầu của bạn.";
                header("Location: /contact");
            } else {
                throw new \Exception("Không thể gửi yêu cầu. Vui lòng thử lại.");
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /contact");
        }
        exit;
    }
}