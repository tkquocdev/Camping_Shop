<?php
namespace App\Models;

use App\Core\Model;

class News extends Model {

    // Lấy tất cả tin tức (Mới nhất lên đầu)
    public function getAll() {
        $sql = "SELECT * FROM news ORDER BY created_at DESC";
        // Sử dụng fetchAll() mặc định của PDO (trả về mảng)
        return $this->db->query($sql)->fetchAll();
    }

    // Lấy chi tiết 1 tin tức theo ID
    public function getById($id) {
        $sql = "SELECT * FROM news WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Thêm tin tức mới
    public function create($data) {
        // PostgreSQL dùng NOW() để lấy thời gian hiện tại
        $sql = "INSERT INTO news (title, summary, content, image, created_at, updated_at) 
                VALUES (:title, :summary, :content, :image, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':title'   => $data['title'],
            ':summary' => $data['summary'],
            ':content' => $data['content'],
            ':image'   => $data['image']
        ]);
    }

    // Cập nhật tin tức
    public function update($id, $data) {
        $sql = "UPDATE news 
                SET title = :title, 
                    summary = :summary, 
                    content = :content, 
                    image = :image, 
                    updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':title'   => $data['title'],
            ':summary' => $data['summary'],
            ':content' => $data['content'],
            ':image'   => $data['image'],
            ':id'      => $id
        ]);
    }

    // Xóa tin tức
    public function delete($id) {
        $sql = "DELETE FROM news WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
