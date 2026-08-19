<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Order extends Model {
    // Get order count by status
    public function getOrderCountByStatus($status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = ?");
        $stmt->execute([$status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }
// USER

// 1. Tạo đơn hàng mới
public function createOrder(
    $userId,
    $totalAmount,
    $address,
    $phone,
    $note,
    $paymentMethod,
    $discountAmount,
    $shippingFee,
    $couponCode = null
) {
    $sql = "
        INSERT INTO orders
        (user_id, total_amount, status, shipping_address, phone, note,
            payment_method, discount_amount, shipping_fee, coupon_code, created_at)
        VALUES
        (:uid, :total, 'pending', :address, :phone, :note,
            :method, :discount, :ship, :coupon, NOW())
        RETURNING id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':uid'      => $userId,
        ':total'    => $totalAmount,
        ':address'  => $address,
        ':phone'    => $phone,
        ':note'     => $note,
        ':method'   => $paymentMethod,
        ':discount' => $discountAmount,
        ':ship'     => $shippingFee,
        ':coupon'   => $couponCode
    ]);

    return (int)$stmt->fetchColumn();
}

// 2. Lưu chi tiết sản phẩm
public function createOrderItem($orderId, $productId, $quantity, $price) {
    $sql = "
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (:oid, :pid, :qty, :price)
    ";

    return $this->db->prepare($sql)->execute([
        ':oid'   => $orderId,
        ':pid'   => $productId,
        ':qty'   => $quantity,
        ':price' => $price
    ]);
}

// 3. Đơn hàng của user
public function getOrdersByUserId($userId) {
    $sql = "
        SELECT *
        FROM orders
        WHERE user_id = :uid
        ORDER BY created_at DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':uid' => $userId]);

    return $stmt->fetchAll();
}

// 4. Chi tiết đơn hàng
public function getOrderById($id) {
    $sql = "
        SELECT o.*, u.full_name, u.email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        WHERE o.id = :id
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);

    return $stmt->fetch();
}

// 5. Sản phẩm trong đơn
public function getOrderItems($orderId) {
    $sql = "
        SELECT oi.*, p.name, p.image
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = :oid
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':oid' => $orderId]);

    return $stmt->fetchAll();
}

// 5.5 Tính tổng giá gốc sản phẩm (không tính discount/shipping)
public function getOrderItemSubtotal($orderId) {
    $sql = "
        SELECT COALESCE(SUM(quantity * price), 0) as subtotal
        FROM order_items
        WHERE order_id = :oid
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':oid' => $orderId]);
    $result = $stmt->fetch();

    return (int)$result['subtotal'];
}

// 6. Hủy đơn (chỉ pending) - Hoàn lại stock
public function cancelOrder($orderId, $userId) {
    try {
        $this->db->beginTransaction();
        
        // 1. Lấy danh sách sản phẩm trong đơn
        $sql = "SELECT product_id, quantity FROM order_items WHERE order_id = :oid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':oid' => $orderId]);
        $items = $stmt->fetchAll();
        
        // 2. Hoàn lại stock cho mỗi sản phẩm
        foreach ($items as $item) {
            $updateSql = "UPDATE products SET stock = stock + :qty WHERE id = :pid";
            $this->db->prepare($updateSql)->execute([
                ':qty' => $item['quantity'],
                ':pid' => $item['product_id']
            ]);
        }
        
        // 3. Cập nhật status đơn thành cancelled
        $cancelSql = "
            UPDATE orders
            SET status = 'cancelled'
            WHERE id = :id
                AND user_id = :uid
                AND status = 'pending'
        ";
        
        $result = $this->db->prepare($cancelSql)->execute([
            ':id'  => $orderId,
            ':uid' => $userId
        ]);
        
        $this->db->commit();
        return $result;
        
    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log("Error cancelling order: " . $e->getMessage());
        return false;
    }
}

// ADMIN

public function updateStatus($orderId, $status)
{
    // When status changes to 'completed', set the completed_at timestamp
    if ($status === 'completed') {
        $sql = "UPDATE orders SET status = :status, completed_at = NOW() WHERE id = :id";
    } else {
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
    }

    return $this->db->prepare($sql)->execute([
        ':status' => $status,
        ':id'     => $orderId
    ]);
}

public function getAllOrders()
{
    $sql = "
        SELECT o.*, u.full_name, u.email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC
    ";

    return $this->db->query($sql)->fetchAll();
}

public function getOrdersByStatus($status)
{
    $sql = "
        SELECT o.*, u.full_name AS user_name, u.email
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        WHERE o.status = :status
        ORDER BY o.created_at DESC
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':status' => $status]);

    return $stmt->fetchAll();
}

public function requestReturn($orderId, $reason, $description)
{
    $sql = "
        UPDATE orders
        SET status = 'return_requested',
            return_reason = :reason,
            return_description = :desc
        WHERE id = :id
    ";

    return $this->db->prepare($sql)->execute([
        ':reason' => $reason,
        ':desc'   => $description,
        ':id'     => $orderId
    ]);
}

