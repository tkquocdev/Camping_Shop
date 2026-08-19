<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Flash Sale - Camping Shop</title>

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
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <?php $active = 'flash_sale'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fa-solid fa-bolt me-2"></i>Quản lý Flash Sale
            </h3>
            <p class="text-muted mb-0">Danh sách các chương trình khuyến mãi chớp nhoáng.</p>
        </div>

        <a href="/admin/flash_sale/create" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Tạo đợt Sale mới
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th class="px-4">ID</th>
                            <th>Tên chương trình</th>
                            <th>Tiến độ</th>
                            <th>Thời gian</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($flash_sales)): ?>
                        <?php foreach ($flash_sales as $sale): ?>

                        <?php
                            $sold = (int)($sale['total_sold'] ?? 0);
                            $totalQty = (int)($sale['total_quantity'] ?? 0);
                            $percent = $totalQty > 0 ? ($sold / $totalQty) * 100 : 0;

                            $now = date('Y-m-d H:i:s');

                            if ($sale['status'] == 0) {
                                $status = '<span class="badge bg-secondary">Ẩn</span>';
                            } elseif ($now < $sale['start_time']) {
                                $status = '<span class="badge bg-info text-dark">Sắp diễn ra</span>';
                            } elseif ($now <= $sale['end_time']) {
                                $status = '<span class="badge bg-success">Đang chạy</span>';
                            } else {
                                $status = '<span class="badge bg-danger">Đã kết thúc</span>';
                            }
                        ?>

                        <tr>
                            <td class="px-4">#<?= $sale['id'] ?></td>

                            <td>
                                <strong><?= htmlspecialchars($sale['name']) ?></strong><br>
                                <a href="/admin/flash_sale/items/<?= $sale['id'] ?>" class="small text-primary">
                                    Quản lý sản phẩm
                                </a>
                            </td>

                            <td>
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                                </div>
                                <small><?= number_format($percent,1) ?>%</small>
                            </td>

                            <td>
                                <?= date('d/m/Y H:i', strtotime($sale['start_time'])) ?><br>
                                <?= date('d/m/Y H:i', strtotime($sale['end_time'])) ?>
                            </td>

                            <td class="text-center"><?= $status ?></td>

                            <td class="text-center">
                                <a href="/admin/flash_sale/edit/<?= $sale['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                <form action="/admin/flash_sale/delete/<?= $sale['id'] ?>" method="POST" style="display:inline;">
                                    <button class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                Chưa có Flash Sale
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>

            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>