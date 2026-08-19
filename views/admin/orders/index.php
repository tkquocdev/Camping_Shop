<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng - Camping Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
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
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
        .toast-notification {
            background: white;
            border-radius: 8px;
            padding: 16px 24px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            animation: slideIn 0.3s ease-out;
        }
        .toast-success {
            border-left: 4px solid #28a745;
            background: #f0f8f5;
        }
        .toast-error {
            border-left: 4px solid #dc3545;
            background: #fdf8f7;
        }
        .toast-success i { color: #28a745; }
        .toast-error i { color: #dc3545; }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
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
    </style>
</head>
<body>
    <div class="toast-container" id="toastContainer"></div>
    <div class="sidebar">
        <?php $active = 'orders'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Đơn hàng</h2>
            <div class="input-group" style="max-width: 400px;">
                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo mã đơn hàng...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="ordersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <?php foreach ($orders as $order): ?>
                            <tr class="order-row" data-order-id="<?= $order['id'] ?>">
                                <td><strong>#<?= $order['id'] ?></strong></td>
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
                                        'pending' => 'status-pending',
                                        'processing' => 'status-processing',
                                        'shipped' => 'status-shipped',
                                        'delivered' => 'status-delivered',
                                        'cancelled' => 'status-cancelled',
                                        default => 'status-pending'
                                    };
                                    $statusText = match($order['status']) {
                                        'pending' => 'Chờ xử lý',
                                        'processing' => 'Đang xử lý',
                                        'shipped' => 'Đang giao hàng',
                                        'delivered' => 'Đã giao hàng',
                                        'completed' => 'Hoàn thành',
                                        'cancelled' => 'Đã hủy',
                                        default => 'Chờ xử lý'
                                    };
                                    ?>
                                    <span class="order-status <?= $statusClass ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>
                                <td>
                                    <a href="/admin/orders/detail/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary me-2" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cập nhật trạng thái">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'processing')">Đang xử lý</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'shipped')">Đang giao hàng</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'delivered')">Đã giao hàng</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'completed')">Hoàn thành</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $order['id'] ?>, 'cancelled')">Đã hủy</a></li>
                                        </ul>
                                    </div>
                                    <?php if (in_array($order['status'], ['pending', 'cancelled'])): ?>
                                    <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteOrder(<?= $order['id'] ?>)" title="Xóa đơn hàng">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toastClass = type === 'success' ? 'toast-success' : 'toast-error';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            const toast = document.createElement('div');
            toast.className = `toast-notification ${toastClass}`;
            toast.innerHTML = `<i class="fa-solid ${icon}"></i><span>${message}</span>`;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function updateStatus(orderId, status) {
            const formData = new FormData();
            formData.append('status', status);
            
            fetch(`/admin/orders/update/${orderId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    showToast(data.message || 'Lỗi cập nhật', 'error');
                }
            })
            .catch(error => {
                showToast('Lỗi: ' + error, 'error');
            });
        }

        function deleteOrder(orderId) {
            if (confirm('Bạn có chắc muốn xóa đơn hàng này? Hành động này không thể hoàn tác.')) {
                window.location.href = `/admin/orders/delete/${orderId}`;
            }
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchBtn = document.getElementById('searchBtn');
            const orderRows = document.querySelectorAll('.order-row');

            function filterOrders() {
                const searchValue = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                orderRows.forEach(row => {
                    const orderId = row.dataset.orderId;
                    
                    if (searchValue === '' || orderId.includes(searchValue)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show message if no results
                const tbody = document.getElementById('ordersTableBody');
                let noResultsRow = document.getElementById('noResultsRow');
                
                if (visibleCount === 0 && searchValue !== '') {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.id = 'noResultsRow';
                        noResultsRow.innerHTML = '<td colspan="6" class="text-center text-muted py-4">Không tìm thấy đơn hàng nào</td>';
                        tbody.appendChild(noResultsRow);
                    }
                } else if (noResultsRow) {
                    noResultsRow.remove();
                }
            }

            searchInput.addEventListener('input', filterOrders);
            searchBtn.addEventListener('click', filterOrders);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    filterOrders();
                }
            });
        });
    </script>
</body>
</html>