<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model {

    // Tìm user theo username
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tìm user theo email (nếu cần)
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo user mới
    public function create($data) {
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $data['username'],
                $data['full_name'],
                $data['email'] ?? null,
                $data['password'],
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role'] ?? 'customer'
            ]);
        } catch (\Exception $e) {
            error_log("User create error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật user
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([
            $data['full_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $id
        ]);
    }

    // Tìm user theo ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lưu OTP cho quên mật khẩu
    public function savePasswordResetToken($username, $token) {
        // Xóa token cũ
        $this->db->prepare("DELETE FROM password_resets WHERE username = ?")->execute([$username]);
        // Thêm token mới
        $stmt = $this->db->prepare("INSERT INTO password_resets (username, token) VALUES (?, ?)");
        return $stmt->execute([$username, $token]);
    }

    // Kiểm tra OTP
    public function verifyPasswordResetToken($username, $token) {
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE username = ? AND token = ? AND created_at > NOW() - INTERVAL '15 minutes'");
        $stmt->execute([$username, $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật mật khẩu theo ID
    public function updatePassword($username, $password) {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE username = ?");
        return $stmt->execute([$password, $username]);
    }

    // Cập nhật mật khẩu theo ID (cho forgot password)
    public function updatePasswordById($id, $password) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $password,
            ':id' => $id
        ]);
    }

    // Xóa token sau khi dùng
    public function deletePasswordResetToken($username) {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE username = ?");
        return $stmt->execute([$username]);
    }

    // Cập nhật thông tin cá nhân (Họ tên, SĐT)
    public function updateProfile($id, $fullname, $phone) {
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':full_name' => $fullname,
                ':phone'     => $phone,
                ':id'        => $id
            ]);
        } catch (\Exception $e) {
            error_log("User::updateProfile error: " . $e->getMessage());
            return false;
        }
    }

    // Đổi mật khẩu
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':password' => password_hash($newPassword, PASSWORD_BCRYPT), // Mã hóa pass mới
            ':id'       => $id
        ]);
    }

    // Cập nhật Avatar
    public function updateAvatar($id, $avatarFilename) {
        $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':avatar' => $avatarFilename,
            ':id'     => $id
        ]);
    }

    // Alias cho find
    public function getUserById($id) {
        return $this->findById($id);
    }

    // Get total users count (bao gồm cả admin)
    public function getTotalUsers() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    // Get new users today
    public function getNewUsersToday() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURRENT_DATE AND role = 'customer'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    // Get all users for admin management
        // Lấy tất cả người dùng
        public function getAllUsers() {
            $stmt = $this->db->prepare("SELECT * FROM users");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update user comprehensive (for admin)
    public function updateUserAdmin($id, $data) {
        try {
            $fullName = $data['full_name'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $address = $data['address'] ?? '';
            $role = $data['role'] ?? 'customer';

            $sql = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address, role = :role WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':role' => $role,
                ':id' => $id
            ]);
        } catch (\Exception $e) {
            error_log("User::updateUserAdmin error: " . $e->getMessage());
            return false;
        }
    }

    // Delete user
    public function deleteUser($id) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (\Exception $e) {
            error_log("User::deleteUser error: " . $e->getMessage());
            return false;
        }
    }

    // Ban or unban user
    public function banUser($id, $isBanned) {
        try {
            $status = $isBanned ? 'banned' : 'active';
            $sql = "UPDATE users SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        } catch (\Exception $e) {
            error_log("User::banUser error: " . $e->getMessage());
            return false;
        }
    }
}