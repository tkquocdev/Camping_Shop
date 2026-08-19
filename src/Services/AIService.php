<?php
namespace App\Services;

class AIService {
    private $apiKey;
    private $model;

    public function __construct() {
        $this->apiKey = $this->requiredEnv('GROQ_API_KEY');
        $this->model = $this->env('GROQ_MODEL', 'llama-3.3-70b-versatile');
    }

    /**
     * Hàm xử lý chat chung cho cả Khách hàng và Admin
     * @param string $userMessage Câu hỏi của người dùng
     * @param string $contextData Dữ liệu kèm theo (List sản phẩm hoặc Báo cáo doanh thu)
     * @param string $mode Chế độ: 'customer' (mặc định) hoặc 'admin'
     */
    public function getChatResponse($userMessage, $contextData, $mode = 'customer') {
        $url = $this->env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
        
        // 1. CẤU HÌNH PROMPT THEO CHẾ ĐỘ (MODE)
        if ($mode === 'admin') {
            // --- CHẾ ĐỘ ADMIN: Trợ lý thông minh, Phân tích số liệu, Viết content ---
            $systemPrompt = "Bạn là Trợ lý Ảo Đắc Lực riêng cho Chủ Shop (Admin) của Camping Shop. \n\n" .
                            "NHIỆM VỤ CHÍNH:\n" .
                            "1. Phân tích báo cáo doanh thu, đơn hàng, kho hàng từ dữ liệu được cung cấp.\n" .
                            "2. Hỗ trợ trả lời các câu hỏi thường gặp: quy trình quản lý, báo cáo doanh số.\n" .
                            "3. Hỗ trợ viết content quảng cáo, status Facebook, email marketing.\n" .
                            "4. Đưa ra lời khuyên quản trị kinh doanh.\n\n" .
                            "PHONG CÁCH: Chuyên nghiệp, sắc sảo, hữu ích, sử dụng dấu bullet point khi liệt kê.\n" .
                            "ĐỊNH DẠNG: Khi liệt kê sản phẩm hoặc dữ liệu, hãy xuống dòng cho dễ nhìn.\n" .
                            "DỮ LIỆU CỬA HÀNG HIỆN TẠI:\n" . 
                            ($contextData ?: "Hiện chưa có số liệu thống kê.") . "\n" .
                            "LƯU Ý: Trả lời chính xác dựa trên số liệu trên. Nếu không có dữ liệu, nói rõ ràng là chưa có.";
            
            $maxTokens = 1500;
            $temperature = 0.7;

        } else {
            // --- CHẾ ĐỘ KHÁCH HÀNG: Nhân viên bán hàng nhanh gọn ---
            $systemPrompt = "Bạn là nhân viên tư vấn bán hàng của Camping Shop.\n\n" .
                            "NHIỆM VỤ CHÍNH:\n" .
                            "1. Trả lời câu hỏi về sản phẩm, giá cả, kho hàng.\n" .
                            "2. Hỗ trợ về quy trình đặt hàng, thanh toán, giao hàng.\n" .
                            "3. Hỗ trợ về quy trình trả hàng, hoàn tiền.\n" .
                            "4. Trả lời các câu hỏi thường gặp của khách hàng.\n\n" .
                            "PHONG CÁCH: Nhanh gọn, đi thẳng vào vấn đề, thân thiện, sử dụng emoji nhẹ nhàng.\n" .
                            "ĐỊNH DẠNG: Khi liệt kê danh mục, sản phẩm hoặc thông tin, hãy xuống dòng cho dễ nhìn.\n" .
                            "THÔNG TIN LIÊN HỆ:\n" .
                            "- Hotline: 0868 285 824\n" .
                            "- Địa chỉ: Đại học Cần Thơ\n\n" .
                            "DỮ LIỆU SẢN PHẨM & DANH MỤC:\n" . 
                            ($contextData ?: "Hiện không có dữ liệu sản phẩm.") . "\n" .
                            "QUY TRÌNH TRẢ HÀNG: Khách hàng có thể yêu cầu trả hàng trong vòng 7 ngày kể từ khi nhận hàng nếu sản phẩm có lỗi hoặc không đúng mô tả.\n" .
                            "LƯU Ý: Nếu không có dữ liệu về sản phẩm, không bịa đặt - nói rõ ràng 'Xin lỗi, shop chưa cập nhật danh sách này, bạn vui lòng xem trên website nhé!'";
            
            $maxTokens = 500;
            $temperature = 0.5;
        }

        // 2. TẠO MESSAGE GỬI ĐI
        $messages = [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userMessage]
        ];

        // 3. CẤU HÌNH GỬI API
        $data = [
            "model" => $this->model,
            "messages" => $messages,
            "temperature" => $temperature, 
            "max_tokens" => $maxTokens
        ];

        // 4. GỌI CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        $result = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "Lỗi kết nối AI: " . $error_msg;
        }
        
        curl_close($ch);

        // 5. XỬ LÝ KẾT QUẢ TRẢ VỀ
        $response = json_decode($result, true);

        if (isset($response['error'])) {
            return "Lỗi API Groq: " . $response['error']['message'];
        }

        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }

        return "Xin lỗi, hiện tại hệ thống AI đang bận.";
    }

    private function env($key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }

    private function requiredEnv($key) {
        $value = $this->env($key);
        if ($value === null || $value === '') {
            throw new \RuntimeException("Missing required environment variable: $key");
        }

        return $value;
    }
}
?>