<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Nhà Cung Cấp - Camping Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 300px; background-color: #343a40; overflow-y: auto; }
        .main-content { margin-left: 300px; padding: 20px; min-height: 100vh; }
        @media (max-width: 992px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'suppliers'; require_once ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Cập Nhật Nhà Cung Cấp
                </h3>
                <p class="text-muted mb-0">Chỉnh sửa thông tin nhà cung cấp #<?= $supplier['id'] ?></p>
            </div>
            <a href="/admin/suppliers" class="btn btn-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="/admin/suppliers/update/<?= $supplier['id'] ?>" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required 
                                   value="<?= htmlspecialchars($supplier['name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email liên hệ</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Địa chỉ kho/văn phòng</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                    </div>

                    <div class="text-end">
                        <a href="/admin/suppliers" class="btn btn-outline-secondary px-4 me-2">
                            <i class="fa-solid fa-times me-2"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-warning px-4 shadow-sm">
                            <i class="fa-solid fa-check me-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>