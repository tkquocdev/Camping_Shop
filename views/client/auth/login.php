<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="fw-bold text-primary mb-0">ĐĂNG NHẬP</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($_SESSION['flash_error'])): ?>
                        <div class="alert alert-danger text-center shadow-sm">
                            <i class="fa-solid fa-lock me-2"></i> 
                            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-success text-center shadow-sm">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form action="/auth/login" method="POST">
                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tài khoản</label>
                            <input type="text" name="username" class="form-control" required 
                                   placeholder="Nhập tài khoản của bạn"
                                   autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required placeholder="Nhập mật khẩu của bạn">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold py-2">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng Nhập
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="d-flex align-items-center my-3">
                        <hr class="flex-grow-1">
                        <span class="mx-2 text-muted small">Hoặc</span>
                        <hr class="flex-grow-1">
                    </div>

                    <!-- Google Login Button -->
                    <div class="d-grid">
                        <a href="/auth/google" class="btn btn-outline-danger fw-bold py-2 d-flex align-items-center justify-content-center">
                            <i class="fa-brands fa-google me-2"></i> Đăng nhập bằng Google
                        </a>
                    </div>

                    <div class="text-center my-3">
                        <small><a href="/auth/forgot-password" class="text-secondary text-decoration-none"><i class="fa-solid fa-key me-1"></i>Quên mật khẩu?</a></small>
                    </div>
                    </div>
                <div class="card-footer text-center bg-light py-3">
                    <small>Chưa có tài khoản? <a href="/auth/register" class="text-decoration-none fw-bold">Đăng ký ngay</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fa-solid fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fa-solid fa-eye';
    }
});
</script>