<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Notification extends Model {

    /**
     * 1. Lấy danh sách thông báo (Kèm trạng thái đã đọc hay chưa)
     */
    public function getNotifications($userId, $limit = 10) {
        $sql = "SELECT n.id, n.title, n.message, n.link, n.type, n.created_at,
                       (CASE WHEN nr.read_at IS NOT NULL THEN 1 ELSE 0 END) as is_read
                FROM notifications n
                LEFT JOIN notification_reads nr 
                       ON n.id = nr.notification_id 
                       AND nr.user_id = :uid
                WHERE (n.user_id = :uid OR n.user_id IS NULL)
                ORDER BY n.created_at DESC
                LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 2. Đếm số lượng tin CHƯA ĐỌC
     */
    public function countUnread($userId) {
        $sql = "SELECT COUNT(*) as total 
                FROM notifications n
                WHERE (n.user_id = :uid OR n.user_id IS NULL)
                AND NOT EXISTS (
                    SELECT 1 FROM notification_reads nr 
                    WHERE nr.notification_id = n.id AND nr.user_id = :uid
                )";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * 3. Đánh dấu 1 tin là ĐÃ ĐỌC
     * Tối ưu: Dùng ON CONFLICT của PostgreSQL (Insert nếu chưa có, bỏ qua nếu có rồi)
     */
    public function markAsRead($userId, $notificationId) {
        $sql = "INSERT INTO notification_reads (user_id, notification_id, read_at) 
                VALUES (:uid, :nid, NOW())
                ON CONFLICT (user_id, notification_id) DO NOTHING";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':nid', $notificationId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 4. Đánh dấu TẤT CẢ là ĐÃ ĐỌC
     */
    public function markAllRead($userId) {
        // Logic: Tìm tất cả thông báo của user mà chưa có trong bảng reads, sau đó insert vào
        $sql = "INSERT INTO notification_reads (user_id, notification_id, read_at)
                SELECT :uid, n.id, NOW()
                FROM notifications n
                WHERE (n.user_id = :uid OR n.user_id IS NULL)
                ON CONFLICT (user_id, notification_id) DO NOTHING";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 5. Tạo thông báo mới (Dùng cho Admin hoặc System Trigger)
     */
    public function create($title, $content, $userId = null, $link = '#', $type = 'system') {
        $sql = "INSERT INTO notifications (title, message, user_id, link, type, created_at) 
                VALUES (:title, :message, :user_id, :link, :type, NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':message', $content);
            $stmt->bindValue(':user_id', $userId); // PDO tự xử lý NULL
            $stmt->bindValue(':link', $link);
            $stmt->bindValue(':type', $type);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create notification from array (for admin controller)
     */
    public function createFromArray($data) {
        $sql = "INSERT INTO notifications (title, message, user_id, link, type, created_at) 
                VALUES (:title, :message, :user_id, :link, :type, :created_at)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':message', $data['message']);
            $stmt->bindValue(':user_id', $data['user_id'] ?? null);
            $stmt->bindValue(':link', $data['link'] ?? '#');
            $stmt->bindValue(':type', $data['type'] ?? 'general');
            $stmt->bindValue(':created_at', $data['created_at'] ?? date('Y-m-d H:i:s'));
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get all notifications (for admin)
     */
    public function getAllNotifications() {
        $sql = "SELECT n.id, n.title, n.message, n.link, n.type, n.user_id, n.created_at
                FROM notifications n
                ORDER BY n.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Delete notification by ID
     */
    public function delete($id) {
        $sql = "DELETE FROM notifications WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}