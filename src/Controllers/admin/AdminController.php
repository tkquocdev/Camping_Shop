<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class AdminController extends Controller {

    public function dashboard() {
        // Check if admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /auth/login");
            exit;
        }

        // Get dashboard statistics
        $orderModel = $this->model('Order');
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');
        $userModel = $this->model('User');

        // Revenue statistics
        $totalRevenue = $orderModel->getTotalRevenue();
        $todayRevenue = $orderModel->getRevenueByDate(date('Y-m-d'));
        $monthlyRevenue = $orderModel->getRevenueByMonth(date('Y'), date('m'));

        // Order statistics
        $totalOrders = $orderModel->getTotalOrders();
        $pendingOrders = $orderModel->getOrdersByStatus('pending');
        $processingOrders = $orderModel->getOrdersByStatus('processing');
        $shippedOrders = $orderModel->getOrdersByStatus('shipped');
        $deliveredOrders = $orderModel->getOrdersByStatus('delivered');
        $completedOrders = $orderModel->getOrdersByStatus('completed');
        $cancelledOrders = $orderModel->getOrdersByStatus('cancelled');

        // Product and category stats
        $totalProducts = $productModel->getTotalProducts();
        $totalCategories = $categoryModel->getTotalCategories();
        $lowStockProducts = $productModel->getLowStockProducts(5); // Products with stock <= 5

        // User statistics
        $totalUsers = $userModel->getTotalUsers();
        $newUsersToday = $userModel->getNewUsersToday();

        // Recent orders (last 10)
        $recentOrders = $orderModel->getRecentOrders(10);

        // Revenue chart data (last N days: default 30)
        $revenueDays = 30;
        if (isset($_GET['revenue_days']) && in_array((int)$_GET['revenue_days'], [7, 30], true)) {
            $revenueDays = (int)$_GET['revenue_days'];
        }
        $revenueChartData = $orderModel->getRevenueChartData($revenueDays);

        // Monthly revenue for chart
        $monthlyChartData = $orderModel->getMonthlyRevenueChartData(12);

        $data = [
            'page_title' => 'Admin Dashboard',
            'active' => 'dashboard',

            // Statistics
            'total_revenue' => $totalRevenue,
            'today_revenue' => $todayRevenue,
            'monthly_revenue' => $monthlyRevenue,

            'total_orders' => $totalOrders,
            'pending_orders' => count($pendingOrders),
            'processing_orders' => count($processingOrders),
            'shipped_orders' => count($shippedOrders),
            'delivered_orders' => count($deliveredOrders),
            'completed_orders' => count($completedOrders),
            'cancelled_orders' => count($cancelledOrders),

            'total_products' => $totalProducts,
            'total_categories' => $totalCategories,
            'low_stock_count' => count($lowStockProducts),

            'total_users' => $totalUsers,
            'new_users_today' => $newUsersToday,

            // Data for charts and tables
            'recent_orders' => $recentOrders,
            'revenue_chart_data' => json_encode($revenueChartData ?? []),
            'monthly_chart_data' => json_encode($monthlyChartData ?? []),
            'revenue_days' => $revenueDays,
            'low_stock_products' => $lowStockProducts
        ];

        $this->view('admin/dashboard/index', $data);
    }
}