<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Review extends Model {

    // Lấy đánh giá theo sản phẩm (including reviewer info)
    public function getReviewsByProduct($productId) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   COALESCE(r.reviewer_name, u.full_name) as user_name,
                   COALESCE(r.reviewer_avatar, u.avatar) as user_avatar
            FROM reviews r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if user has purchased this product
    public function hasPurchasedProduct($userId, $productId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'completed'
        ");
        $stmt->execute([$userId, $productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    }

    // Check if user has already reviewed this product
    public function userHasReviewed($userId, $productId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM reviews
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$userId, $productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    }

    // Thêm đánh giá
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO reviews (user_id, product_id, rating, comment, reviewer_name, reviewer_avatar) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['user_id'],
            $data['product_id'],
            $data['rating'],
            $data['comment'] ?? null,
            $data['reviewer_name'] ?? null,
            $data['reviewer_avatar'] ?? null
        ]);
    }

    // Get review by ID
    public function getById($reviewId) {
        $stmt = $this->db->prepare("
            SELECT r.*,
                   COALESCE(r.reviewer_name, u.full_name) as user_name,
                   COALESCE(r.reviewer_avatar, u.avatar) as user_avatar
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reviewId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update review
    public function update($reviewId, $data) {
        $stmt = $this->db->prepare("
            UPDATE reviews 
            SET rating = ?, comment = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['rating'],
            $data['comment'] ?? null,
            $reviewId
        ]);
    }

    // Delete review
    public function delete($reviewId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$reviewId]);
    }

    // Tính điểm trung bình
    public function getAverageRating($productId) {
        $stmt = $this->db->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_rating'] ?? 0;
    }

    // Get all reviews (for admin)
    public function getAllReviews() {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   COALESCE(r.reviewer_name, u.full_name) as user_name,
                   p.name as product_name,
                   p.image as product_image
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get review by ID for admin with full details
    public function getReviewById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, 
                   COALESCE(r.reviewer_name, u.full_name) as user_name,
                   u.email as user_email,
                   p.name as product_name, 
                   p.image as product_image
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}