<?php
namespace App\Models;

use App\Core\Model;

class StockIssue extends Model {

    // 1. Lấy danh sách phiếu xuất
    public function getAllIssues() {
        $sql = "SELECT si.*, u.full_name as user_name 
                FROM stock_issues si
                LEFT JOIN users u ON si.user_id = u.id
                ORDER BY si.created_at DESC";
        // Giả sử Model cha có method query hoặc dùng pdo
        return $this->db->query($sql)->fetchAll();
    }

    // 2. Lấy chi tiết 1 phiếu xuất (Header)
    public function getIssueById($id) {
        $sql = "SELECT si.*, u.full_name as user_name 
                FROM stock_issues si
                LEFT JOIN users u ON si.user_id = u.id
                WHERE si.id = ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 3. Lấy danh sách sản phẩm (Detail)
    public function getIssueDetails($issueId) {
        // Lưu ý: Cột trong DB là issue_id
        $sql = "SELECT sid.*, p.name as product_name 
                FROM stock_issue_details sid
                JOIN products p ON sid.product_id = p.id
                WHERE sid.issue_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$issueId]);
        return $stmt->fetchAll();
    }

    // 4. CHỨC NĂNG QUAN TRỌNG: TẠO PHIẾU XUẤT
    public function createIssue($user_id, $note, $items) {
        try {
            // Bắt đầu Transaction
            $this->db->beginTransaction();

            // --- BƯỚC 1: TẠO PHIẾU (HEADER) ---
            // PostgreSQL: Dùng RETURNING id để lấy ID vừa tạo ngay lập tức
            $sql = "INSERT INTO stock_issues (user_id, note, total_amount, created_at) 
                    VALUES (?, ?, 0, NOW()) 
                    RETURNING id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$user_id, $note]);
            
            // Lấy ID trả về từ câu lệnh INSERT
            $issueId = $stmt->fetchColumn();

            if (!$issueId) {
                throw new \Exception("Không thể tạo phiếu xuất (Lỗi Insert Header)");
            }

            $grandTotal = 0;

            // --- BƯỚC 2: CHUẨN BỊ SQL ---
            
            // a. Insert Chi tiết
            // QUAN TRỌNG: KHÔNG insert cột 'total' vì Postgres tự tính (Generated Column)
            // Cột khóa ngoại là 'issue_id'
            $sqlItem = "INSERT INTO stock_issue_details (issue_id, product_id, quantity, price) 
                        VALUES (?, ?, ?, ?)";
            $stmtItem = $this->db->prepare($sqlItem);

            // b. Trừ kho
            $sqlStock = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmtStock = $this->db->prepare($sqlStock);

            // c. Kiểm tra giá & tồn kho (để an toàn)
            $sqlCheck = "SELECT price, stock FROM products WHERE id = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);

            // --- BƯỚC 3: DUYỆT SẢN PHẨM ---
            foreach ($items as $item) {
                $prodId = $item['product_id'];
                $qty = $item['quantity'];

                // Kiểm tra tồn kho trước khi trừ
                $stmtCheck->execute([$prodId]);
                $prodData = $stmtCheck->fetch();

                if (!$prodData) throw new \Exception("Sản phẩm ID $prodId không tồn tại");
                if ($prodData['stock'] < $qty) throw new \Exception("Sản phẩm ID $prodId thiếu hàng");

                $price = $prodData['price']; // Lấy giá từ DB cho chính xác
                $grandTotal += ($qty * $price);

                // Thêm dòng chi tiết (Không gửi total)
                $stmtItem->execute([
                    $issueId,
                    $prodId,
                    $qty,
                    $price
                ]);

                // Trừ tồn kho
                $stmtStock->execute([
                    $qty,
                    $prodId
                ]);
            }

            // --- BƯỚC 4: CẬP NHẬT TỔNG TIỀN ---
            $sqlUpdateTotal = "UPDATE stock_issues SET total_amount = ? WHERE id = ?";
            $stmtUpdate = $this->db->prepare($sqlUpdateTotal);
            $stmtUpdate->execute([$grandTotal, $issueId]);

            // Xác nhận thành công
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            // Nếu lỗi, hoàn tác
            $this->db->rollBack();
            // Trả về chuỗi lỗi để Controller hiển thị
            return $e->getMessage();
        }
    }

    // 5. XÓA PHIẾU XUẤT (VÀ HOÀN TRẢ TỒN KHO)
    public function deleteIssue($id) {
        try {
            $this->db->beginTransaction();

            // 1. Lấy danh sách sản phẩm trong phiếu để hoàn trả tồn kho
            // (Phải lấy trước khi xóa phiếu)
            $sqlGetItems = "SELECT product_id, quantity FROM stock_issue_details WHERE issue_id = ?";
            $stmtGet = $this->db->prepare($sqlGetItems);
            $stmtGet->execute([$id]);
            $items = $stmtGet->fetchAll();

            // 2. Hoàn trả tồn kho (Cộng lại số lượng đã xuất)
            $sqlRestore = "UPDATE products SET stock = stock + ? WHERE id = ?";
            $stmtRestore = $this->db->prepare($sqlRestore);

            foreach ($items as $item) {
                $stmtRestore->execute([$item['quantity'], $item['product_id']]);
            }

            // 3. Xóa phiếu xuất
            // (Do bảng details có ON DELETE CASCADE như ảnh bạn gửi, nên chỉ cần xóa bảng cha stock_issues là bảng con tự mất)
            $sqlDelete = "DELETE FROM stock_issues WHERE id = ?";
            $stmtDelete = $this->db->prepare($sqlDelete);
            $stmtDelete->execute([$id]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return $e->getMessage();
        }
    }
}