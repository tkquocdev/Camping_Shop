<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary">Khuyến Mãi Đặc Biệt</h1>
        <p class="lead text-muted">Nhiều ưu đãi hấp dẫn đang chờ bạn!</p>
    </div>

    <?php if(empty($coupons)): ?>
        <div class="alert alert-warning text-center py-5">
            <i class="fa-solid fa-box-open fa-3x mb-3"></i>
            <p>Hiện tại chưa có mã giảm giá nào đang diễn ra.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($coupons as $c): ?>
            <div class="col-md-6 mb-4">
                <div class="card coupon-card h-100">
                    <div class="card-body text-center d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                                <?= ($c['discount_type'] == 'fixed') ? 'Giảm tiền' : 'Giảm giá %' ?>
                            </span>
                        </div>

                        <h3 class="text-danger fw-bold mb-1" style="letter-spacing: 1px;"><?= $c['code'] ?></h3>

                        <div class="my-3">
                            <?php if($c['discount_type'] == 'fixed'): ?>
                                <h2 class="fw-bold text-dark mb-0">Giảm <?= number_format($c['discount_value']) ?>đ</h2>
                            <?php else: ?>
                                <h2 class="fw-bold text-dark mb-0">Giảm <?= $c['discount_value'] ?>%</h2>
                            <?php endif; ?>
                            <small class="text-muted">Cho đơn từ <?= number_format($c['min_order_value']) ?>đ</small>
                        </div>

                        <div class="mt-auto">
                            <div class="coupon-code mb-3">Mã: <?= $c['code'] ?></div>
                            <button class="btn btn-claim" onclick="copyCode(this, '<?= $c['code'] ?>')">
                                <i class="fa-solid fa-copy me-2"></i>Sao chép
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Điều kiện sử dụng -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Điều kiện sử dụng</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Áp dụng cho đơn hàng từ giá trị tối thiểu</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Không áp dụng chung với khuyến mãi khác</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i> Hạn sử dụng có thời hạn</li>
                        <li class="mb-0"><i class="fa-solid fa-check text-success me-2"></i> Mỗi khách hàng chỉ sử dụng 1 lần</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.coupon-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.coupon-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}
.coupon-code {
    background: rgba(255,255,255,0.2);
    border-radius: 20px;
    padding: 10px 20px;
    font-weight: bold;
    letter-spacing: 1px;
}
.btn-claim {
    background: #ff6b6b;
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    transition: background 0.3s;
}
.btn-claim:hover {
    background: #ff5252;
    color: white;
}
</style>

<script>
function copyCode(btn, code) {
    // Sao chép vào clipboard
    navigator.clipboard.writeText(code).then(() => {
        // Lưu trạng thái ban đầu
        const originalHTML = btn.innerHTML;
        const originalClass = btn.className;

        // Thay đổi nút thành trạng thái thành công
        btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Đã sao chép!';
        btn.className = 'btn btn-success';
        btn.style.background = '#28a745';
        btn.style.borderColor = '#28a745';

        // Hiển thị thông báo
        showToast('Đã sao chép mã: <strong>' + code + '</strong>');

        // Reset lại sau 3 giây
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.className = originalClass;
            btn.style.background = '';
            btn.style.borderColor = '';
        }, 3000);
    }).catch(err => {
        console.error('Không thể sao chép: ', err);
        showToast('Lỗi: Không thể sao chép mã!', 'error');
    });
}

function showToast(message, type = 'success') {
    // Tạo toast element
    const toast = document.createElement('div');
    toast.className = 'toast-notification ' + (type === 'error' ? 'error' : 'success');
    toast.innerHTML = message;

    // Thêm vào body
    document.body.appendChild(toast);

    // Hiển thị
    setTimeout(() => toast.classList.add('show'), 100);

    // Xóa sau 3 giây
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}
</script>

<style>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 9999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    font-weight: 500;
}

.toast-notification.error {
    background: #dc3545;
}

.toast-notification.show {
    transform: translateX(0);
}
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>