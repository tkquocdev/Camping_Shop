<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm Flash Sale - Camping Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 300px;
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
                    <i class="fa-solid fa-box me-2"></i> Sản phẩm Flash Sale
                </h3>
                <p class="text-muted mb-0">Quản lý sản phẩm trong đợt sale: <strong><?= htmlspecialchars($flash_sale['name']) ?></strong></p>
            </div>
            <div>
                <a href="/admin/flash_sale/edit/<?= $flash_sale['id'] ?>" class="btn btn-primary me-2">
                    <i class="fa-solid fa-plus me-2"></i> Thêm sản phẩm
                </a>
                <a href="/admin/flash_sale" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-exclamation-circle me-2"></i> <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['flash_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if (empty($sale_items)): ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có sản phẩm nào trong đợt sale này</h5>
                        <p class="text-muted">Hãy thêm sản phẩm để bắt đầu chương trình khuyến mãi</p>
                        <a href="/admin/flash_sale/edit/<?= $flash_sale['id'] ?>" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i> Thêm sản phẩm đầu tiên
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá gốc</th>
                                    <th>Giá sale</th>
                                    <th>Giảm</th>
                                    <th>Số lượng</th>
                                    <th>Đã bán</th>
                                    <th>Còn lại</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sale_items as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= !empty($item['product_image']) ? $item['product_image'] : 'https://placehold.co/50x50' ?>"
                                                 class="rounded" width="50" height="50" alt="Product">
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></div>
                                            <small class="text-muted">ID: <?= $item['product_id'] ?></small>
                                        </td>
                                        <td class="fw-bold text-muted">
                                            <?= number_format($item['original_price']) ?>đ
                                        </td>
                                        <td class="fw-bold text-danger">
                                            <?= number_format($item['sale_price']) ?>đ
                                        </td>
                                        <td>
                                            <?php
                                            $discount = round((($item['original_price'] - $item['sale_price']) / $item['original_price']) * 100);
                                            ?>
                                            <span class="badge bg-danger">-<?= $discount ?>%</span>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <?= $item['quantity'] ?>
                                        </td>
                                        <td class="fw-bold text-success">
                                            <?= $item['sold'] ?? 0 ?>
                                        </td>
                                        <td class="fw-bold text-warning">
                                            <?= ($item['quantity'] - ($item['sold'] ?? 0)) ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="removeItem(<?= $item['id'] ?>)">
                                                <i class="fa-solid fa-trash me-1"></i> Xóa
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function removeItem(itemId) {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi đợt sale?')) {
            const formData = new FormData();
            formData.append('item_id', itemId);

            fetch('/admin/flash_sale/remove_item', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm'));
                }
            })
            .catch(err => {
                console.error('Lỗi:', err);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            });
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>