<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Product extends Model {

    // Lấy tất cả sản phẩm
    public function getAll() {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY p.created_at DESC"); //ASC hoặc DESC
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm theo ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm theo danh mục
    public function getByCategory($categoryId) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   WHERE p.category_id = ? 
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY p.created_at DESC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm nổi bật (random)
    public function getFeatured($limit = 8) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY RANDOM() LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm mới nhất
    public function getNewestProducts($limit = 4) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY p.created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm random
    public function getRandomProducts($limit = 8) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY RANDOM() LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy tất cả sản phẩm với sort
    public function getAllProducts($sort = 'newest') {
        $orderBy = 'p.created_at DESC';
        if ($sort == 'price_asc') $orderBy = 'p.price ASC';
        elseif ($sort == 'price_desc') $orderBy = 'p.price DESC';

        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY $orderBy");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy sản phẩm theo danh mục với sort
    public function getProductsByCategory($categoryId, $sort = 'newest') {
        $orderBy = 'p.created_at DESC';
        if ($sort == 'price_asc') $orderBy = 'p.price ASC';
        elseif ($sort == 'price_desc') $orderBy = 'p.price DESC';

        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   WHERE p.category_id = ? 
                                   GROUP BY p.id, c.id, c.name
                                   ORDER BY $orderBy");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tìm kiếm sản phẩm
    public function search($query) {
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name, 
                                          COALESCE(ROUND(CAST(AVG(r.rating) AS NUMERIC), 1), 0) as avg_rating,
                                          COUNT(r.id) as review_count
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   LEFT JOIN reviews r ON p.id = r.product_id
                                   WHERE p.name ILIKE ? OR p.description ILIKE ?
                                   GROUP BY p.id, c.id, c.name");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo sản phẩm mới
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO products (category_id, name, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image'] ?? null
        ]);
    }

    // Cập nhật sản phẩm
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['stock'],
            $data['image'] ?? null,
            $id
        ]);
    }

    // Xóa sản phẩm
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Tăng stock
    public function increaseStock($productId, $quantity) {
        $sql = "UPDATE products SET stock = stock + :qty WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':qty' => $quantity,
            ':id' => $productId
        ]);
    }

    // Lấy danh sách tất cả danh mục (Cho Chatbot học)
    public function getAllCategories() {
        $sql = "SELECT name FROM categories ORDER BY id ASC";
        try {
            return $this->db->query($sql)->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    // Lấy 6 sản phẩm mới nhất cho chatbot
    public function getAllForChat() {
        $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 6";
        return $this->db->query($sql)->fetchAll();
    }

    // Get total products count
    public function getTotalProducts() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    // Get products with low stock (<= threshold)
    public function getLowStockProducts($threshold = 10) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE stock <= ? ORDER BY stock ASC");
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get revenue statistics
    public function getRevenueStats() {
        // Total orders and revenue
        $sql = "SELECT 
                    COUNT(DISTINCT id) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_revenue
                FROM orders 
                WHERE status IN ('completed', 'delivered')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $totalStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Today's orders and revenue
        $sqlToday = "SELECT 
                        COUNT(DISTINCT id) as today_orders,
                        COALESCE(SUM(total_amount), 0) as today_revenue
                    FROM orders 
                    WHERE CAST(created_at AS DATE) = CURRENT_DATE
                    AND status IN ('completed', 'delivered')";
        
        $stmtToday = $this->db->prepare($sqlToday);
        $stmtToday->execute();
        $todayStats = $stmtToday->fetch(PDO::FETCH_ASSOC);

        return [
            'total_orders' => $totalStats['total_orders'] ?? 0,
            'total_revenue' => $totalStats['total_revenue'] ?? 0,
            'today_orders' => $todayStats['today_orders'] ?? 0,
            'today_revenue' => $todayStats['today_revenue'] ?? 0
        ];
    }

    // Get top selling products
    public function getTopSellingProducts($limit = 5) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    COALESCE(SUM(oi.quantity), 0) as sold_quantity,
                    COALESCE(SUM(oi.quantity * oi.price), 0) as revenue
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id
                WHERE o.status IS NULL OR o.status IN ('completed', 'delivered')
                GROUP BY p.id, p.name, p.price
                ORDER BY sold_quantity DESC, revenue DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get inventory statistics (for Admin Chat)
    public function getInventoryStats() {
        try {
            // Total products and low stock count
            $total = $this->db->query("SELECT COUNT(*) as count FROM products")->fetch(PDO::FETCH_ASSOC);
            $lowStockProducts = $this->getLowStockProducts(10);
            $outOfStockProducts = $this->db->query("SELECT id, name, stock FROM products WHERE stock = 0")->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total_products' => $total['count'] ?? 0,
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts
            ];
        } catch (\Throwable $e) {
            error_log('[Product::getInventoryStats] ' . $e->getMessage());
            return [
                'total_products' => 0,
                'low_stock' => [],
                'out_of_stock' => []
            ];
        }
    }

    // Get products by array of IDs
    public function getProductsByIds($ids) {
        if (empty($ids)) {
            return [];
        }
        
        // Convert IDs to integers for safety
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt = $this->db->prepare("SELECT p.*, COALESCE(c.name, 'Khác') as category_name 
                                  FROM products p 
                                  LEFT JOIN categories c ON p.category_id = c.id 
                                  WHERE p.id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Smart search - Find products by category keyword or product keyword
     * Used by chatbot to return accurate products
     * 
     * @param string $keyword Category or product name keyword
     * @return array Products matching keyword
     */
    public function searchByKeyword($keyword) {
        try {
            // Escape keyword for LIKE query
            $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($keyword)) . '%';

            // Search both in product names and category names
            $sql = "SELECT p.*, COALESCE(c.name, 'Khác') as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE LOWER(p.name) LIKE LOWER(?) 
                       OR LOWER(c.name) LIKE LOWER(?)
                    ORDER BY c.name, p.name DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[Product::searchByKeyword] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products with full category information with pagination
     * 
     * @param int $limit Limit results
     * @param int $offset Offset for pagination
     * @return array Products with category details
     */
    public function getProductsWithCategory($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT p.*, c.id as category_id_full, c.name as category_name, c.description as category_description
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY c.name, p.name DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[Product::getProductsWithCategory] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find category by keyword and return its products
     * Useful for chatbot: "Danh mục lều nào?" -> Search category like "lều" -> Get all "lều" products
     * 
     * @param string $keyword Category keyword
     * @return array Products from matching categories
     */
    public function findProductsByCategory($keyword) {
        try {
            // Escape keyword
            $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], trim($keyword)) . '%';

            // First, find categories matching keyword
            $sql = "SELECT p.*, COALESCE(c.name, 'Khác') as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE LOWER(c.name) LIKE LOWER(?)
                    ORDER BY p.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[Product::findProductsByCategory] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all products grouped by category (for chatbot category listing)
     * Returns products organized by their categories
     * 
     * @return array Associative array with category names as keys and products as values
     */
    public function getProductsByCategories() {
        try {
            $sql = "SELECT p.*, COALESCE(c.name, 'Khác') as category_name, c.id as category_id
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    ORDER BY c.name, p.name ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group products by category
            $grouped = [];
            foreach ($products as $product) {
                $category = $product['category_name'] ?? 'Khác';
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $product;
            }

            return $grouped;
        } catch (\Throwable $e) {
            error_log('[Product::getProductsByCategories] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get category with product count
     * Useful for chatbot to show how many items in each category
     * 
     * @return array Categories with product counts
     */
    public function getCategoriesWithProductCount() {
        try {
            $sql = "SELECT c.id, c.name, COUNT(p.id) as product_count
                    FROM categories c 
                    LEFT JOIN products p ON c.id = p.category_id 
                    GROUP BY c.id, c.name
                    ORDER BY c.name ASC";
            
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[Product::getCategoriesWithProductCount] ' . $e->getMessage());
            return [];
        }
    }
}
