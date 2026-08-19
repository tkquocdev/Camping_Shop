<?php
namespace App\Models;

use App\Core\Model;

class Supplier extends Model {
    
    // Lấy tất cả nhà cung cấp
    public function getAll() {
        $sql = "SELECT * FROM suppliers ORDER BY id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    // Lấy 1 nhà cung cấp theo ID
    public function getById($id) {
        $sql = "SELECT * FROM suppliers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Thêm mới
    public function create($name, $phone, $email, $address) {
        $sql = "INSERT INTO suppliers (name, phone, email, address) 
                VALUES (:name, :phone, :email, :address)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':address' => $address
        ]);
    }

    // Cập nhật
    public function update($id, $name, $phone, $email, $address) {
        $sql = "UPDATE suppliers 
                SET name = :name, phone = :phone, email = :email, address = :address 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':phone' => $phone,
            ':email' => $email,
            ':address' => $address
        ]);
    }

    // Xóa (Lưu ý: Nếu đã có phiếu nhập kho gắn với NCC này thì có thể sẽ lỗi khóa ngoại)
    public function delete($id) {
        $sql = "DELETE FROM suppliers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}