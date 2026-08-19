<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm">
                <a href="/profile/index" class="list-group-item list-group-item-action active fw-bold">
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
                <a href="/profile/coupons" class="list-group-item list-group-item-action">
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
                    <h5 class="mb-0 fw-bold">Hồ sơ cá nhân</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4 mb-md-0 border-end">
                            <?php 
                                $userAvatar = $user['avatar'] ?? null;
                                $userFullName = $user['full_name'] ?? 'Khách hàng';
                                $defaultAvatar = "https://ui-avatars.com/api/?name=" . urlencode($userFullName) . "&background=0D8ABC&color=fff&size=128";
                                $avatarPath = $defaultAvatar;

                                if (!empty($userAvatar)) {
                                    if (filter_var($userAvatar, FILTER_VALIDATE_URL)) {
                                        $avatarPath = $userAvatar;
                                    } else {
                                        $relative = '/uploads/' . ltrim($userAvatar, '/');
                                        $fullPath = ROOT_PATH . '/public' . $relative;

                                        if (!file_exists($fullPath)) {
                                            $relative = '/uploads/users/' . basename($userAvatar);
                                            $fullPath = ROOT_PATH . '/public' . $relative;
                                        }

                                        if (file_exists($fullPath)) {
                                            $avatarPath = $relative . '?v=' . time();
                                        }
                                    }
                                }
                            ?>
                            <div class="position-relative d-inline-block mt-3">
                                <img src="<?= $avatarPath ?>" 
                                     class="rounded-circle shadow object-fit-cover" 
                                     width="150" height="150" 
                                     alt="Avatar" style="border: 4px solid #f8f9fa;">
                                
                                <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 rounded-circle shadow border"
                                        data-bs-toggle="modal" data-bs-target="#uploadAvatarModal"
                                        title="Đổi ảnh đại diện" style="width: 35px; height: 35px;">
                                    <i class="fa-solid fa-camera text-primary"></i>
                                </button>
                            </div>
                            <div class="mt-3">
                                <span class="text-muted small">Dụng lượng file tối đa: 2MB</span><br>
                                <span class="text-muted small">Định dạng: .JPEG, .PNG</span>
                            </div>
                        </div>

                        <div class="col-md-8 ps-md-4">
                            <form action="/profile/update_info" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">Họ và tên</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" 
                                           placeholder="Nhập họ và tên đầy đủ..." required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">Email</label>
                                    <input type="email" name="email" class="form-control bg-light" 
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                           readonly title="Không thể thay đổi email">
                                    <div class="form-text text-muted"><i class="fa-solid fa-circle-info me-1"></i>Email dùng để đăng nhập, không thể tự thay đổi.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                           placeholder="Nhập số điện thoại...">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-secondary">Vai trò</label>
                                    <div><span class="badge bg-info text-dark text-uppercase"><?= $user['role'] ?? 'user' ?></span></div>
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Lưu thông tin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Bảo mật</h5>
                </div>
                <div class="card-body">
                    <form action="/profile/change_password" method="POST">
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Mật khẩu hiện tại</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="password" name="current_password" class="form-control" id="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="fa-solid fa-eye" id="current_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Mật khẩu mới</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="password" name="new_password" class="form-control" id="new_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="fa-solid fa-eye" id="new_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-md-4 col-form-label">Nhập lại MK mới</label>
                            <div class="col-md-8">
                                <div class="input-group">
                                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="fa-solid fa-eye" id="confirm_password_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-warning text-dark fw-bold">
                                <i class="fa-solid fa-key me-1"></i> Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="uploadAvatarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/profile/upload_avatar" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Đổi ảnh đại diện</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-3"></i>
                        <p>Chọn ảnh từ máy tính của bạn (Max 2MB)</p>
                        <input type="file" name="avatar" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tải lên ngay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>)
