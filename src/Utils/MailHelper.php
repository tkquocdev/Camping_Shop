<?php

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;
use Dompdf\Options;

class MailHelper {

    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // Cấu hình SMTP từ biến môi trường (nếu có)
        $this->mailer->isSMTP();
        // Phương thức đọc cấu hình: ưu tiên $_ENV rồi $_SERVER rồi getenv().
        $getEnv = function($key, $default = null) {
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
            return getenv($key) ?: $default;
        };

        $requiredEnv = function($key) use ($getEnv) {
            $value = $getEnv($key);
            if ($value === null || $value === '') {
                throw new \RuntimeException("Missing required environment variable: $key");
            }

            return $value;
        };

        $this->mailer->Host = $getEnv('MAIL_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $requiredEnv('MAIL_USERNAME');
        $this->mailer->Password = $requiredEnv('MAIL_PASSWORD');
        $this->mailer->SMTPSecure = (strtolower($getEnv('MAIL_ENCRYPTION', 'tls')) === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)$getEnv('MAIL_PORT', 587);

        $this->mailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ];

        // Cấu hình người gửi
        $from = $getEnv('MAIL_FROM_ADDRESS', $this->mailer->Username);
        $fromName = $getEnv('MAIL_FROM_NAME', 'Camping Shop');
        $this->mailer->setFrom($from, trim($fromName, '"'));

        // Reply-To: mặc định là SMTP user (để trả về đúng tài khoản đang dùng gửi)
        if ($from !== $this->mailer->Username) {
            $this->mailer->addReplyTo($this->mailer->Username, 'Camping Shop');
        }

        $this->mailer->CharSet = 'UTF-8';

