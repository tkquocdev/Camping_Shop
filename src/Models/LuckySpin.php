<?php
namespace App\Models;

use App\Core\Model;

class LuckySpin extends Model {

    // 1. Lấy danh sách tất cả phần thưởng (Sắp xếp theo tỷ lệ giảm dần)
    public function getAllPrizes() {
        $sql = "SELECT * FROM lucky_prizes ORDER BY percent DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // 2. Lấy chi tiết 1 phần thưởng theo ID
    public function getPrizeById($id) {
        $sql = "SELECT * FROM lucky_prizes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // 3. [MỚI] Thêm giải thưởng mới
    public function create($data) {
        $sql = "INSERT INTO lucky_prizes (name, coupon_code, percent, color) 
                VALUES (:name, :code, :percent, :color)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'    => $data['name'],
            ':code'    => $data['coupon_code'],
            ':percent' => $data['percent'],
            ':color'   => $data['color']
        ]);
    }

    // 4. [CẬP NHẬT] Sửa thông tin phần thưởng (Bao gồm cả tên)
    public function updatePrize($id, $data) {
        $sql = "UPDATE lucky_prizes 
                SET name = :name, 
                    percent = :percent, 
                    color = :color, 
                    coupon_code = :code 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'    => $data['name'],
            ':percent' => $data['percent'],
            ':color'   => $data['color'],
            ':code'    => $data['coupon_code'],
            ':id'      => $id
        ]);
    }

    // 5. [MỚI] Xóa giải thưởng
    public function delete($id) {
        $sql = "DELETE FROM lucky_prizes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // 6. Kiểm tra xem user đã chơi hôm nay chưa
    public function checkPlayedToday($userId) {
        // Lưu ý: Cú pháp created_at::DATE dành cho PostgreSQL. 
        // Nếu dùng MySQL thì sửa thành: DATE(created_at) = CURDATE()
        $sql = "SELECT * FROM lucky_history 
                WHERE user_id = :uid 
                AND created_at::DATE = CURRENT_DATE"; 
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch();
    }

    // 7. Lưu kết quả quay vào lịch sử
    public function saveHistory($userId, $prize) {
        $sql = "INSERT INTO lucky_history (user_id, prize_id, prize_name, created_at) 
                VALUES (:uid, :pid, :pname, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':uid'   => $userId,
            ':pid'   => $prize['id'],
            ':pname' => $prize['name']
        ]);
    }

    // 8. Lấy lịch sử quay thưởng
    public function getHistory($userId) {
        $sql = "SELECT h.prize_name, h.created_at, p.coupon_code
                FROM lucky_history h
                LEFT JOIN lucky_prizes p ON h.prize_id = p.id
                WHERE h.user_id = :uid 
                ORDER BY h.created_at DESC 
                LIMIT 20";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    // 9. Kiểm tra user đã trúng mã này chưa
    public function checkUserWonCoupon($userId, $couponCode) {
        $sql = "SELECT COUNT(*) as count
                FROM lucky_history h
                JOIN lucky_prizes p ON h.prize_id = p.id
                WHERE h.user_id = :uid 
                AND p.coupon_code = :code";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':code' => $couponCode
        ]);
        
        $result = $stmt->fetch();
        return ($result['count'] > 0);
    }

    public function createCouponAuto($data) {
        // 1. Kiểm tra xem mã này đã tồn tại trong bảng coupons chưa
        $sqlCheck = "SELECT COUNT(*) as count FROM coupons WHERE code = :code";
        $stmt = $this->db->prepare($sqlCheck);
        $stmt->execute([':code' => $data['code']]);
        $exists = $stmt->fetch()['count'] > 0;

        // Nếu đã có rồi thì không tạo nữa, trả về true luôn
        if ($exists) {
            return true;
        }

        // 2. Nếu chưa có, tiến hành INSERT vào bảng coupons
        // Cấu hình mặc định: 
        // - Số lượng: 1000
        // - Hạn dùng: 1 năm
        // - Trạng thái: 1 (Active)
        // - Riêng tư: 1 (Chỉ dùng cho game)
        
        $sql = "INSERT INTO coupons (name, code, type, value, quantity, start_date, end_date, status, is_private, created_at) 
                VALUES (:name, :code, :type, :value, :qty, NOW(), :end_date, 1, 1, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'     => $data['name'],          // Tên coupon lấy theo tên giải thưởng
            ':code'     => $data['code'],
            ':type'     => $data['type'],          // 'money' hoặc 'percent'
            ':value'    => $data['value'],         // Giá trị (VD: 15000)
            ':qty'      => 1000,                   // Mặc định cho 1000 mã
            ':end_date' => date('Y-m-d H:i:s', strtotime('+1 year')) // Mặc định hạn 1 năm
        ]);
    }
}