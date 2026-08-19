<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="fw-bold text-primary mb-0">ĐĂNG KÝ TÀI KHOẢN</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger text-center shadow-sm">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> 
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                        <div class="alert alert-danger text-center shadow-sm">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            <?= implode(' | ', array_map('htmlspecialchars', $_SESSION['errors'])); unset($_SESSION['errors']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/auth/register">
                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tài khoản</label>
                            <input type="text" name="username" class="form-control" required 
                                   placeholder="Chọn tên tài khoản"
                                   value="<?= htmlspecialchars($_SESSION['old']['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ và Tên</label>
                            <input type="text" name="full_name" class="form-control" required 
                                   placeholder="Nhập họ và tên của bạn"
                                   value="<?= htmlspecialchars($_SESSION['old']['full_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required 
                                   placeholder="Nhập mật khẩu">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   placeholder="Nhập lại mật khẩu">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold py-2">
                                <i class="fa-solid fa-user-plus me-2"></i> Đăng Ký
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light py-3">
                    <small>Đã có tài khoản? <a href="/auth/login" class="text-decoration-none fw-bold">Đăng nhập ngay</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php unset($_SESSION['old']); ?>
<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
