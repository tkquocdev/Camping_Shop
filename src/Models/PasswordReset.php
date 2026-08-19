<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class PasswordReset extends Model {

    /**
     * Tạo token OTP cho email
     * Nếu email đã có token, thì cập nhật lại token và thời gian tạo
     */
    public function createToken($email, $otp) {
        // Kiểm tra xem email đã có token chưa
        $checkSql = "SELECT * FROM password_resets WHERE email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':email' => $email]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            // Cập nhật token cũ
            $sql = "UPDATE password_resets SET token = :token, created_at = NOW() WHERE email = :email";
        } else {
            // Tạo token mới
            $sql = "INSERT INTO password_resets (email, token, created_at) VALUES (:email, :token, NOW())";
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':email' => $email,
            ':token' => $otp
        ]);
    }

    /**
     * Xác minh token OTP
     * Token chỉ có hiệu lực trong vòng 15 phút (900 giây)
     */
    public function verifyToken($email, $otp) {
        $sql = "SELECT * FROM password_resets 
                WHERE email = :email 
                AND token = :token 
                AND created_at > NOW() - INTERVAL '15 minutes'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':token' => $otp
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Xóa token sau khi sử dụng hoặc hết hạn
     */
    public function deleteToken($email) {
        $sql = "DELETE FROM password_resets WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':email' => $email]);
    }

    /**
     * Xóa tất cả token hết hạn (giúp dọn dẹp DB)
     */
    public function deleteExpiredTokens() {
        $sql = "DELETE FROM password_resets WHERE created_at < NOW() - INTERVAL '1 day'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}
