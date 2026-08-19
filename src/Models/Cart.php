<?php
namespace App\Models;

use App\Core\Model;

class Cart extends Model {

    // 1. LẤY GIỎ HÀNG CỦA USER
    // Join bảng cart_items với products và categories để lấy tên, giá, ảnh, danh mục...
    public function getUserCart($userId) {
        $sql = "SELECT p.*, c.quantity as buy_quantity, cat.name as category_name
                FROM cart_items c 
                JOIN products p ON c.product_id = p.id 
                LEFT JOIN categories cat ON p.category_id = cat.id
                WHERE c.user_id = :uid
                ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    // 2. THÊM SẢN PHẨM VÀO DB (Dùng cho cả Sync và Thêm mới)
    // Sử dụng cú pháp PostgreSQL (ON CONFLICT) để xử lý trùng lặp
    public function addToUserCart($userId, $productId, $quantity = 1) {
        $sql = "INSERT INTO cart_items (user_id, product_id, quantity) 
                VALUES (:uid, :pid, :qty)
                ON CONFLICT (user_id, product_id) 
                DO UPDATE SET quantity = cart_items.quantity + :qty";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':uid' => $userId, 
            ':pid' => $productId, 
            ':qty' => $quantity
        ]);
    }

    // 3. ĐỒNG BỘ TỪ SESSION VỀ DB
    // Hàm này chạy vòng lặp để đẩy từng món từ Session vào DB
    public function syncSessionToDb($userId, $sessionCart) {
        if (empty($sessionCart)) return;

        foreach ($sessionCart as $productId => $qty) {
            $this->addToUserCart($userId, $productId, $qty);
        }
    }

    // 4. CẬP NHẬT SỐ LƯỢNG (Dùng cho AJAX ở trang giỏ hàng)
    public function updateQuantity($userId, $productId, $quantity) {
        // Nếu số lượng <= 0 thì xóa luôn sản phẩm đó
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }

        $sql = "UPDATE cart_items SET quantity = :qty 
                WHERE user_id = :uid AND product_id = :pid";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':qty' => $quantity, 
            ':uid' => $userId, 
            ':pid' => $productId
        ]);
    }

    // 5. XÓA 1 SẢN PHẨM KHỎI GIỎ
    public function removeItem($userId, $productId) {
        $sql = "DELETE FROM cart_items WHERE user_id = :uid AND product_id = :pid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':uid' => $userId, ':pid' => $productId]);
    }

    // 6. XÓA TOÀN BỘ GIỎ HÀNG (Dùng sau khi Thanh toán thành công)
    public function clearCart($userId) {
        $sql = "DELETE FROM cart_items WHERE user_id = :uid";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':uid' => $userId]);
    }

    // 7. [HỖ TRỢ] LẤY CHI TIẾT SẢN PHẨM TỪ LIST ID (Dùng cho khách vãng lai/Session)
    public function getProductsByIds($ids) {
        if (empty($ids)) return [];

        // Tạo chuỗi dấu hỏi chấm (?,?,?) tương ứng số lượng ID để bind param
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        // PDO cần mảng giá trị liên tục (indexed array)
        $stmt->execute(array_values($ids));
        
        return $stmt->fetchAll();
    }

    // 8. ĐẾM TỔNG SỐ LƯỢNG SẢN PHẨM TRONG GIỎ (Dùng cho Header Badge)
    public function countItems($userId) {
        $sql = "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = :uid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}