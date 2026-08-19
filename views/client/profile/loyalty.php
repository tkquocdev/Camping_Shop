<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm">
                <a href="/profile/index" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-user me-2"></i> Thông tin tài khoản
                </a>
                <a href="/profile/addresses" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-map-location-dot me-2"></i> Địa chỉ nhận hàng
                </a>
                <a href="/profile/history" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử đơn hàng
                </a>
                <a href="/profile/notifications" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-bell me-2"></i> Thông báo của tôi
                </a>
                <a href="/profile/loyalty" class="list-group-item list-group-item-action active fw-bold">
                    <i class="fa-solid fa-gift me-2"></i> Đổi thưởng & Quà tặng
                </a>
                <a href="/profile/coupons" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-ticket me-2"></i> Kho voucher của tôi
                </a>
                <a href="/auth/logout" class="list-group-item list-group-item-action text-danger">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div class="col-md-9">
            
            <div class="card shadow-sm border-0 mb-4 bg-primary text-white" style="background: linear-gradient(45deg, #0d6efd, #0dcaf0);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Điểm tích lũy hiện tại</h5>
                        <h2 class="fw-bold mb-0 display-4"><?= number_format($user_points ?? 0) ?> <span class="fs-5">điểm</span></h2>
                    </div>
                    <div class="text-end opacity-50">
                        <i class="fa-solid fa-coins fa-4x"></i>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-gifts me-2 text-warning"></i>Danh sách quà đổi thưởng</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($rewards)): ?>
                        <p class="text-center text-muted py-3">Hiện tại chưa có quà đổi thưởng nào.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($rewards as $reward): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-3 d-flex justify-content-between align-items-center h-100 hover-shadow">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($reward['name']) ?></h6>
                                            <div class="small text-muted mb-2">Trị giá: <?= number_format($reward['voucher_value']) ?>đ</div>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fa-solid fa-bolt me-1"></i> <?= number_format($reward['points_required']) ?> điểm
                                            </span>
                                        </div>
                                        <div>
                                            <form id="exchangeForm<?= $reward['id'] ?>" action="/profile/exchange_reward" method="POST" onsubmit="return confirm('Bạn có chắc muốn đổi <?= $reward['points_required'] ?> điểm lấy quà này?');">
                                                <input type="hidden" name="reward_id" value="<?= $reward['id'] ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm" <?= (($user_points ?? 0) < $reward['points_required']) ? 'disabled' : '' ?>>
                                                    Đổi ngay
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i>Lịch sử điểm thưởng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Thời gian</th>
                                    <th>Nội dung</th>
                                    <th class="text-end pe-4">Số điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($history)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Chưa có lịch sử giao dịch.</td></tr>
                                <?php else: ?>
                                    <?php foreach($history as $h): ?>
                                        <tr>
                                            <td class="ps-4 text-muted small">
                                                <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                                            </td>
                                            <td>
                                                <span class="d-block text-dark"><?= htmlspecialchars($h['description']) ?></span>
                                                <small class="text-muted fst-italic"><?= $h['type'] == 'purchase' ? 'Mua hàng' : ($h['type'] == 'redeem' ? 'Đổi quà' : 'Khác') ?></small>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if($h['amount'] > 0): ?>
                                                    <span class="text-success fw-bold">+<?= number_format($h['amount']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger fw-bold"><?= number_format($h['amount']) ?></span>
                                                <?php endif; ?>
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle exchange form submissions with AJAX
    document.querySelectorAll('form[id^="exchangeForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('Bạn có chắc muốn đổi điểm lấy quà này?')) {
                return;
            }
            
            const formData = new FormData(this);
            const rewardId = formData.get('reward_id');
            
            // Disable button to prevent double submission
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang xử lý...';
            
            fetch('/profile/exchange_reward', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showToast('success', data.message);
                    // Reload page after 2 seconds to show updated points
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    showToast('error', data.message);
                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });
    
    function showToast(type, message) {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
