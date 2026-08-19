<?php
namespace App\Controllers\Client;

use App\Core\Controller;
use App\Core\Security;

class ReviewController extends Controller {

    // Submit a review for a product
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit;
        }

        // CSRF Protection - Form token
        if (!Security::verifyCSRFToken($_POST[Security::TOKEN_FIELD] ?? null)) {
            $_SESSION['flash_error'] = "Lỗi bảo mật CSRF. Vui lòng thử lại.";
            header("Location: /");
            exit;
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = "Vui lòng đăng nhập để đánh giá sản phẩm!";
            header("Location: /auth/login");
            exit;
        }

        try {
            $userId = $_SESSION['user']['id'];
            // XSS Protection - Xử lý dữ liệu đầu vào dạng số nguyên
            $productId = Security::sanitizeInt($_POST['product_id'] ?? 0);
            $rating = Security::sanitizeInt($_POST['rating'] ?? 0);
            // XSS Protection - Xử lý dữ liệu nhập văn bản
            $comment = Security::sanitizeInput($_POST['comment'] ?? '');

            // Validate
            if (!$productId || $rating < 1 || $rating > 5) {
                throw new \Exception("Dữ liệu không hợp lệ");
            }

            // Kiểm tra nếu có mẫu nghi ngờ tấn công injection trong comment
            if (Security::hasInjectionPatterns($comment)) {
                throw new \Exception("Dữ liệu không hợp lệ");
            }

            $reviewModel = $this->model('Review');

            // Check if user has purchased this product
            if (!$reviewModel->hasPurchasedProduct($userId, $productId)) {
                throw new \Exception("Bạn cần mua sản phẩm này trước khi có thể đánh giá");
            }

            // Check if user has already reviewed
            if ($reviewModel->userHasReviewed($userId, $productId)) {
                throw new \Exception("Bạn đã đánh giá sản phẩm này rồi");
            }

            // Get user info for reviewer fields
            $userModel = $this->model('User');
            $user = $userModel->findById($userId);

            // Create review
            $reviewData = [
                'user_id' => $userId,
                'product_id' => $productId,
                'rating' => $rating,
                'comment' => $comment,
                'reviewer_name' => $user['full_name'] ?? 'Người dùng ẩn danh',
                'reviewer_avatar' => $user['avatar'] ?? null
            ];

            if ($reviewModel->create($reviewData)) {
                $_SESSION['flash_message'] = "Cảm ơn bạn đã đánh giá!";
                header("Location: /product/detail/" . $productId);
            } else {
                throw new \Exception("Lỗi lưu đánh giá");
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /product/detail/" . ($productId ?? 0));
        }
        exit;
    }

    // Update a review
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit;
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = "Vui lòng đăng nhập";
            header("Location: /auth/login");
            exit;
        }

        try {
            $reviewId = (int)($_POST['review_id'] ?? 0);
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            if (!$reviewId || $rating < 1 || $rating > 5) {
                throw new \Exception("Dữ liệu không hợp lệ");
            }

            $reviewModel = $this->model('Review');
            $review = $reviewModel->getById($reviewId);

            if (!$review || $review['user_id'] != $_SESSION['user']['id']) {
                throw new \Exception("Không có quyền chỉnh sửa đánh giá này");
            }

            if ($reviewModel->update($reviewId, [
                'rating' => $rating,
                'comment' => $comment
            ])) {
                $_SESSION['flash_message'] = "Cập nhật đánh giá thành công";
            } else {
                throw new \Exception("Lỗi cập nhật đánh giá");
            }

            header("Location: /product/detail/" . $review['product_id']);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: " . ($_POST['return_url'] ?? "/"));
        }
        exit;
    }

    // Delete a review
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /");
            exit;
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = "Vui lòng đăng nhập";
            header("Location: /auth/login");
            exit;
        }

        try {
            $reviewId = (int)($_POST['review_id'] ?? 0);
            if (!$reviewId) throw new \Exception("ID không hợp lệ");

            $reviewModel = $this->model('Review');
            $review = $reviewModel->getById($reviewId);

            if (!$review || $review['user_id'] != $_SESSION['user']['id']) {
                throw new \Exception("Không có quyền xóa đánh giá này");
            }

            $productId = $review['product_id'];

            if ($reviewModel->delete($reviewId)) {
                $_SESSION['flash_message'] = "Xóa đánh giá thành công";
            } else {
                throw new \Exception("Lỗi xóa đánh giá");
            }

            header("Location: /product/detail/" . $productId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: " . ($_POST['return_url'] ?? "/"));
        }
        exit;
    }

    // Show review form for products in an order
    public function for_order() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = "Vui lòng đăng nhập để đánh giá!";
            header("Location: /auth/login");
            exit;
        }

        $orderId = (int)($_GET['order_id'] ?? 0);
        if (!$orderId) {
            header("Location: /profile/history");
            exit;
        }

        try {
            $userId = $_SESSION['user']['id'];
            $orderModel = $this->model('Order');
            $order = $orderModel->getOrderById($orderId);

            // Check if order belongs to user
            if (!$order || $order['user_id'] != $userId) {
                throw new \Exception("Bạn không có quyền xem đơn hàng này");
            }

            // Get order items/products
            $orderItems = $orderModel->getOrderItems($orderId);
            
            $this->view('client/review/for_order', [
                'page_title' => 'Đánh giá sản phẩm từ đơn hàng #' . $orderId,
                'order' => $order,
                'order_items' => $orderItems
            ]);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            header("Location: /profile/history");
            exit;
        }
    }
}