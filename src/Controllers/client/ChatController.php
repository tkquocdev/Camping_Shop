<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Security;

class ChatController extends Controller {

    public function sendMessage() {
        // 1. CẤU HÌNH PHẢN HỒI JSON (Bắt buộc để Frontend JS hiểu được)
        ini_set('display_errors', 0); // Tắt lỗi HTML rác trồi lên làm hỏng JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            // Kiểm tra phương thức gửi
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['status' => 'error', 'reply' => 'Phương thức không hợp lệ.']);
                return;
            }

            // CSRF Protection (AJAX header)
            $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!Security::verifyCSRFToken($csrfToken)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'reply' => 'CSRF token invalid']);
                return;
            }

            // 2. NHẬN DỮ LIỆU TỪ CLIENT
            $input = json_decode(file_get_contents('php://input'), true);
            $message = trim($input['message'] ?? '');
            $userId = $_SESSION['user']['id'] ?? null;
            $userRole = $_SESSION['user']['role'] ?? 'customer';

            if (empty($message)) {
                echo json_encode(['status' => 'error', 'reply' => 'Bạn chưa nhập câu hỏi.']);
                return;
            }

            // XSS Protection - Sanitize message input
            $message = Security::sanitizeInput($message);
            if (Security::hasInjectionPatterns($message)) {
                echo json_encode(['status' => 'error', 'reply' => 'Dữ liệu không hợp lệ']);
                return;
            }

            // 3. KHỞI TẠO MODEL & SERVICE
            $productModel = $this->model('Product');
            $customerCareModel = $this->model('CustomerCare');

            // Load AI Service thủ công
            $aiServicePath = __DIR__ . '/../../Services/AIService.php';
            if (file_exists($aiServicePath)) {
                require_once $aiServicePath;
                $aiService = new \App\Services\AIService();
            } else {
                throw new \Exception("Thiếu file Service AI.");
            }

            // 4. PHÂN LOẠI CHẾ ĐỘ (ADMIN vs CUSTOMER)
            $mode = ($userRole === 'admin') ? 'admin' : 'customer';

            // 5. XÂY DỰNG CONTEXT THEO CHỈ ĐỀ
            if ($mode === 'admin') {
                // ADMIN MODE: LẤY THỐNG KÊ, DOANH THU, SẢN PHẨM BÁN CHẠY, HẾT HÀNG, DANH MỤC
                $context = "VAI TRÒ: Bạn là Trợ lý Ảo Đắc Lực cho Chủ Cửa Hàng Camping Shop.\n\n";
                
                // A. Thống kê doanh thu
                if (method_exists($productModel, 'getRevenueStats')) {
                    try {
                        $revenueStats = $productModel->getRevenueStats();
                        if ($revenueStats) {
                            $context .= "THỐNG KÊ DOANH THU:\n";
                            $context .= "• Tổng đơn hàng: " . ($revenueStats['total_orders'] ?? 0) . "\n";
                            $context .= "• Tổng doanh thu: " . number_format($revenueStats['total_revenue'] ?? 0) . " VNĐ\n";
                            $context .= "• Đơn hàng hôm nay: " . ($revenueStats['today_orders'] ?? 0) . "\n";
                            $context .= "• Doanh thu hôm nay: " . number_format($revenueStats['today_revenue'] ?? 0) . " VNĐ\n\n";
                        }
                    } catch (\Throwable $e) {
                        error_log("Revenue Stats Error: " . $e->getMessage());
                    }
                }

                // B. Sản phẩm bán chạy nhất (Top 5)
                if (method_exists($productModel, 'getTopSellingProducts')) {
                    try {
                        $topProducts = $productModel->getTopSellingProducts(5);
                        if (!empty($topProducts)) {
                            $context .= "TOP SẢN PHẨM BÁN CHẠY:\n";
                            foreach ($topProducts as $idx => $p) {
                                $context .= "• " . ($idx + 1) . ". {$p['name']} - Đã bán: {$p['sold_quantity']} cái, Doanh thu: " . number_format($p['revenue'] ?? 0) . " VNĐ\n";
                            }
                            $context .= "\n";
                        }
                    } catch (\Throwable $e) {
                        error_log("Top Products Error: " . $e->getMessage());
                    }
                }

                // C. Sản phẩm sắp hết hàng
                if (method_exists($productModel, 'getLowStockProducts')) {
                    try {
                        $lowStock = $productModel->getLowStockProducts(10);
                        if (!empty($lowStock)) {
                            $context .= "SẢN PHẨM SẮP HẾT HÀNG:\n";
                            foreach ($lowStock as $p) {
                                $context .= "• {$p['name']}: Còn {$p['stock']} cái\n";
                            }
                            $context .= "\n";
                        }
                    } catch (\Throwable $e) {
                        error_log("Low Stock Error: " . $e->getMessage());
                    }
                }

                // D. Danh sách danh mục
                if (method_exists($productModel, 'getAllCategories')) {
                    try {
                        $cats = $productModel->getAllCategories();
                        if (!empty($cats)) {
                            $context .= "DANH MỤC SẢN PHẨM:\n";
                            foreach ($cats as $c) {
                                $context .= "• {$c['name']}\n";
                            }
                            $context .= "\n";
                        }
                    } catch (\Throwable $e) {
                        error_log("Categories Error: " . $e->getMessage());
                    }
                }

            } else {
                // CUSTOMER MODE
                $context = "VAI TRÒ: Bạn là nhân viên tư vấn bán hàng của Camping Shop.\n";
                $context .= "PHONG CÁCH: Thân thiện, nhiệt tình, sử dụng emoji nhẹ nhàng.\n";
                $context .= "ĐỊNH DẠNG: Khi liệt kê danh mục hoặc sản phẩm, hãy xuống dòng cho dễ nhìn.\n\n";
                $context .= "THÔNG TIN LIÊN HỆ:\n";
                $context .= "• Hotline: 0868 285 824\n";
                $context .= "• Địa chỉ: Đại học Cần Thơ\n\n";


                $products = [];
                $searchKeywords = [
                    // Common tent-related keywords
                    'lều' => 'lều', 'tent' => 'lều', 'lều cắm trại' => 'lều cắm trại',
                    // Sleeping bag
                    'túi ngủ' => 'túi ngủ', 'sleeping bag' => 'túi ngủ',
                    // Cooking
                    'bếp' => 'bếp dã ngoại', 'nấu' => 'bếp dã ngoại', 'nồi' => 'bếp dã ngoại',
                    // Light
                    'đèn' => 'đèn pin', 'đèn pin' => 'đèn pin', 'lantern' => 'đèn pin',
                    // Survival tools
                    'dụng cụ' => 'dụng cụ sinh tồn', 'công cụ' => 'dụng cụ sinh tồn',
                    // Chair
                    'ghế' => 'ghế gấp', 'ghế gấp' => 'ghế gấp',
                    // Boots
                    'giày' => 'giày boots', 'giày boots' => 'giày boots', 'boots' => 'giày boots',
                    // Protection
                    'bảo hộ' => 'đồ bảo hộ', 'đồ bảo hộ' => 'đồ bảo hộ',
                    // Accessories
                    'phụ kiện' => 'phụ kiện khác', 'accessories' => 'phụ kiện khác'
                ];

                $messageLower = strtolower($message);
                $foundKeyword = null;

                // Tìm kiếm keyword trong câu hỏi để xác định danh mục sản phẩm phù hợp
                foreach ($searchKeywords as $keyword => $category) {
                    if (strpos($messageLower, $keyword) !== false) {
                        $foundKeyword = $category;
                        break;
                    }
                }

                // Nếu tìm thấy keyword, tìm sản phẩm theo danh mục đó
                if ($foundKeyword && method_exists($productModel, 'findProductsByCategory')) {
                    try {
                        $products = $productModel->findProductsByCategory($foundKeyword);
                    } catch (\Throwable $e) {
                        error_log("Search Products Error: " . $e->getMessage());
                    }
                }

                // Nếu không tìm thấy sản phẩm nào từ tìm kiếm, thêm thông tin về các danh mục có số lượng sản phẩm để gợi ý khách hàng
                if (empty($products) && method_exists($productModel, 'getCategoriesWithProductCount')) {
                    try {
                        $catWithCount = $productModel->getCategoriesWithProductCount();
                        if (!empty($catWithCount)) {
                            $context .= "DANH MỤC SHOP BÁN (SỐ LƯỢNG):\n";
                            foreach ($catWithCount as $c) {
                                if ($c['product_count'] > 0) {
                                    $context .= "• {$c['name']}: {$c['product_count']} mặt hàng\n";
                                }
                            }
                            $context .= "\n";
                        }
                    } catch (\Throwable $e) {
                        error_log("Categories Count Error: " . $e->getMessage());
                    }
                }

                // Nếu có sản phẩm khớp với tìm kiếm, hiển thị danh sách sản phẩm đó
                if (!empty($products)) {
                    $context .= "DANH SÁCH SẢN PHẨM KHỚP VỚI TÌM KIẾM:\n";
                    foreach ($products as $p) {
                        $price = number_format($p['price'] ?? 0) . " VNĐ";
                        $stock = ($p['stock'] > 0) ? "Còn hàng ({$p['stock']} cái)" : "Hết hàng";
                        $context .= "• {$p['name']} - {$price} ({$stock})\n";
                    }
                    $context .= "\nNêu rõ tên sản phẩm để biết chi tiết.\n\n";
                } else {
                    // Get newest products as fallback
                    if (method_exists($productModel, 'getNewestProducts')) {
                        try {
                            $products = $productModel->getNewestProducts(8);
                            if (!empty($products)) {
                                $context .= "SẢN PHẨM MỚI:\n";
                                foreach ($products as $p) {
                                    $price = number_format($p['price'] ?? 0) . " VNĐ";
                                    $stock = ($p['stock'] > 0) ? "Còn hàng" : "Hết hàng";
                                    $context .= "• {$p['name']} - {$price} ({$stock})\n";
                                }
                                $context .= "\n";
                            }
                        } catch (\Throwable $e) {
                            error_log("Newest Products Error: " . $e->getMessage());
                        }
                    }
                }

                // QUY TRÌNH TRẢ HÀNG
                $context .= "QUY TRÌNH TRẢ HÀNG (7 ngày):\n";
                $context .= "• Khách có thể trả hàng trong 7 ngày kể từ khi nhận\n";
                $context .= "• Lý do: Sản phẩm lỗi hoặc không đúng mô tả\n";
                $context .= "• Liên hệ: Gọi 0868 285 824 để xử lý\n";
            }

            // 6. GỌI AI ĐỂ LẤY CÂU TRẢ LỜI
            $reply = $aiService->getChatResponse($message, $context, $mode);

            // 7. TRẢ KẾT QUẢ VỀ JSON
            echo json_encode([
                'status' => 'success',
                'success' => true,
                'reply' => nl2br($reply) // Chuyển xuống dòng thành thẻ <br>
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                'status' => 'error',
                'success' => false,
                'reply' => 'Hệ thống đang bận, vui lòng thử lại sau. (Lỗi: ' . $e->getMessage() . ')'
            ]);
        }
    }
}