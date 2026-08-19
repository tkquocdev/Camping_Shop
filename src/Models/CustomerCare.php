<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class CustomerCare extends Model {

    // PHẦN 1: TICKET SYSTEM (Quản lý yêu cầu/khiếu nại)

    /**
     * Lấy danh sách tất cả các Ticket
     * Điều kiện: Có ticket_id HOẶC loại tương tác là khiếu nại/bảo hành
     */
    public function getAllTickets() {
        $sql = "SELECT c.*, 
                       u.full_name as customer_name, 
                       u.email as customer_email,
                       s.full_name as staff_name 
                FROM customer_care_logs c 
                JOIN users u ON c.customer_id = u.id 
                LEFT JOIN users s ON c.staff_id = s.id 
                WHERE c.ticket_id IS NOT NULL 
                   OR c.interaction_type IN ('Khiếu nại', 'Bảo hành', 'Ticket')
                   OR (c.interaction_type = 'Tuvan' AND c.status = 'Pending')
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách yêu cầu trả hàng từ contact form
     */
    public function getReturnRequests() {
        $sql = "SELECT c.*, 
                       u.full_name as customer_name, 
                       u.email as customer_email
                FROM customer_care_logs c 
                LEFT JOIN users u ON c.customer_id = u.id 
                WHERE c.interaction_type = 'Đổi trả/Bảo hành' 
                   AND c.status = 'Pending'
                ORDER BY c.created_at DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy lịch sử yêu cầu của một user
     */
    public function getUserRequests($userId) {
        $sql = "SELECT c.*, 
                       s.full_name as staff_name
                FROM customer_care_logs c 
                LEFT JOIN users s ON c.staff_id = s.id 
                WHERE c.customer_id = ?
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết một Ticket cụ thể theo ID
     */
    public function getTicketById($id) {
        $sql = "SELECT c.*, 
                       u.full_name as customer_name, 
                       u.email as customer_email, 
                       u.phone as phone_number,
                       s.full_name as staff_name
                FROM customer_care_logs c 
                LEFT JOIN users u ON c.customer_id = u.id 
                LEFT JOIN users s ON c.staff_id = s.id 
                WHERE c.id = :id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái Ticket & Ghi chú xử lý
     * Logic: Cập nhật status, gán staff_id, và nối thêm ghi chú vào content cũ
     */
    public function updateTicketStatus($id, $status, $staffId, $note = null) {
        // Câu lệnh SQL cơ bản
        $sql = "UPDATE customer_care_logs 
                SET status = ?, 
                    updated_at = NOW()";

        $params = [$status];

        // Chỉ set staff_id nếu có giá trị
        if ($staffId !== null) {
            $sql .= ", staff_id = ?";
            $params[] = $staffId;
        }

        // Nếu có ghi chú, nối thêm vào content cũ
        if (!empty($note)) {
            $sql .= ", content = content || '\n\n----------------\n[' || NOW() || '] Admin Note: ' || ?";
            $params[] = $note;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // PHẦN 2: CRM (Quản lý hồ sơ khách hàng)

    /**
     * Lấy danh sách khách hàng (User thường)
     * Kèm theo ngày tương tác gần nhất
     */
    public function getAllCustomers() {
        $sql = "SELECT u.*, 
                (SELECT MAX(created_at) FROM customer_care_logs WHERE customer_id = u.id) as last_interaction
                FROM users u 
                WHERE u.role != '1' 
                ORDER BY u.created_at DESC";
        
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin chi tiết một khách hàng
     */
    public function getCustomerById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật thông tin cá nhân khách hàng (SĐT, Địa chỉ)
     */
    public function updateCustomer($id, $data) {
        $sql = "UPDATE users SET phone = :phone, address = :address WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'phone'   => $data['phone'], 
            'address' => $data['address'], 
            'id'      => $id
        ]);
    }

    // PHẦN 3: LOGS & TẠO MỚI (Lịch sử chăm sóc)

    /**
     * Lấy toàn bộ lịch sử chăm sóc của một khách hàng
     */
    public function getLogs($customerId) {
        $sql = "SELECT c.*, s.full_name as staff_name 
                FROM customer_care_logs c 
                LEFT JOIN users s ON c.staff_id = s.id 
                WHERE c.customer_id = :cid 
                ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cid' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm Log lịch sử mới (Tư vấn, Gọi điện...)
     */
    public function addLog($data) {
        $sql = "INSERT INTO customer_care_logs (customer_id, staff_id, ticket_id, interaction_type, content, status, created_at) 
                VALUES (:customer_id, :staff_id, :ticket_id, :type, :content, :status, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'customer_id' => $data['customer_id'],
            'staff_id'    => $data['staff_id'],
            'ticket_id'   => $data['ticket_id'] ?? null,
            'type'        => $data['type'],
            'content'     => $data['content'],
            'status'      => $data['status'] ?? 'Completed'
        ]);
    }

    /**
     * Tạo Ticket mới (Khách hàng gửi yêu cầu)
     * Tự động sinh mã Ticket ID (Ví dụ: TK-170423...)
     */
    public function createTicket($userId, $content, $type = 'Khiếu nại') {
        // Sinh mã ticket ngẫu nhiên dựa trên thời gian
        $ticketCode = 'TK-' . time(); 

        $sql = "INSERT INTO customer_care_logs (customer_id, ticket_id, interaction_type, content, status, created_at) 
                VALUES (:id, :ticket_code, :type, :content, 'Pending', NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id'          => $userId,
                'ticket_code' => $ticketCode,
                'type'        => $type,
                'content'     => $content
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

        public function getHistoryByCustomerId($customerId) {
        // Logic: Lấy ticket_id và trạng thái mới nhất
        // Subquery original_content: Lấy dòng đầu tiên của ticket làm lý do
        $sql = "SELECT 
                    t.ticket_id,
                    MAX(t.created_at) as last_update,
                    
                    -- Cố gắng lấy nội dung đầu tiên của ticket
                    (SELECT content FROM customer_care_logs 
                     WHERE ticket_id = t.ticket_id 
                     ORDER BY id ASC LIMIT 1) as original_content,
                     
                    -- Lấy trạng thái mới nhất (dòng cuối cùng)
                    (SELECT status FROM customer_care_logs 
                     WHERE ticket_id = t.ticket_id 
                     ORDER BY id DESC LIMIT 1) as current_status,
                     
                    MAX(t.id) as latest_id
                FROM customer_care_logs t
                WHERE t.customer_id = :cid 
                  AND t.ticket_id IS NOT NULL 
                GROUP BY t.ticket_id
                ORDER BY last_update DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hàm xóa Ticket theo ID
    public function deleteTicket($id) {
        // Lấy ticket_id của dòng sắp xóa
        $stmt = $this->db->prepare("SELECT ticket_id FROM customer_care_logs WHERE id = ?");
        $stmt->execute([$id]);
        $ticket = $stmt->fetch();

        if ($ticket && $ticket['ticket_id']) {
            // Nếu có ticket_id, xóa TẤT CẢ lịch sử liên quan đến ticket này cho sạch sẽ
            $sql = "DELETE FROM customer_care_logs WHERE ticket_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ticket['ticket_id']]);
        } else {
            // Nếu là log lẻ không có ticket_id, chỉ xóa đúng dòng đó
            $sql = "DELETE FROM customer_care_logs WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        }
    }
}