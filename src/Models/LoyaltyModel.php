<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class LoyaltyModel extends Model {

    // PHẦN 1: CLIENT - XỬ LÝ ĐIỂM & LỊCH SỬ

    // 1. Lấy điểm hiện tại của user
    public function getUserPoints($userId) {
        $stmt = $this->db->prepare("SELECT points FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        return $user ? (int)$user['points'] : 0;
    }

    // 2. Cộng điểm (Dùng cho Mua hàng, Login)
    public function addPoints($userId, $points, $type = 'purchase', $desc = '') {
        if ($points <= 0) return false;

        try {
            // A. Cộng vào bảng users
            $sqlUser = "UPDATE users SET points = points + :p WHERE id = :id";
            $this->db->prepare($sqlUser)->execute([':p' => $points, ':id' => $userId]);

            // B. Ghi lịch sử
            $this->logHistory($userId, $points, $type, $desc);
            
            return true;
        } catch (\Exception $e) {
            error_log("Lỗi addPoints: " . $e->getMessage());
            return false;
        }
    }

    // 3. Đổi điểm lấy Voucher (Alias cho redeemReward để match với controller)
    public function exchangeReward($userId, $rewardId) {
        $result = $this->redeemReward($userId, $rewardId);

        if ($result['status']) {
            return [
                'success' => true,
                'message' => $result['msg'],
                'new_points' => $this->getUserPoints($userId)
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['msg']
            ];
        }
    }
    public function redeemReward($userId, $rewardId) {
        // A. Lấy thông tin gói quà
        $stmt = $this->db->prepare("SELECT * FROM loyalty_rewards WHERE id = :id AND status = 1");
        $stmt->execute([':id' => $rewardId]);
        $reward = $stmt->fetch();

        if (!$reward) {
            return ['status' => false, 'msg' => 'Gói quà không tồn tại hoặc đã hết hạn!'];
        }

        // B. Kiểm tra điểm user
        $currentPoints = $this->getUserPoints($userId);
        if ($currentPoints < $reward['points_required']) {
            return ['status' => false, 'msg' => 'Bạn không đủ điểm (Cần: ' . number_format($reward['points_required']) . ')'];
        }

        // C. Bắt đầu xử lý (Transaction)
        try {
            $this->db->beginTransaction();

            // 1. Tạo Voucher mới (Tạo trước để lấy mã code ghi log)
            $couponCode = 'VOUCHER-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6)); 
            
            // Insert vào bảng coupons (Lưu ý: Có thêm cột user_id để làm tính năng "Kho voucher")
            $sqlCoupon = "INSERT INTO coupons (
                name, code, discount_type, discount_value, min_order_value, 
                quantity, start_date, expiration_date, status, is_private, user_id, created_at
            ) VALUES (
                :name, :code, 'fixed', :val, 0, 
                1, NOW(), :exp, 1, 1, :uid, NOW()
            )";

            $this->db->prepare($sqlCoupon)->execute([
                ':name' => "Đổi điểm: " . $reward['name'],
                ':code' => $couponCode,
                ':val'  => $reward['voucher_value'],
                ':exp'  => date('Y-m-d 23:59:59', strtotime('+30 days')), // Hạn 30 ngày
                ':uid'  => $userId // <--- QUAN TRỌNG: Gán chủ sở hữu
            ]);

            // 2. Trừ điểm User
            $sqlMinus = "UPDATE users SET points = points - :p WHERE id = :id";
            $this->db->prepare($sqlMinus)->execute([
                ':p'  => $reward['points_required'], 
                ':id' => $userId
            ]);

            // 3. Ghi lịch sử trừ điểm -> KÈM MÃ CODE ĐỂ TRA CỨU
            $logMsg = "Đổi quà: " . $reward['name'] . " (Mã: " . $couponCode . ")";
            $this->logHistory($userId, -$reward['points_required'], 'redeem', $logMsg);

            $this->db->commit();
            return ['status' => true, 'code' => $couponCode, 'msg' => 'Đổi quà thành công!'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['status' => false, 'msg' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    // 4. Kiểm tra điểm danh (Phiên bản chuẩn cho PostgreSQL)
    public function checkDailyLogin($userId) {
        // PostgreSQL dùng: created_at::DATE = CURRENT_DATE
        $sql = "SELECT COUNT(*) as count FROM point_history 
                WHERE user_id = :uid 
                AND type = 'daily_login' 
                AND created_at::DATE = CURRENT_DATE"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $result = $stmt->fetch();
        return ($result && $result['count'] > 0);
    }

    // 5. Lấy lịch sử điểm thưởng
    public function getHistory($userId) {
        $sql = "SELECT * FROM point_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    // 6. [MỚI] Lấy danh sách Voucher của riêng User ("Kho Voucher")
    public function getMyCoupons($userId) {
        // Lấy voucher thuộc về user này, chưa dùng (status=1) và còn hạn
        // PostgreSQL: NOW() trả về timestamp hiện tại
        $sql = "SELECT * FROM coupons 
                WHERE user_id = :uid 
                AND status = 1 
                AND expiration_date > NOW()
                ORDER BY created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    // Hàm Private: Ghi log (Helper)
    private function logHistory($uid, $amount, $type, $desc) {
        // PostgreSQL: NOW() là chuẩn
        $sql = "INSERT INTO point_history (user_id, amount, type, description, created_at) 
                VALUES (:uid, :amt, :type, :desc, NOW())";
        $this->db->prepare($sql)->execute([
            ':uid'  => $uid, 
            ':amt'  => $amount, 
            ':type' => $type, 
            ':desc' => $desc
        ]);
    }

    // PHẦN 2: ADMIN - QUẢN LÝ GÓI THƯỞNG

    public function getAllRewards() {
        return $this->db->query("SELECT * FROM loyalty_rewards ORDER BY points_required ASC")->fetchAll();
    }

    public function getRewardById($id) {
        $stmt = $this->db->prepare("SELECT * FROM loyalty_rewards WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getActiveRewards() {
        return $this->db->query("SELECT * FROM loyalty_rewards WHERE status = 1 ORDER BY points_required ASC")->fetchAll();
    }

    public function createReward($name, $points, $value, $status) {
        $sql = "INSERT INTO loyalty_rewards (name, points_required, voucher_value, status, created_at) 
                VALUES (:name, :pts, :val, :st, NOW())";
        return $this->db->prepare($sql)->execute([
            ':name' => $name,
            ':pts'  => $points,
            ':val'  => $value,
            ':st'   => $status
        ]);
    }

    public function updateReward($id, $name, $points, $value, $status) {
        $sql = "UPDATE loyalty_rewards 
                SET name = :name, points_required = :pts, voucher_value = :val, status = :st 
                WHERE id = :id";
        return $this->db->prepare($sql)->execute([
            ':name' => $name,
            ':pts'  => $points,
            ':val'  => $value,
            ':st'   => $status,
            ':id'   => $id
        ]);
    }
    
    public function deleteReward($id) {
        return $this->db->prepare("DELETE FROM loyalty_rewards WHERE id = :id")->execute([':id'=>$id]);
    }
}