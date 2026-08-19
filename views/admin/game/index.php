<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình Vòng Quay - Camping Shop</title>
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
        <?php $active = 'game'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-gamepad me-2"></i> Cấu hình Vòng Quay
                </h3>
                <p class="text-muted mb-0">Thiết lập danh sách giải thưởng và tỷ lệ trúng thưởng.</p>
            </div>
            <div>
                <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addPrizeModal">
                    <i class="fas fa-plus me-1"></i> Thêm giải thưởng
                </button>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['flash_message']; ?>
                <?php unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="fas fa-table me-2 text-primary"></i>Danh sách giải thưởng
                </h6>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold text-secondary">
                            <tr class="text-center">
                                <th width="5%" class="py-3">ID</th>
                                <th width="25%">Tên giải thưởng</th>
                                <th width="20%">Mã Coupon</th>
                                <th width="15%">Tỷ lệ (%)</th>
                                <th width="10%">Màu sắc</th>
                                <th width="15%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalPercent = 0;
                            if (!empty($prizes)):
                                foreach ($prizes as $prize): 
                                    $totalPercent += $prize['percent'];
                            ?>
                                <tr>
                                    <form action="/admin/game/update" method="POST" id="form-update-<?= $prize['id'] ?>">
                                        <input type="hidden" name="id" value="<?= $prize['id'] ?>">
                                        
                                        <td class="text-center text-muted fw-bold">#<?= $prize['id'] ?></td>
                                        
                                        <td>
                                            <input type="text" name="name" class="form-control form-control-sm fw-bold text-dark" value="<?= htmlspecialchars($prize['name']) ?>" required>
                                        </td>
                                        
                                        <td>
                                            <input type="text" name="code" class="form-control form-control-sm text-center fw-semibold text-primary" 
                                                   value="<?= htmlspecialchars($prize['coupon_code'] ?? '') ?>" 
                                                   placeholder="Không có mã">
                                        </td>
                                        
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="percent" class="form-control text-center fw-bold" 
                                                       value="<?= $prize['percent'] ?>" min="0" max="100" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <input type="color" name="color" class="form-control form-control-color border-0 p-1" 
                                                       value="<?= $prize['color'] ?>" title="Chọn màu hiển thị" style="width: 50px;">
                                            </div>
                                        </td>
                                    </form> <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="submit" form="form-update-<?= $prize['id'] ?>" class="btn btn-primary btn-sm shadow-sm" title="Lưu thay đổi">
                                                <i class="fas fa-save"></i>
                                            </button>

                                            <form action="/admin/game/delete" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa ô này không?');">
                                                <input type="hidden" name="id" value="<?= $prize['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                endforeach; 
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-box-open fa-3x mb-3 opacity-25"></i>
                                        <p>Chưa có dữ liệu giải thưởng</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-white p-3">
                <?php if ($totalPercent == 100): ?>
                    <div class="alert alert-success mb-0 d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i> 
                        <div>
                            <h6 class="fw-bold mb-0">Cấu hình hợp lệ!</h6>
                            <small>Tổng tỷ lệ trúng thưởng hiện tại là <strong>100%</strong>.</small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger mb-0 d-flex align-items-center shadow-sm">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i> 
                        <div>
                            <h6 class="fw-bold mb-0">Cảnh báo cấu hình!</h6>
                            <small>Tổng tỷ lệ hiện tại là <strong class="fs-6"><?= $totalPercent ?>%</strong>. Vui lòng thêm/xóa hoặc chỉnh sửa để tổng bằng tròn <strong>100%</strong>.</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<div class="modal fade" id="addPrizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Ô Trúng Thưởng Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/admin/game/store" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên giải thưởng</label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Voucher 50K" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mã Coupon (Nếu có)</label>
                        <input type="text" name="code" class="form-control" placeholder="Nhập mã code giảm giá">
                        <div class="form-text">Để trống nếu là ô "Chúc may mắn" hoặc quà hiện vật.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tỷ lệ trúng (%)</label>
                            <input type="number" name="percent" class="form-control" value="10" min="0" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Màu ô</label>
                            <input type="color" name="color" class="form-control form-control-color w-100" value="#FF5733" title="Chọn màu">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>