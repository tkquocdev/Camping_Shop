<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhà Cung Cấp - Camping Shop</title>

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
    <?php $active = 'suppliers'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fa-solid fa-building me-2"></i>Thêm Nhà Cung Cấp Mới
            </h3>
            <p class="text-muted mb-0">Nhập thông tin nhà cung cấp mới vào hệ thống</p>
        </div>
        <a href="/admin/suppliers" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
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
                <form action="/admin/suppliers/store" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="VD: Công ty TNHH ABC">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="02X XXXX XXXX">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email liên hệ</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@example.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Địa chỉ kho/văn phòng</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Số nhà, đường, phường/xã, quận/huyện..."></textarea>
                    </div>

                    <div class="text-end">
                        <button type="reset" class="btn btn-outline-secondary px-4 me-2">
                            <i class="fa-solid fa-redo me-2"></i>Xóa
                        </button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa-solid fa-save me-2"></i>Lưu thông tin
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