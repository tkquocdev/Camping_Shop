<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đánh giá - Camping Shop</title>
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
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'reviews'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="fa-solid fa-star me-2"></i>Quản lý Đánh giá
            </h4>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Sản phẩm</th>
                                <th>Khách hàng</th>
                                <th>Đánh giá</th>
                                <th>Ngày</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reviews)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-comments fa-3x mb-3 text-secondary"></i><br>
                                        <span class="fw-bold">Không có đánh giá nào.</span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="/uploads/products/<?= htmlspecialchars($r['product_image'] ?? '') ?>" 
                                                 alt="<?= htmlspecialchars($r['product_name'] ?? 'Product') ?>"
                                                 width="50" height="50" 
                                                 class="rounded"
                                                 style="object-fit: cover;"
                                                 onerror="this.src='https://placehold.co/50x50?text=No+Image'">
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($r['product_name'] ?? 'N/A') ?></div>
                                                <small class="text-muted">ID: #<?= $r['product_id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($r['reviewer_name'] ?? 'Ẩn danh') ?></div>
                                    </td>
                                    <td>
                                        <div>
                                            <?php for($i=0; $i<$r['rating']; $i++): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php endfor; ?>
                                            <?php for($i=$r['rating']; $i<5; $i++): ?>
                                                <i class="far fa-star text-muted"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="d-block text-muted mt-1" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?= htmlspecialchars(isset($r['comment']) ? substr($r['comment'], 0, 50) : 'Không có bình luận') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/admin/reviews/detail/<?= $r['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary me-1"
                                           title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <form action="/admin/reviews/delete/<?= $r['id'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Xóa đánh giá này không?')">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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