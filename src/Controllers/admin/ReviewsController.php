<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class ReviewsController extends Controller {

    public function index() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $reviewModel = $this->model('Review');
        $reviews = $reviewModel->getAllReviews();

        $data = [
            'reviews' => $reviews,
            'active' => 'reviews'
        ];

        $this->view('admin/reviews/index', $data);
    }

    public function detail($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        $reviewModel = $this->model('Review');
        $review = $reviewModel->getReviewById($id);

        if (!$review) {
            $_SESSION['flash_error'] = "Không tìm thấy đánh giá yêu cầu!";
            header("Location: /admin/reviews");
            exit;
        }

        $data = [
            'review' => $review,
            'active' => 'reviews'
        ];

        $this->view('admin/reviews/detail', $data);
    }

    public function delete($id) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = "Yêu cầu không hợp lệ";
            header("Location: /admin/reviews");
            exit;
        }

        $reviewModel = $this->model('Review');
        $review = $reviewModel->getById($id);

        if (!$review) {
            $_SESSION['flash_error'] = "Đánh giá không tồn tại";
            header("Location: /admin/reviews");
            exit;
        }

        if ($reviewModel->delete($id)) {
            $_SESSION['flash_message'] = "Xóa đánh giá thành công";
        } else {
            $_SESSION['flash_error'] = "Lỗi xóa đánh giá";
        }

        header("Location: /admin/reviews");
        exit;
    }
}