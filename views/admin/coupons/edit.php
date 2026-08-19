<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Mã Giảm Giá - Camping Shop Admin</title>
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
        <?php $active = 'coupons'; require_once ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

    <div class="main-content w-100 p-4 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary">
                Chỉnh sửa Mã: <span class="text-danger"><?= htmlspecialchars($coupon['code']) ?></span>
            </h3>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="/admin/coupons/update" method="POST">
                    <input type="hidden" name="id" value="<?= $coupon['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="code" class="form-label fw-bold">Mã Code (Viết liền, không dấu) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase fw-bold" 
                                   id="code" name="code" 
                                   value="<?= htmlspecialchars($coupon['code']) ?>" required>
                            <div class="form-text text-danger">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Lưu ý: Nếu sửa mã này, các link chia sẻ cũ chứa mã cũ sẽ không còn tác dụng.
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Tên chương trình (Hiển thị cho khách)</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($coupon['name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Loại giảm giá</label>
                            <select name="discount_type" class="form-select">
                                <option value="fixed" <?= $coupon['discount_type'] == 'fixed' ? 'selected' : '' ?>>Trừ tiền trực tiếp (VNĐ)</option>
                                <option value="amount" <?= $coupon['discount_type'] == 'amount' ? 'selected' : '' ?>>Trừ theo phần trăm (%)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Giá trị giảm</label>
                            <div class="input-group">
                                <input type="number" name="discount_value" class="form-control" 
                                       value="<?= $coupon['discount_value'] ?>" required min="0">
                                <span class="input-group-text">VNĐ / %</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Đơn hàng tối thiểu</label>
                            <div class="input-group">
                                <input type="number" name="min_order_value" class="form-control" 
                                       value="<?= $coupon['min_order_value'] ?>" min="0">
                                <span class="input-group-text">VNĐ</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số lượng mã</label>
                            <input type="number" name="quantity" class="form-control" 
                                   value="<?= $coupon['quantity'] ?>" required min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngày bắt đầu</label>
                            <input type="datetime-local" name="start_date" class="form-control" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($coupon['start_date'])) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngày hết hạn</label>
                            <?php 
                                // Xử lý ngày hết hạn để tránh lỗi nếu null
                                $expValue = '';
                                if (!empty($coupon['expiration_date']) && $coupon['expiration_date'] != '0000-00-00 00:00:00') {
                                    $expValue = date('Y-m-d\TH:i', strtotime($coupon['expiration_date']));
                                }
                                // Lưu ý: name="end_date" hoặc "expiration_date" tùy thuộc vào Controller của bạn hứng biến nào
                                // Ở đây mình để end_date cho chuẩn với form create
                            ?>
                            <input type="datetime-local" name="end_date" class="form-control" 
                                   value="<?= $expValue ?>">
                        </div>

                        <div class="col-md-12 mt-4">
                            <div class="card bg-light border-0 p-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_private" id="isPrivate" 
                                        <?= ($coupon['is_private'] ?? 0) == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="isPrivate">
                                        Mã riêng tư 
                                        <small class="text-muted fw-normal">(Chỉ dùng cho Game/Quà tặng, không hiển thị công khai trên web)</small>
                                    </label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" id="status" 
                                        <?= ($coupon['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-success" for="status">Kích hoạt ngay</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-1"></i> Cập nhật
                        </button>
                        <a href="/admin/coupons" class="btn btn-outline-secondary px-4">Quay lại</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>