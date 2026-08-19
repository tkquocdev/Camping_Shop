<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Coupon extends Model {

    // =================================================================
    // CLIENT SIDE (Dành cho người dùng mua hàng)
    // =================================================================

    // 1. Tìm mã để áp dụng (chỉ lấy mã còn hạn, active - không check quantity cho private coupon)
    public function findByCode($code) {
        $sql = "SELECT * FROM coupons 
                WHERE code = :code 
                AND expiration_date > NOW() 
                AND status = 1 
                AND (quantity > 0 OR is_private = 1)
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Lấy danh sách mã công khai đang chạy
    public function getAllActive() {
        $sql = "SELECT * FROM coupons 
                WHERE expiration_date > NOW() 
                AND status = 1 
                AND quantity > 0
                AND is_private = 0 
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Kiểm tra User có được phép dùng mã này không
    // (Mã riêng tư: Kiểm tra số lần dùng <= số lần trúng)
    // (Mã công khai: Luôn cho phép dùng nếu quantity > 0)
    public function canUserUse($userId, $coupon) {
        // A. Nếu là mã riêng tư (Game/Quà tặng)
        if ($coupon['is_private'] == 1) {
            // Đếm số lần user trúng coupon này (qua lucky spin hoặc prize_name)
            $sqlWon = "SELECT COUNT(*) FROM lucky_history lh
                    INNER JOIN lucky_prizes lp ON lh.prize_id = lp.id
                    WHERE lh.user_id = :uid AND (lp.coupon_code = :code OR lp.name = :code)"; 
            $stmtWon = $this->db->prepare($sqlWon);
            $stmtWon->execute([':uid' => $userId, ':code' => $coupon['code']]);
            $timesWon = (int)$stmtWon->fetchColumn();
            
            // Nếu chưa trúng qua lucky và không được assign qua loyalty, check user_id
            if ($timesWon == 0) {
                $sqlLoyalty = "SELECT COUNT(*) FROM coupons WHERE id = :cid AND user_id = :uid";
                $stmtLoyalty = $this->db->prepare($sqlLoyalty);
                $stmtLoyalty->execute([':cid' => $coupon['id'], ':uid' => $userId]);
                $isFromLoyalty = (int)$stmtLoyalty->fetchColumn();
                
                if ($isFromLoyalty == 0) {
                    // Chưa trúng lucky và không được assign loyalty
                    return false;
                }
                // Nếu được assign qua loyalty (user_id = này), thì cho dùng 1 lần
                $timesWon = 1;
            }
            
            // Đếm số lần user đã dùng mã này trong đơn hàng
            $sqlUsed = "SELECT COUNT(*) FROM orders 
                        WHERE user_id = :uid 
                        AND coupon_code = :code 
                        AND status IN ('completed', 'delivered')";
            $stmtUsed = $this->db->prepare($sqlUsed);
            $stmtUsed->execute([':uid' => $userId, ':code' => $coupon['code']]);
            $timesUsed = (int)$stmtUsed->fetchColumn();
            
            // Kiểm tra: số lần dùng < số lần trúng?
            if ($timesUsed >= $timesWon) {
                return false; // Đã dùng hết số lần -> Từ chối
            }
        } else {
            // B. Mã công khai: Luôn cho phép dùng (quantity > 0 đã được check ở findByCode())
            // Không cần check "đã dùng 1 lần rồi" vì mã công khai cho dùng thoải mái
            return true;
        }

        return true;
    }

    // [MỚI] Helper: Kiểm tra user đã dùng mã này trong đơn hàng cũ chưa
    public function hasUserUsed($userId, $code) {
        // Giả sử bảng orders của bạn có cột 'user_id' và 'coupon_code' (hoặc lưu trong json)
        // Nếu bạn lưu mã giảm giá vào bảng orders, câu lệnh sẽ như sau:
        $sql = "SELECT COUNT(*) FROM orders 
                WHERE user_id = :uid 
                AND coupon_code = :code 
                AND status IN ('completed', 'delivered')"; // Không tính đơn đã hủy
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId, ':code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    // =================================================================
    // ADMIN SIDE (Quản trị viên)
    // =================================================================

    // 4. Lấy tất cả mã (phân trang hoặc lấy hết)
    public function getAll() {
        $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Lấy chi tiết 1 mã theo ID
    public function getById($id) {
        $sql = "SELECT * FROM coupons WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 6. Kiểm tra mã đã tồn tại chưa (Dùng khi Tạo mới)
    public function exists($code) {
        $sql = "SELECT COUNT(*) FROM coupons WHERE code = :code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    // [MỚI] 6.1. Kiểm tra mã tồn tại khi Update (Trừ chính nó ra)
    public function isCodeExistsForUpdate($code, $id) {
        $sql = "SELECT COUNT(*) FROM coupons WHERE code = :code AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code, ':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // 7. Tạo mã mới
    public function create($data) {
        $sql = "INSERT INTO coupons (code, name, discount_type, discount_value, min_order_value, quantity, start_date, expiration_date, is_private, status) 
                VALUES (:code, :name, :discount_type, :discount_value, :min_order_value, :quantity, :start_date, :end_date, :is_private, :status)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // 8. Cập nhật mã (Đã bổ sung cập nhật CODE)
    public function update($data) {
        $sql = "UPDATE coupons SET 
                code = :code,  /* <--- Đã thêm dòng này */
                name = :name,
                discount_type = :discount_type,
                discount_value = :discount_value,
                min_order_value = :min_order_value,
                quantity = :quantity,
                start_date = :start_date,
                expiration_date = :end_date,
                is_private = :is_private,
                status = :status
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    // 9. Xóa mã
    public function delete($id) {
        // Kiểm tra xem mã này đã được sử dụng trong đơn hàng nào chưa
        // Nếu đã dùng thì không nên xóa hẳn mà chỉ nên ẩn đi (status = 0)
        // Tuy nhiên ở đây làm theo yêu cầu xóa cứng:
        $sql = "DELETE FROM coupons WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // 10. Trừ số lượng mã sau khi đặt hàng thành công
    public function decrementQuantity($code) {
        // Sử dụng Transaction ở Controller gọi hàm này để an toàn
        $sql = "UPDATE coupons SET quantity = quantity - 1 WHERE code = :code AND quantity > 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':code' => $code]);
    }

    // 11. [MỚI] Thống kê nhanh (Dùng cho Dashboard Admin)
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 1 AND expiration_date > NOW() THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
                FROM coupons";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 12. Lấy danh sách coupon của user (bao gồm private coupons từ lucky spin và exchanged)
    public function getUserCoupons($userId) {
        // Lấy các coupon private mà user đã trúng thưởng
        $sql = "SELECT c.*, 'private' as source, gh.created_at as won_at
                FROM coupons c
                INNER JOIN lucky_history gh ON c.code = gh.prize_name
                WHERE gh.user_id = :user_id
                AND c.is_private = 1
                AND c.expiration_date > NOW()
                AND c.status = 1
                AND c.quantity > 0

                UNION ALL

                -- Lấy các coupon đã đổi từ điểm thưởng (user_id)
                SELECT c.*, 'exchanged' as source, c.created_at as won_at
                FROM coupons c
                WHERE c.user_id = :user_id
                AND c.expiration_date > NOW()
                AND c.status = 1

                UNION ALL

                -- Lấy tất cả coupon công khai
                SELECT c.*, 'public' as source, NULL as won_at
                FROM coupons c
                WHERE c.is_private = 0
                AND c.expiration_date > NOW()
                AND c.status = 1

                ORDER BY expiration_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 13. Create or ensure game coupon exists
    public function ensureGameCoupon($code, $value = 10000) {
        // Check if coupon already exists
        if ($this->exists($code)) {
            return true; // Already exists
        }

        // Create new game coupon
        $data = [
            ':code' => $code,
            ':name' => "Game - $code",
            ':discount_type' => 'fixed',
            ':discount_value' => $value,
            ':min_order_value' => 0,
            ':quantity' => 1000,
            ':start_date' => date('Y-m-d H:i:s'),
            ':end_date' => date('Y-m-d H:i:s', strtotime('+5 years')),
            ':is_private' => 1,
            ':status' => 1
        ];

        return $this->create($data);
    }
}