<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class UserAddress extends Model {

    // Lấy tất cả địa chỉ của user
    public function getAllByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy địa chỉ mặc định của user
    public function getDefaultByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = TRUE LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy địa chỉ theo ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM user_addresses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo địa chỉ mới
    public function create($userId, $recipientName, $phone, $address, $isDefault = false) {
        try {
            // Nếu là mặc định, bỏ mặc định của các địa chỉ khác
            if ($isDefault) {
                $this->db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?")
                         ->execute([$userId]);
            }

            $stmt = $this->db->prepare("INSERT INTO user_addresses (user_id, recipient_name, phone, address, is_default) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$userId, $recipientName, $phone, $address, $isDefault]);
        } catch (\Exception $e) {
            error_log("UserAddress create error: " . $e->getMessage());
            return false;
        }
    }

    // Cập nhật địa chỉ
    public function updateAddress($id, $userId, $recipientName, $phone, $address, $isDefault = false) {
        try {
            // Nếu là mặc định, bỏ mặc định của các địa chỉ khác
            if ($isDefault) {
                $this->db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ? AND id != ?")
                         ->execute([$userId, $id]);
            }

            $stmt = $this->db->prepare("UPDATE user_addresses SET recipient_name = ?, phone = ?, address = ?, is_default = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
            return $stmt->execute([$recipientName, $phone, $address, $isDefault, $id, $userId]);
        } catch (\Exception $e) {
            error_log("UserAddress updateAddress error: " . $e->getMessage());
            return false;
        }
    }

    // Đặt địa chỉ mặc định
    public function setDefault($id, $userId) {
        try {
            // Bỏ mặc định tất cả địa chỉ của user
            $this->db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?")
                     ->execute([$userId]);

            // Đặt địa chỉ này là mặc định
            $stmt = $this->db->prepare("UPDATE user_addresses SET is_default = TRUE, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
            return $stmt->execute([$id, $userId]);
        } catch (\Exception $e) {
            error_log("UserAddress setDefault error: " . $e->getMessage());
            return false;
        }
    }

    // Xóa địa chỉ
    public function deleteAddress($id, $userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            return $stmt->execute([$id, $userId]);
        } catch (\Exception $e) {
            error_log("UserAddress delete error: " . $e->getMessage());
            return false;
        }
    }

    // Kiểm tra địa chỉ có thuộc về user không
    public function belongsToUser($id, $userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetchColumn() > 0;
    }
}