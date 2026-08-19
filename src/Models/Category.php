<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Category extends Model {

    // Lấy tất cả danh mục
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alias cho getAll để dùng chung trong controller
    public function getAllCategories() {
        return $this->getAll();
    }

    // Lấy danh mục theo ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo danh mục mới
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$data['name'], $data['description']]);
    }

    // Cập nhật danh mục
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'], $id]);
    }

    // Xóa danh mục
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get total categories count
    public function getTotalCategories() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM categories");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }
}
