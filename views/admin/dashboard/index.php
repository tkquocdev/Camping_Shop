<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Camping Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            min-width: 280px;
            background-color: #343a40;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 300px;
            padding: 20px;
            min-height: 100vh;
        }
        @media (max-width: 992px) {
            .sidebar {
                position: relative;
                height: auto;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            min-height: 120px;
            display: flex;
            align-items: center;
        }
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .stats-label {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
            white-space: nowrap;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .recent-orders {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .order-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'dashboard'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <!-- Statistics Cards Row -->
        <div class="row mb-4">
            <!-- Total Revenue -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number" title="<?= number_format($total_revenue) ?> VNĐ">
                                <?= number_format($total_revenue) ?> VNĐ
                            </div>
                            <div class="stats-label">Tổng doanh thu</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-shopping-cart"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($total_orders) ?>
                            </div>
                            <div class="text-muted small">Tổng đơn hàng</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($total_products) ?>
                            </div>
                            <div class="text-muted small">Tổng sản phẩm</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($total_users) ?>
                            </div>
                            <div class="text-muted small">Tổng người dùng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row mb-4">
            <!-- Today's Revenue -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($today_revenue) ?> VNĐ
                            </div>
                            <div class="text-muted small">Doanh thu hôm nay</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-calendar-alt"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($monthly_revenue) ?> VNĐ
                            </div>
                            <div class="text-muted small">Doanh thu tháng này</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($total_categories) ?>
                            </div>
                            <div class="text-muted small">Danh mục sản phẩm</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fa-solid fa-exclamation-triangle"></i>
                        </div>
                        <div class="ms-3 flex-grow-1">
                            <div class="stats-number">
                                <?= number_format($low_stock_count) ?>
                            </div>
                            <div class="text-muted small">Sản phẩm sắp hết</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <!-- 7 Days Revenue Chart -->
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-line me-2"></i>
                        Doanh thu 7 ngày gần đây
                    </h5>
                    <canvas id="chart7Days" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- 30 Days Revenue Chart -->
            <div class="col-lg-6 mb-4">
                <div class="chart-container">
                    <h5 class="mb-3">
                        <i class="fa-solid fa-chart-line me-2"></i>
                        Doanh thu 30 ngày gần đây
                    </h5>
                    <canvas id="chart30Days" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Orders and Low Stock -->
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-8 mb-4">
                <div class="recent-orders">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-clock me-2"></i>
                            Đơn hàng gần đây
                        </h5>
                        <a href="/admin/orders" class="btn btn-primary btn-sm">
                            Xem tất cả
                            <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="/admin/orders/detail/<?= $order['id'] ?>" class="text-decoration-none">
                                            #<?= $order['id'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($order['full_name'] ?? 'N/A') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($order['email'] ?? '') ?></small>
                                    </td>
                                    <td class="fw-semibold text-success">
                                        <?= number_format($order['total_amount']) ?> VNĐ
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($order['status']) {
                                            'pending'    => 'status-pending',
                                            'processing' => 'status-processing',
                                            'shipped'    => 'status-shipped',
                                            'delivered'  => 'status-delivered',
                                            'completed'  => 'status-delivered',
                                            'cancelled'  => 'status-cancelled',
                                            default      => 'status-pending'
                                        };
                                        $statusText = match($order['status']) {
                                            'pending'    => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipped'    => 'Đang giao hàng',
                                            'delivered'  => 'Đã giao',
                                            'completed'  => 'Hoàn thành',
                                            'cancelled'  => 'Đã hủy',
                                            default      => 'Chờ xử lý'
                                        };
                                        ?>
                                        <span class="order-status <?= $statusClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="/admin/orders/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Xem chi tiết đơn hàng">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-lg-4 mb-4">
                <div class="recent-orders">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-exclamation-triangle me-2 text-danger"></i>
                            Sản phẩm sắp hết hàng
                        </h5>
                        <a href="/admin/products" class="btn btn-danger btn-sm">
                            Xem tất cả
                            <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <?php if (empty($low_stock_products)): ?>
                        <div class="text-center py-4">
                            <i class="fa-solid fa-check-circle text-success fa-3x mb-3"></i>
                            <p class="text-muted">Tất cả sản phẩm đều còn hàng</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($low_stock_products, 0, 5) as $product): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <img src="/uploads/products/<?= $product['image'] ?>" 
                                         class="rounded me-3" width="50" height="50" alt="Product"
                                         style="object-fit: cover;"
                                         onerror="this.src='https://placehold.co/50x50?text=No+Image'">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </div>
                                        <div class="text-muted small">
                                            Còn lại: <span class="text-danger fw-bold"><?= $product['stock'] ?></span> sản phẩm
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get real revenue data from database (passed from PHP controller as JSON)
        const revenueChartData = <?= $revenue_chart_data ?? '[]' ?>;
        
        // Helper function to format date from YYYY-MM-DD to dd/mm
        function formatDateShort(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1];
        }

        // Prepare data for 7-day chart (last 7 days)
        function prepare7DayData() {
            const last7 = revenueChartData.slice(-7);
            return {
                labels: last7.map(item => formatDateShort(item.date)),
                data: last7.map(item => parseInt(item.revenue) || 0)
            };
        }

        // Prepare data for 30-day chart (all available data)
        function prepare30DayData() {
            return {
                labels: revenueChartData.map(item => formatDateShort(item.date)),
                data: revenueChartData.map(item => parseInt(item.revenue) || 0)
            };
        }

        // Get chart data
        const data7Days = prepare7DayData();
        const data30Days = prepare30DayData();

        // Chart 7 Days
        const ctx7 = document.getElementById('chart7Days').getContext('2d');
        new Chart(ctx7, {
            type: 'line',
            data: {
                labels: data7Days.labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data7Days.data,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });

        // Chart 30 Days
        const ctx30 = document.getElementById('chart30Days').getContext('2d');
        new Chart(ctx30, {
            type: 'bar',
            data: {
                labels: data30Days.labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data30Days.data,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: '#007bff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
