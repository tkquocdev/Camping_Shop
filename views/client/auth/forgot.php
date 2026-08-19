<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fa-solid fa-key me-2"></i>Quên Mật Khẩu
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted text-center mb-4">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        Nhập tên tài khoản hoặc email của bạn để đặt lại mật khẩu.
                    </p>
                    
                    <form method="POST" action="/auth/forgot-password">
                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                        <div class="mb-3">
                            <label for="identifier" class="form-label fw-bold">Tên Tài Khoản hoặc Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-user text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="identifier" name="identifier" required placeholder="tên tài khoản hoặc email">
                            </div>
                            <div class="form-text">Nhập tên tài khoản hoặc email đã đăng ký</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                <i class="fa-solid fa-paper-plane me-2"></i>Gửi Mã OTP
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            <a href="/auth/login" class="text-decoration-none fw-bold">Quay lại Đăng Nhập</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>