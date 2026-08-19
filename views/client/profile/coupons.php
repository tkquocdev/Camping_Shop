<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    
    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

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
                <a href="/profile/loyalty" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-gift me-2"></i> Đổi thưởng & Quà tặng
                </a>
                <a href="/profile/coupons" class="list-group-item list-group-item-action active fw-bold">
                    <i class="fa-solid fa-ticket me-2"></i> Kho voucher của tôi
                </a>
                <a href="/auth/logout" class="list-group-item list-group-item-action text-danger">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-ticket text-warning me-2"></i>Kho Voucher của tôi</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($coupons) ?> mã khả dụng</span>
                </div>
                
                <div class="card-body bg-light">
                    <?php if (empty($coupons)): ?>
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076478.png" width="100" class="mb-3 opacity-50">
                            <h6 class="text-muted">Bạn chưa có voucher nào.</h6>
                            <a href="/profile/loyalty" class="btn btn-sm btn-outline-primary mt-2">Đổi điểm lấy quà ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($coupons as $c): ?>
                                <div class="col-md-6 col-lg-6">
                                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden voucher-card">
                                        <div class="position-absolute top-0 start-0 bottom-0 bg-warning" style="width: 6px;"></div>
                                        
                                        <div class="card-body d-flex align-items-center p-3">
                                            <div class="flex-shrink-0 text-center me-3">
                                                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fa-solid fa-percent text-warning fs-4"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="card-title fw-bold text-truncate mb-1" title="<?= htmlspecialchars($c['name'] ?? '') ?>">
                                                    <?= htmlspecialchars($c['name'] ?? '') ?>
                                                </h6>
                                                
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-light text-dark border me-2 font-monospace user-select-all" id="code-<?= $c['id'] ?>">
                                                        <?= htmlspecialchars($c['code'] ?? '') ?>
                                                    </span>
                                                    <button class="btn btn-link btn-sm p-0 text-decoration-none copy-btn" 
                                                            data-clipboard-target="#code-<?= $c['id'] ?>"
                                                            title="Sao chép mã">
                                                        <i class="fa-regular fa-copy"></i>
                                                    </button>
                                                </div>

                                                <p class="card-text small text-muted mb-0">
                                                    Giảm: 
                                                    <span class="fw-bold text-danger">
                                                        <?php if($c['discount_type'] == 'percent'): ?>
                                                            <?= number_format($c['discount_value']) ?>%
                                                        <?php else: ?>
                                                            <?= number_format($c['discount_value']) ?>đ
                                                        <?php endif; ?>
                                                    </span>
                                                </p>
                                                <p class="card-text small text-muted mb-0">
                                                    Đơn tối thiểu: <?= $c['min_order_value'] > 0 ? number_format($c['min_order_value']).'đ' : '0đ' ?>
                                                </p>
                                                <p class="card-text small text-danger mb-0 mt-1">
                                                    <i class="fa-regular fa-clock me-1"></i>HSD: <?= date('d/m/Y', strtotime($c['expiration_date'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                            <i class="fa-solid fa-ticket fa-3x text-secondary rotate-45"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer bg-white small text-muted">
                    * Các mã giảm giá sẽ tự động ẩn khi hết hạn hoặc hết lượt sử dụng.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyBtns = document.querySelectorAll('.copy-btn');
    
    copyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-clipboard-target');
            const codeText = document.querySelector(targetId).innerText.trim();
            
            navigator.clipboard.writeText(codeText).then(() => {
                // Hiệu ứng copy thành công
                const originalIcon = this.innerHTML;
                this.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                this.classList.add('disabled');
                
                setTimeout(() => {
                    this.innerHTML = originalIcon;
                    this.classList.remove('disabled');
                }, 2000);
            });
        });
    });
});
</script>

<style>
    .rotate-45 { transform: rotate(-25deg); }
    .voucher-card { transition: transform 0.2s; }
    .voucher-card:hover { transform: translateY(-3px); }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>