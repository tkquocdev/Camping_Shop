<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-map-location-dot me-2"></i>Địa chỉ nhận hàng
        </h4>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
            <i class="fa-solid fa-plus me-2"></i>Thêm địa chỉ mới
        </button>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm">
                <a href="/profile/index" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-user me-2"></i> Thông tin tài khoản
                </a>
                <a href="/profile/addresses" class="list-group-item list-group-item-action active fw-bold">
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
            <?php if (empty($addresses)): ?>
                <div class="card shadow border-0 rounded-3">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-map-location-dot fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Bạn chưa có địa chỉ nào</h5>
                        <p class="text-secondary mb-4">Thêm địa chỉ để thuận tiện cho việc giao hàng!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="fa-solid fa-plus me-2"></i>Thêm địa chỉ đầu tiên
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($addresses as $address): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm border-0 h-100 position-relative overflow-hidden" 
                                 style="<?php echo $address['is_default'] ? 'background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%); border-left: 4px solid #0D8ABC !important;' : ''; ?>">
                                <?php if ($address['is_default']): ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-primary" style="font-size: 0.8rem;">
                                            <i class="fa-solid fa-star me-1"></i>Mặc định
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="card-title mb-1 fw-bold">
                                                <?= htmlspecialchars($address['recipient_name']) ?>
                                            </h6>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editAddress(<?= $address['id'] ?>)">
                                                    <i class="fa-solid fa-edit me-2"></i>Sửa
                                                </a></li>
                                                <?php if (!$address['is_default']): ?>
                                                    <li><a class="dropdown-item" href="/profile/set_default_address?id=<?= $address['id'] ?>">
                                                        <i class="fa-solid fa-star me-2"></i>Đặt làm mặc định
                                                    </a></li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="/profile/delete_address?id=<?= $address['id'] ?>"
                                                       onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?')">
                                                    <i class="fa-solid fa-trash me-2"></i>Xóa
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="text-muted small mb-2">
                                        <i class="fa-solid fa-phone me-1"></i>
                                        <?= htmlspecialchars($address['phone']) ?>
                                    </div>

                                    <div class="text-secondary small mb-3">
                                        <i class="fa-solid fa-location-dot me-1"></i>
                                        <?= htmlspecialchars($address['address']) ?>
                                    </div>

                                    <?php if (!$address['is_default']): ?>
                                        <a href="/profile/set_default_address?id=<?= $address['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                            <i class="fa-solid fa-star me-1"></i>Đặt làm mặc định
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Thêm địa chỉ -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/profile/add_address" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Thêm địa chỉ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..." required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" id="isDefaultCheck">
                        <label class="form-check-label" for="isDefaultCheck">
                            Đặt làm địa chỉ mặc định
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm địa chỉ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa địa chỉ -->
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/profile/update_address" method="POST">
                <input type="hidden" name="address_id" id="editAddressId">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Sửa địa chỉ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="editRecipientName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="editPhone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                        <textarea name="address" id="editAddress" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" id="editIsDefaultCheck">
                        <label class="form-check-label" for="editIsDefaultCheck">
                            Đặt làm địa chỉ mặc định
                        </label>
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
function editAddress(addressId) {
    // AJAX để lấy thông tin địa chỉ
    fetch(`/profile/get_address?id=${addressId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const addr = data.address;
                document.getElementById('editAddressId').value = addr.id;
                document.getElementById('editRecipientName').value = addr.recipient_name;
                document.getElementById('editPhone').value = addr.phone;
                document.getElementById('editAddress').value = addr.address;
                document.getElementById('editIsDefaultCheck').checked = addr.is_default == 1;

                new bootstrap.Modal(document.getElementById('editAddressModal')).show();
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
