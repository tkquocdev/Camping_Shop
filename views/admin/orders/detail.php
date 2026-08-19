<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Đơn hàng - Camping Shop</title>
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
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-returned { background-color: #f8d7da; color: #721c24; }
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
    </style>
</head>
<body>
    <div class="toast-container" id="toastContainer"></div>
    <div class="sidebar">
        <?php $active = 'orders'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Chi tiết Đơn hàng #<?= $order['id'] ?></h2>
            <div>
                <a href="/admin/orders" class="btn btn-secondary me-2">
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                <a href="/admin/orders/export_invoice/<?= $order['id'] ?>" class="btn btn-primary" target="_blank">
                    <i class="fa-solid fa-download me-2"></i>
                    Xuất PDF
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Thông tin đơn hàng -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Thông tin đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID Đơn hàng:</strong> #<?= $order['id'] ?></p>
                                <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['full_name'] ?? 'N/A') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'N/A') ?></p>
                                <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Trạng thái:</strong>
                                    <span class="order-status status-<?= $order['status'] ?>">
                                        <?php
                                        $statusText = match($order['status']) {
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'shipped' => 'Đang giao hàng',
                                            'delivered' => 'Đã giao hàng',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy',
                                            'returned' => 'Đã trả hàng',
                                            default => 'Không xác định'
                                        };
                                        echo $statusText;
                                        ?>
                                    </span>
                                </p>
                                <p><strong>Phương thức thanh toán:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                                <p><strong>Địa chỉ giao hàng:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                            </div>
                        </div>
                        <?php if (!empty($order['note'])): ?>
                        <div class="mt-3">
                            <strong>Ghi chú:</strong>
                            <p class="mb-0"><?= htmlspecialchars($order['note']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sản phẩm trong đơn -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sản phẩm trong đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image'])): ?>
                                                <img src="/uploads/products/<?= htmlspecialchars($item['image']) ?>" alt="" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-end"><?= number_format($item['price']) ?> VNĐ</td>
                                        <td class="text-end fw-semibold"><?= number_format($item['quantity'] * $item['price']) ?> VNĐ</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Cập nhật trạng thái -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Cập nhật trạng thái</h5>
                    </div>
                    <div class="card-body">
                        <form id="statusForm" onsubmit="updateStatusAjax(event, <?= $order['id'] ?>)">
                            <div class="mb-3">
                                <label for="status" class="form-label">Trạng thái đơn hàng</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Đang giao hàng</option>
                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Đã giao hàng</option>
                                    <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </div>
                            <div id="alertMessage" style="display:none;" class="alert mb-3" role="alert"></div>
                            <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                <i class="fa-solid fa-save me-2"></i>
                                Cập nhật trạng thái
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tổng kết -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tổng kết đơn hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tạm tính (Tổng sản phẩm):</span>
                            <span><?= number_format($order['total_amount'] + $order['discount_amount'] - $order['shipping_fee']) ?> VNĐ</span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Giảm giá:</span>
                            <span>-<?= number_format($order['discount_amount']) ?> VNĐ</span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Phí vận chuyển:</span>
                            <span>+<?= number_format($order['shipping_fee']) ?> VNĐ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span class="text-success"><?= number_format($order['total_amount']) ?> VNĐ</span>
                        </div>
                    </div>
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

        function updateStatusAjax(event, orderId) {
            event.preventDefault();
            
            const status = document.getElementById('status').value;
            const submitBtn = document.getElementById('submitBtn');
            const alertDiv = document.getElementById('alertMessage');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Đang cập nhật...';
            
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
                    alertDiv.style.display = 'block';
                    alertDiv.className = 'alert alert-success mb-3';
                    alertDiv.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i>' + data.message;
                    // Reload page after 1.5 seconds to show updated status
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message, 'error');
                    alertDiv.style.display = 'block';
                    alertDiv.className = 'alert alert-danger mb-3';
                    alertDiv.innerHTML = '<i class="fa-solid fa-exclamation-circle me-2"></i>' + data.message;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-save me-2"></i>Cập nhật trạng thái';
                }
            })
            .catch(error => {
                showToast('Lỗi: ' + error, 'error');
                alertDiv.style.display = 'block';
                alertDiv.className = 'alert alert-danger mb-3';
                alertDiv.innerHTML = '<i class="fa-solid fa-exclamation-circle me-2"></i>Lỗi: ' + error;
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-save me-2"></i>Cập nhật trạng thái';
            });
        }
    </script>
</body>
</html>