<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white text-center py-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fa-solid fa-lock me-2"></i>Đặt Lại Mật Khẩu
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
                        <?php 
                        if (isset($_SESSION['reset_username'])) {
                            echo "Nhập mật khẩu mới cho tài khoản <strong>" . htmlspecialchars($_SESSION['reset_username']) . "</strong>";
                        } else {
                            echo "Nhập mật khẩu mới cho tài khoản của bạn.";
                        }
                        ?>
                    </p>
                    
                    <form method="POST" action="/auth/reset-password">
                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Mật Khẩu Mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-lock text-success"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Nhập mật khẩu mới">
                            </div>
                            <div class="form-text">Tối thiểu 3 ký tự</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-bold">Xác Nhận Mật Khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fa-solid fa-lock-open text-success"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       placeholder="Nhập lại mật khẩu">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg fw-bold">
                                <i class="fa-solid fa-check-circle me-2"></i>Đặt Lại Mật Khẩu
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
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