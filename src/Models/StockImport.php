<?php
namespace App\Models;

use App\Core\Model;

class StockImport extends Model {

    // Lấy danh sách nhà cung cấp
    public function getSuppliers() {
        $stmt = $this->db->prepare("SELECT * FROM suppliers ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy tất cả phiếu nhập (dùng cho quản trị)
    public function getAllImports() {
        $sql = "
            SELECT si.*, s.name AS supplier_name, u.full_name AS user_name
            FROM stock_imports si
            LEFT JOIN suppliers s ON si.supplier_id = s.id
            LEFT JOIN users u ON si.user_id = u.id
            ORDER BY si.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    // Lấy phiếu nhập theo ID
    public function getImportById($id) {
        $sql = "
            SELECT si.*, s.name AS supplier_name, u.full_name AS user_name
            FROM stock_imports si
            LEFT JOIN suppliers s ON si.supplier_id = s.id
            LEFT JOIN users u ON si.user_id = u.id
            WHERE si.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy chi tiết sản phẩm trong phiếu nhập
    public function getImportItems($importId) {
        $sql = "
            SELECT sii.*, p.name AS product_name
            FROM stock_import_items sii
            LEFT JOIN products p ON sii.product_id = p.id
            WHERE sii.import_id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$importId]);
        return $stmt->fetchAll();
    }

    /**
     * Xóa phiếu nhập và Hoàn tác tồn kho (Trừ ngược lại số lượng đã nhập)
     */
    // ... (Giữ nguyên các hàm bên trên) ...

    public function createImport($data, $items) {
        try {
            // 1. Bắt đầu Transaction
            $this->db->beginTransaction();

            // 2. Tạo Phiếu Nhập (Bảng: stock_imports)
            // Lưu ý: Đảm bảo Session user có tồn tại, nếu không lấy mặc định là 1 (Admin)
            $userId = $_SESSION['user']['id'] ?? 1;
            
            $sql = "INSERT INTO stock_imports (supplier_id, user_id, note, total_amount, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['supplier_id'],
                $userId,
                $data['note'],
                0 // Tạm để 0, lát tính tổng cập nhật sau
            ]);
            
            // Lấy ID phiếu vừa tạo (PostgreSQL/MySQL đều dùng hàm này qua PDO)
            $importId = $this->db->lastInsertId();
            $grandTotal = 0;

            // 3. Chuẩn bị câu SQL cho bảng Chi tiết (Bảng: stock_import_items)
            // Cột đúng: import_id, product_id, quantity, import_price, total
            $sqlItem = "INSERT INTO stock_import_items (import_id, product_id, quantity, import_price, total) 
                        VALUES (?, ?, ?, ?, ?)";
            $stmtItem = $this->db->prepare($sqlItem);

            // 4. Chuẩn bị câu SQL cập nhật kho (Bảng: products)
            $sqlStock = "UPDATE products SET stock = stock + ? WHERE id = ?";
            $stmtStock = $this->db->prepare($sqlStock);

            // 5. Duyệt qua từng sản phẩm để insert
            foreach ($items as $item) {
                $qty = $item['quantity'];
                $price = $item['price'];
                $total = $qty * $price;
                $grandTotal += $total;

                // Thực thi thêm dòng chi tiết
                $stmtItem->execute([
                    $importId,
                    $item['product_id'],
                    $qty,
                    $price,
                    $total
                ]);

                // Thực thi cộng tồn kho
                $stmtStock->execute([
                    $qty,
                    $item['product_id']
                ]);
            }

            // 6. Cập nhật lại Tổng tiền cho phiếu nhập
            $sqlUpdateTotal = "UPDATE stock_imports SET total_amount = ? WHERE id = ?";
            $this->db->prepare($sqlUpdateTotal)->execute([$grandTotal, $importId]);

            // Xác nhận lưu vào DB
            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            // Nếu có lỗi, hoàn tác tất cả
            $this->db->rollBack();
            // Ghi log để bạn debug nếu cần (xem trong file log của PHP/Server)
            error_log("Lỗi Nhập Kho: " . $e->getMessage()); 
            return false;
        }
    }


    public function deleteImport($id) {
        try {
            $this->db->beginTransaction();

            // 1. Lấy danh sách sản phẩm trong phiếu để trừ kho
            $items = $this->getImportItems($id);

            // 2. Trừ tồn kho (Revert Stock)
            $sqlStock = "UPDATE products SET stock = stock - ? WHERE id = ?";
            $stmtStock = $this->db->prepare($sqlStock);

            foreach ($items as $item) {
                $stmtStock->execute([$item['quantity'], $item['product_id']]);
            }

            // 3. Xóa chi tiết phiếu
            $this->db->prepare("DELETE FROM stock_import_items WHERE import_id = ?")->execute([$id]);

            // 4. Xóa phiếu nhập
            $this->db->prepare("DELETE FROM stock_imports WHERE id = ?")->execute([$id]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi xóa phiếu nhập: " . $e->getMessage());
            return false;
        }
    }
}