//ADMIN - Dashboard

public function getAdminStats() {
    try {
        $sql = "
            SELECT
                COALESCE(SUM(
                    CASE
                        WHEN created_at::date = CURRENT_DATE
                            AND status IN ('completed','delivered')
                        THEN total_amount ELSE 0
                    END
                ),0) AS revenue_today,

                COALESCE(SUM(
                    CASE
                        WHEN DATE_TRUNC('month', created_at)
                                = DATE_TRUNC('month', CURRENT_DATE)
                            AND status IN ('completed','delivered')
                        THEN total_amount ELSE 0
                    END
                ),0) AS revenue_month,

                COUNT(*) FILTER (WHERE status = 'pending') AS pending_orders,

                COUNT(*) FILTER (
                    WHERE DATE_TRUNC('month', created_at)
                            = DATE_TRUNC('month', CURRENT_DATE)
                ) AS month_orders
            FROM orders
        ";

        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

    } catch (\Throwable $e) {
        error_log('[Order::getAdminStats] ' . $e->getMessage());
        return [
            'revenue_today'  => 0,
            'revenue_month'  => 0,
            'pending_orders' => 0,
            'month_orders'   => 0
        ];
    }
}

public function getTopSelling() {
    $sql = "
        SELECT p.name, SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        GROUP BY p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ";

    try {
        return $this->db->query($sql)->fetchAll();
    } catch (\Throwable $e) {
        error_log('[Order::getTopSelling] ' . $e->getMessage());
        return [];
    }
}

public function getTopCustomers() {
    $sql = "
        SELECT u.full_name, SUM(o.total_amount) AS total_spent
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.status IN ('completed','delivered')
        GROUP BY u.full_name
        ORDER BY total_spent DESC
        LIMIT 5
    ";

    try {
        return $this->db->query($sql)->fetchAll();
    } catch (\Throwable $e) {
        error_log('[Order::getTopCustomers] ' . $e->getMessage());
        return [];
    }
}

public function getActiveCoupons() {
    $sql = "
        SELECT code, discount_value
        FROM coupons
        WHERE expiration_date >= CURRENT_DATE
    ";

    try {
        return $this->db->query($sql)->fetchAll();
    } catch (\Throwable $e) {
        error_log('[Order::getActiveCoupons] ' . $e->getMessage());
        return [];
    }
}


public function markAsRewarded($orderId) {
    $sql = "UPDATE orders SET is_rewarded = 1 WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $orderId]);
}

// Lấy tổng doanh thu (chỉ tính đơn completed)
public function getTotalRevenue() {
    $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['total'];
}

// Lấy doanh thu theo ngày (chỉ tính đơn completed, dùng completed_at nếu có, ngược lại dùng created_at)
public function getRevenueByDate($date) {
    $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed' AND DATE(COALESCE(completed_at, created_at)) = ?::date");
    $stmt->execute([$date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['total'];
}

// Lấy doanh thu theo tháng (chỉ tính đơn completed, dùng completed_at nếu có, ngược lại dùng created_at)
public function getRevenueByMonth($year, $month) {
    $stmt = $this->db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed' AND EXTRACT(YEAR FROM COALESCE(completed_at, created_at)) = ? AND EXTRACT(MONTH FROM COALESCE(completed_at, created_at)) = ?");
    $stmt->execute([$year, $month]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (float) $result['total'];
}

// Lấy tổng số đơn hàng (tất cả trạng thái)
public function getTotalOrders() {
    $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) $result['total'];
}

public function getRecentOrders($limit = 10) {
    $stmt = $this->db->prepare("
        SELECT o.*, u.full_name, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy dữ liệu biểu đồ doanh thu cho N ngày gần nhất (sử dụng completed_at nếu có, sau đó là created_at)
public function getRevenueChartData($days = 30) {
    $stmt = $this->db->prepare("
        SELECT
            DATE(COALESCE(completed_at, created_at)) as date,
            COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE COALESCE(completed_at, created_at) >= CURRENT_DATE - (? || ' days')::interval
            AND status = 'completed'
        GROUP BY DATE(COALESCE(completed_at, created_at))
        ORDER BY DATE(COALESCE(completed_at, created_at))
    ");
    $stmt->execute([$days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy dữ liệu biểu đồ doanh thu theo tháng cho N tháng gần nhất (sử dụng completed_at nếu có, sau đó là created_at)
public function getMonthlyRevenueChartData($months = 12) {
    $stmt = $this->db->prepare("
        SELECT
            TO_CHAR(COALESCE(completed_at, created_at), 'YYYY-MM') as month,
            COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE COALESCE(completed_at, created_at) >= CURRENT_DATE - (? || ' months')::interval
            AND status = 'completed'
        GROUP BY TO_CHAR(COALESCE(completed_at, created_at), 'YYYY-MM')
        ORDER BY TO_CHAR(COALESCE(completed_at, created_at), 'YYYY-MM')
    ");
    $stmt->execute([$months]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}