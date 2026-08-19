<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà Cung Cấp - Camping Shop</title>
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
        <?php $active = 'suppliers'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-truck me-2"></i> Quản lý Nhà Cung Cấp
                </h3>
                <p class="text-muted mb-0">Danh sách các nhà cung cấp hàng hóa</p>
            </div>
            <a href="/admin/suppliers/create" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i> Thêm mới
            </a>
        </div>

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold text-secondary">
                            <tr>
                                <th class="ps-4" style="width:80px;">ID</th>
                                <th>Tên Nhà Cung Cấp</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th>Địa chỉ</th>
                                <th class="text-center" style="width:150px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($suppliers)): ?>
                                <?php foreach($suppliers as $s): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?= $s['id'] ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($s['name']) ?></td>
                                    <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                                    <td title="<?= htmlspecialchars($s['address'] ?? '') ?>" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars(substr($s['address'] ?? '-', 0, 30)) ?></td>
                                    <td class="text-center">
                                        <a href="/admin/suppliers/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="/admin/suppliers/delete/<?= $s['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Xóa nhà cung cấp này?')" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <p class="mb-0">Chưa có nhà cung cấp nào. <a href="/admin/suppliers/create" class="text-decoration-none">Thêm mới</a></p>
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