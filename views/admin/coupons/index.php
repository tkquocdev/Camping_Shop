<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khuyến mãi - Camping Shop</title>
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
    <div class="sidebar">
        <?php $active = 'coupons'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Quản lý Khuyến mãi</h4>
            <a href="/admin/coupons/create" class="btn btn-primary fw-bold">
                <i class="fa-solid fa-plus me-2"></i> Tạo mã mới
            </a>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="ps-4">Mã Code</th>
                                <th>Loại giảm</th>
                                <th>Giá trị</th>
                                <th>Đơn tối thiểu</th>
                                <th class="text-center">Số lượng</th>
                                <th>Đối tượng</th>
                                <th>Hạn sử dụng</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php foreach($coupons as $c): ?>
                            <?php 
                                // --- XỬ LÝ LOGIC NGÀY THÁNG ---
                                // Ưu tiên lấy expiration_date, nếu không có lấy end_date (fix lỗi Warning undefined key)
                                $dateValue = $c['expiration_date'] ?? $c['end_date'] ?? null;
                                $isExpired = false;
                                $dateDisplay = '<span class="text-muted">--</span>'; // Mặc định nếu null

                                if (!empty($dateValue)) {
                                    $timestamp = strtotime($dateValue);
                                    if ($timestamp !== false) {
                                        $isExpired = $timestamp < time();
                                        $dateDisplay = date('d/m/Y H:i', $timestamp);
                                    }
                                }

                                // --- XỰ LÝ TRẠNG THÁI ---
                                $quantity = intval($c['quantity'] ?? 0);
                                $isEnabled = intval($c['status'] ?? 1) == 1;
                                
                                if (!$isEnabled) {
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Tắt';
                                } elseif ($isExpired) {
                                    $statusClass = 'bg-secondary';
                                    $statusText = 'Hết hạn';
                                } elseif ($quantity <= 0) {
                                    $statusClass = 'bg-danger';
                                    $statusText = 'Hết lượt';
                                } else {
                                    $statusClass = 'bg-success';
                                    $statusText = 'Đang chạy';
                                }
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-danger"><?= htmlspecialchars($c['code'] ?? '') ?></td>
                                
                                <td>
                                    <?php if(($c['discount_type'] ?? '') == 'fixed'): ?>
                                        <span class="badge bg-info text-dark">Trừ tiền</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Trừ %</span>
                                    <?php endif; ?>
                                </td>

                                <td class="fw-bold">
                                    <?= ($c['discount_type'] ?? '') == 'fixed' 
                                        ? number_format($c['discount_value'] ?? 0) . ' đ' 
                                        : ($c['discount_value'] ?? 0) . '%' ?>
                                </td>

                                <td><?= number_format($c['min_order_value'] ?? 0) ?> đ</td>

                                <td class="text-center">
                                    <?php if ($quantity <= 0): ?>
                                        <span class="badge bg-danger rounded-pill px-3">0</span>
                                    <?php elseif ($quantity <= 10): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3" title="Sắp hết!">
                                            <?= number_format($quantity) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border rounded-pill px-3">
                                            <?= number_format($quantity) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($c['is_private'] ?? 0) == 1): ?>
                                        <span class="badge" style="background-color: #6610f2;">Riêng tư</span>
                                        <div class="small text-muted" style="font-size: 0.75rem;">Game/Quà tặng</div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Công khai</span>
                                        <div class="small text-muted" style="font-size: 0.75rem;">Tất cả khách</div>
                                    <?php endif; ?>
                                </td>

                                <td><?= $dateDisplay ?></td>
                                
                                <td>
                                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>

                                <td class="text-end pe-4">
                                    <a href="/admin/coupons/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning me-2" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <form action="/admin/coupons/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa mã này?');">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
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
</body>
</html>