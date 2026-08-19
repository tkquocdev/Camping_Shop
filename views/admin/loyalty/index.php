<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đổi thưởng - Camping Shop</title>
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
        <?php $active = 'loyalty'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-gift me-2"></i> Quản lý Đổi thưởng</h3>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRewardModal">
                <i class="fa-solid fa-plus me-2"></i> Thêm quà mới
            </button>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Tên quà tặng</th>
                            <th>Điểm yêu cầu</th>
                            <th>Giá trị Voucher</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rewards)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có gói đổi thưởng nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($rewards as $r): ?>
                                <tr>
                                    <td class="ps-4 text-muted">#<?= $r['id'] ?></td>
                                    
                                    <td class="fw-bold text-primary">
                                        <?= htmlspecialchars($r['name']) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-warning text-dark rounded-pill px-3">
                                            <i class="fa-solid fa-bolt me-1"></i> <?= number_format($r['points_required']) ?>
                                        </span>
                                    </td>

                                    <td class="fw-bold text-success">
                                        <?= number_format($r['voucher_value']) ?> đ
                                    </td>

                                    <td>
                                        <?php if(isset($r['status']) && $r['status'] == 0): ?>
                                            <span class="badge bg-secondary">Đang ẩn</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Đang hiện</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-info border-0" title="Chỉnh sửa" type="button" onclick="loadRewardData(<?= $r['id'] ?>, <?= json_encode($r) ?>)" data-bs-toggle="modal" data-bs-target="#editRewardModal">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form action="/admin/loyalty/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa gói quà này không?');">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger border-0" title="Xóa" type="submit">
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

<div class="modal fade" id="addRewardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/loyalty/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Thêm gói đổi thưởng mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên quà tặng</label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Voucher giảm 50k" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Điểm cần đổi</label>
                            <input type="number" name="points_required" class="form-control" placeholder="VD: 500" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá trị (VNĐ)</label>
                            <input type="number" name="voucher_value" class="form-control" placeholder="VD: 50000" required min="0">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="rewardStatus" value="1" checked>
                        <label class="form-check-label" for="rewardStatus">Đang hiện (Nếu bỏ chọn sẽ ẩn đi)</label>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Khi khách hàng đổi điểm, hệ thống sẽ tự động tạo ra một mã giảm giá riêng (loại giảm tiền trực tiếp) với giá trị tương ứng.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Reward -->
<div class="modal fade" id="editRewardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/loyalty/update" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chỉnh sửa gói đổi thưởng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_reward_id">
                    
                    <!-- Display Current Values -->
                    <div class="alert alert-light border mb-4 p-3" style="background-color: #f8f9fa;">
                        <h6 class="fw-bold text-muted mb-2"><i class="fa-solid fa-info-circle me-2"></i>Giá trị hiện tại</h6>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted">Tên quà tặng:</span><br>
                                <span class="fw-bold text-primary" id="current_reward_name">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Trạng thái:</span><br>
                                <span class="badge bg-success" id="current_reward_status">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Điểm cần đổi:</span><br>
                                <span class="fw-bold text-warning" id="current_reward_points">-</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Giá trị (VNĐ):</span><br>
                                <span class="fw-bold text-success" id="current_reward_value">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Edit Form -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên quà tặng</label>
                        <input type="text" name="name" id="edit_reward_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Điểm cần đổi</label>
                            <input type="number" name="points_required" id="edit_reward_points" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Giá trị (VNĐ)</label>
                            <input type="number" name="voucher_value" id="edit_reward_value" class="form-control" required min="0">
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="edit_reward_status" value="1">
                        <label class="form-check-label" for="edit_reward_status">Đang hiện</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadRewardData(id, data) {
    document.getElementById('edit_reward_id').value = data.id;
    document.getElementById('edit_reward_name').value = data.name;
    document.getElementById('edit_reward_points').value = data.points_required;
    document.getElementById('edit_reward_value').value = data.voucher_value;
    document.getElementById('edit_reward_status').checked = data.status == 1;
    
    // Display current values
    document.getElementById('current_reward_name').textContent = data.name;
    document.getElementById('current_reward_points').textContent = Number(data.points_required).toLocaleString('vi-VN');
    document.getElementById('current_reward_value').textContent = Number(data.voucher_value).toLocaleString('vi-VN', {style: 'currency', currency: 'VND'}).replace('₫', 'đ');
    document.getElementById('current_reward_status').textContent = data.status == 1 ? 'Đang hiện' : 'Đang ẩn';
    document.getElementById('current_reward_status').className = data.status == 1 ? 'badge bg-success' : 'badge bg-secondary';
}
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>