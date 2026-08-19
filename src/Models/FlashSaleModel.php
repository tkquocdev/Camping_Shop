<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class FlashSaleModel extends Model {

    // Lấy tất cả flash sale (có phân trang) + tính tổng số lượng bán
    public function getAll($limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT 
                fs.*,
                COALESCE(SUM(fsi.sold), 0) as total_sold,
                COALESCE(SUM(fsi.quantity), 0) as total_quantity
            FROM flash_sales fs
            LEFT JOIN flash_sale_items fsi ON fs.id = fsi.flash_sale_id
            GROUP BY fs.id
            ORDER BY fs.start_time DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số flash sale
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM flash_sales");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Lấy chi tiết flash sale theo ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM flash_sales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo flash sale mới
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO flash_sales (name, start_time, end_time, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $result = $stmt->execute([
            $data['name'],
            $data['start_time'],
            $data['end_time'],
            $data['status'] ?? 1
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    // Cập nhật flash sale
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE flash_sales 
            SET name = ?, start_time = ?, end_time = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['start_time'],
            $data['end_time'],
            $data['status'] ?? 1,
            $id
        ]);
    }

    // Xóa flash sale (CASCADE xóa items)
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM flash_sales WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ===== QUẢN LÝ ITEMS =====

    // Lấy items của flash sale (kèm tên sản phẩm, ảnh, giá gốc)
    public function getItemsBySaleId($saleId) {
        $stmt = $this->db->prepare("
            SELECT fsi.*, p.name as product_name, p.image as product_image, p.price as original_price
            FROM flash_sale_items fsi
            JOIN products p ON fsi.product_id = p.id
            WHERE fsi.flash_sale_id = ?
            ORDER BY fsi.id DESC
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết item
    public function getItemDetail($itemId) {
        $stmt = $this->db->prepare("
            SELECT fsi.*, p.name as product_name, p.image as product_image, p.price as original_price
            FROM flash_sale_items fsi
            JOIN products p ON fsi.product_id = p.id
            WHERE fsi.id = ?
        ");
        $stmt->execute([$itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm item vào flash sale
    public function addItem($data) {
        $stmt = $this->db->prepare("
            INSERT INTO flash_sale_items (flash_sale_id, product_id, sale_price, quantity, sold) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['flash_sale_id'],
            $data['product_id'],
            $data['sale_price'],
            $data['quantity'],
            $data['sold'] ?? 0
        ]);
    }

    // Xóa item khỏi flash sale
    public function deleteItem($itemId) {
        $stmt = $this->db->prepare("DELETE FROM flash_sale_items WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    // Kiểm tra sản phẩm có tồn tại trong flash sale không
    public function checkProductInSale($saleId, $productId) {
        $stmt = $this->db->prepare("
            SELECT id FROM flash_sale_items 
            WHERE flash_sale_id = ? AND product_id = ?
        ");
        $stmt->execute([$saleId, $productId]);
        return $stmt->fetch() !== false;
    }

    // Lấy đợt flash sale đang diễn ra
    public function getActiveFlashSale() {
        $stmt = $this->db->prepare("SELECT * FROM flash_sales WHERE start_time <= NOW() AND end_time > NOW() AND status = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy items của flash sale đang diễn ra (dùng cho client)
    public function getFlashSaleItems($flashSaleId) {
        $stmt = $this->db->prepare("
            SELECT fsi.*, p.name, p.image, p.price as original_price
            FROM flash_sale_items fsi
            JOIN products p ON fsi.product_id = p.id
            WHERE fsi.flash_sale_id = ? AND fsi.quantity > fsi.sold
        ");
        $stmt->execute([$flashSaleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cập nhật số lượng đã bán
    public function updateSoldQuantity($itemId, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE flash_sale_items 
            SET sold = sold + ? 
            WHERE id = ?
        ");
        return $stmt->execute([$quantity, $itemId]);
    }

    // Kiểm tra sản phẩm có trong flash sale đang diễn ra không
    public function checkProductInFlashSale($saleId, $productId) {
        $stmt = $this->db->prepare("
            SELECT fsi.* FROM flash_sale_items fsi
            WHERE fsi.flash_sale_id = ? AND fsi.product_id = ?
        ");
        $stmt->execute([$saleId, $productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