        // Debug (bật khi cần kiểm tra lỗi gửi mail)
        if (getenv('MAIL_DEBUG') === '1') {
            $this->mailer->SMTPDebug = 2;
            $this->mailer->Debugoutput = 'error_log';
        }
    }

    /**
     * Gửi email xác nhận đơn hàng
     */
    public function sendOrderConfirmation($toEmail, $orderData) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Xác nhận đơn hàng #' . $orderData['id'] . ' - Camping Shop';

            // Tạo nội dung HTML
            $htmlContent = $this->generateOrderEmailHTML($orderData);
            $this->mailer->Body = $htmlContent;

            // Tạo nội dung text thuần
            $this->mailer->AltBody = $this->generateOrderEmailText($orderData);

            // Không đính kèm PDF trong email - client có thể tải từ trang profile

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage() . " (" . $this->mailer->ErrorInfo . ")");
            return false;
        }
    }

    /**
     * Tạo nội dung HTML cho email đơn hàng (đồng bộ với hoá đơn PDF)
     */
    private function generateOrderEmailHTML($data) {
        $invoiceData = $this->buildInvoiceData($data);
        $invoiceFragment = $this->renderInvoiceFragment($invoiceData);

        return $this->wrapHtmlDocument($invoiceFragment, 'Hóa đơn #' . $invoiceData['order']['id']);
    }


    /**
     * Tạo nội dung text thuần cho email đơn hàng
     */
    private function generateOrderEmailText($data) {
        $text = "Cảm ơn bạn đã đặt hàng!\n\n";
        $text .= "Đơn hàng #: " . $data['id'] . "\n";
        $text .= "Khách hàng: " . $data['customer_name'] . "\n";
        $text .= "SĐT: " . $data['phone'] . "\n";
        $text .= "Địa chỉ: " . $data['address'] . "\n\n";

        $text .= "=== SẢN PHẨM ===\n";
        foreach ($data['products'] as $product) {
            if (!isset($product['id'])) continue;
            $quantity = $data['cart'][$product['id']] ?? 1;
            $text .= "- " . $product['name'] . " x" . $quantity . " = " . number_format($product['price'] * $quantity) . " đ\n";
        }

        $text .= "\nTạm tính: " . number_format($data['subTotal']) . " đ\n";
        $text .= "Phí vận chuyển: " . number_format($data['shipping']) . " đ\n";
        if ($data['discount'] > 0) {
            $text .= "Giảm giá: -" . number_format($data['discount']) . " đ\n";
        }
        $text .= "Tổng cộng: " . number_format($data['total']) . " đ\n\n";
        $text .= "Đây là email tự động, vui lòng không trả lời.\n";
        $text .= "Camping Shop - Thới An Hội, Kế Sách, Sóc Trăng\n";

        return $text;
    }

    /**
     * Sinh PDF hoá đơn (dùng Dompdf) để đính kèm mail.
     */
    private function generateInvoicePdf($data) {
        $invoiceData = $this->buildInvoiceData($data);
        $invoiceFragment = $this->renderInvoiceFragment($invoiceData);
        $html = $this->wrapHtmlDocument($invoiceFragment, 'Hóa đơn #' . $invoiceData['order']['id']);

        // Suppress deprecation warnings from dompdf and font libraries
        $oldErrorReporting = error_reporting();
        error_reporting(0);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        @$dompdf->render();

        $output = $dompdf->output();

        // Restore error reporting
        error_reporting($oldErrorReporting);

        return $output;
    }

    /**
     * Lấy base URL của site (dùng để render link trong email)
     */
    private function getBaseUrl() {
        $candidate = getenv('APP_URL') ?: ($_ENV['APP_URL'] ?? null);
        if (!empty($candidate)) {
            return rtrim($candidate, '/');
        }

        if (!empty($_SERVER['HTTP_HOST'])) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        return 'http://localhost';
    }

    /**
     * Tuyển dữ liệu để render hoá đơn
     */
    private function buildInvoiceData($data) {
        $items = [];
        foreach ($data['products'] as $product) {
            if (!isset($product['id'])) continue;
            $qty = isset($data['cart'][$product['id']]) ? $data['cart'][$product['id']] : 1;
            $items[] = [
                'name' => $product['name'],
                'quantity' => $qty,
                'price' => $product['price'],
            ];
        }

        $order = [
            'id' => $data['id'],
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'status' => $data['status'] ?? 'pending',
            'phone' => $data['phone'] ?? '',
            'shipping_address' => $data['address'] ?? '',
            'note' => $data['note'] ?? '',
            'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : 'cod',
            'total_amount' => $data['total'],
            'shipping_fee' => $data['shipping'],
            'discount_amount' => $data['discount'] ?? 0,
        ];

        return [
            'order' => $order,
            'items' => $items,
            'customerName' => $data['customer_name'] ?? '',
        ];
    }

    /**
     * Xây dựng HTML document đầy đủ (kèm head/meta/css)
     */
    private function wrapHtmlDocument($bodyHtml, $title = 'Hoá đơn') {
        return '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>'
            . '<style>'
            . 'body{font-family:DejaVu Sans, sans-serif; font-size:12px; margin:0; padding:0; color:#000;}'
            . '.container{padding:10px;}'
            . '.clearfix::after{content:"";display:table;clear:both;}'
            . '.header{width:100%;margin-bottom:12px;}'
            . '.title{text-align:center;font-weight:bold;margin-bottom:12px;}'
            . '.product-table{width:100%;border-collapse:collapse;margin-top:10px;}'
            . '.product-table th,.product-table td{border:1px solid #000;padding:6px;font-size:11px;}'
            . '.product-table th{background:#f0f0f0;}'
            . '</style></head><body><div class="container">' . $bodyHtml . '</div></body></html>';
    }

    /**
     * Render fragment hoá đơn (từ file view) và trả về HTML string.
     */
    private function renderInvoiceFragment($invoiceData) {
        $order = $invoiceData['order'];
        $items = $invoiceData['items'];
        $customerName = $invoiceData['customerName'];

        ob_start();
        require ROOT_PATH . '/views/email/invoice_pdf.php';
        return ob_get_clean();
    }

    /**
     * Gửi email thông báo khác (tùy chỉnh)
     */
    public function sendCustomEmail($toEmail, $subject, $htmlContent, $textContent = '') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlContent;

            if ($textContent) {
                $this->mailer->AltBody = $textContent;
            }

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage() . " (" . $this->mailer->ErrorInfo . ")");
            return false;
        }
    }

    /**
     * Gửi email chứa mã OTP để đặt lại mật khẩu
     */
    public function sendOTP($toEmail, $otp) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Mã OTP đặt lại mật khẩu - Camping Shop';

            // Tạo nội dung HTML
            $htmlContent = $this->generateOTPEmailHTML($otp, $toEmail);
            $this->mailer->Body = $htmlContent;

            // Tạo nội dung text thuần
            $textContent = "Mã OTP của bạn: " . $otp . "\n\n"
                         . "Mã này sẽ hết hạn trong 15 phút.\n"
                         . "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n\n"
                         . "Camping Shop";
            $this->mailer->AltBody = $textContent;

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Mail Error (OTP): " . $e->getMessage() . " (" . $this->mailer->ErrorInfo . ")");
            return false;
        }
    }

    /**
     * Tạo nội dung HTML cho email OTP
     */
    private function generateOTPEmailHTML($otp, $email) {
        $html = '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP - Camping Shop</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content p {
            margin: 15px 0;
            color: #555555;
            font-size: 14px;
        }
        .otp-box {
            background-color: #f9f9f9;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 48px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            margin: 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 12px;
            margin: 20px 0;
            font-size: 13px;
            color: #856404;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #999999;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Đặt Lại Mật Khẩu</h1>
        </div>
        <div class="content">
            <p>Xin chào,</p>
            <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Vui lòng sử dụng mã OTP dưới đây để tiếp tục:</p>
            
            <div class="otp-box">
                <p class="otp-code">' . htmlspecialchars($otp) . '</p>
            </div>
            
            <p><strong>Mã này sẽ hết hạn trong 15 phút.</strong></p>
            
            <div class="warning">
                <strong>⚠️ Cảnh báo:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này. Mã OTP này chỉ dành cho bạn.
            </div>
            
            <p style="font-size: 12px; color: #999;">
                Email được gửi đến: ' . htmlspecialchars($email) . '
            </p>
        </div>
        <div class="footer">
            <p>Đây là email tự động, vui lòng không trả lời.</p>
            <p>© 2026 Camping Shop - Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>';
        return $html;
    }
}
