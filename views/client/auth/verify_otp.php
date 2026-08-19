<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-info text-white text-center py-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fa-solid fa-shield-halved me-2"></i>Xác Nhận Mã OTP
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
                        <?php if (isset($_SESSION['reset_email'])): ?>
                            Mã OTP đã được gửi đến <strong><?= htmlspecialchars($_SESSION['reset_email']) ?></strong>
                        <?php elseif (isset($_SESSION['reset_username'])): ?>
                            Xác nhận mật khẩu mới cho tài khoản <strong><?= htmlspecialchars($_SESSION['reset_username']) ?></strong>
                        <?php else: ?>
                            Nhập mã OTP đã được gửi đến email của bạn.
                        <?php endif; ?>
                    </p>
                    
                    <?php if (isset($_SESSION['reset_email'])): ?>
                    <form method="POST" action="/auth/verify-otp">
                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                        <div class="mb-3">
                            <label for="otp" class="form-label fw-bold">Mã OTP (6 chữ số) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-hashtag text-info"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 text-center fw-bold" 
                                       id="otp" 
                                       name="otp" 
                                       maxlength="6" 
                                       inputmode="numeric"
                                       placeholder="000000"
                                       required>
                            </div>
                            <div class="form-text">Nhập 6 chữ số mà bạn vừa nhận được</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info btn-lg fw-bold">
                                <i class="fa-solid fa-check me-2"></i>Xác Nhận
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <?php 
                    // Tự động redirect nếu là username
                    if (isset($_SESSION['reset_username'])) {
                        header("Location: /auth/reset-password");
                        exit;
                    }
                    ?>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="small mb-2">
                            <a href="/auth/forgot-password" class="text-decoration-none fw-bold">
                                <i class="fa-solid fa-redo me-1"></i>Gửi Lại OTP
                            </a>
                        </p>
                        <p class="mb-0">
                            <a href="/auth/login" class="text-decoration-none fw-bold">
                                <i class="fa-solid fa-arrow-left me-1"></i>Quay về Đăng Nhập
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>