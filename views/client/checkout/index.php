<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-uppercase">Thanh Toán Đơn Hàng</h2>
        <p class="text-muted">Vui lòng kiểm tra kỹ thông tin trước khi đặt hàng</p>
    </div>

    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-7">
            <form action="/checkout/submit" method="POST" id="checkoutForm">
                <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3">
                        <i class="fa-solid fa-location-dot me-2 text-primary"></i>Thông tin nhận hàng
                    </div>
                    <div class="card-body">
                        <?php 
                            $currentEmail = $_SESSION['user']['email'] ?? '';
                            $isEmailValid = filter_var($currentEmail, FILTER_VALIDATE_EMAIL);
                        ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email nhận thông báo (Tùy chọn)</label>
                            <input type="email" name="order_email" class="form-control" 
                                   value="<?= $isEmailValid ? htmlspecialchars($currentEmail) : '' ?>" 
                                   placeholder="Nhập email của bạn (để nhận thông báo đơn hàng)">
                            <div class="form-text text-muted">
                                <i class="fa-solid fa-circle-info"></i> Hãy nhập email để nhận cập nhật về đơn hàng của bạn.
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Chọn địa chỉ giao hàng:</h6>
                        
                        <div class="list-group mb-3">
                            <?php if(!empty($user_addresses)): ?>
                                <?php foreach($user_addresses as $addr): ?>
                                    <label class="list-group-item d-flex gap-3 align-items-center cursor-pointer list-group-item-action">
                                        <input class="form-check-input flex-shrink-0" type="radio" name="address_selection" 
                                               value="<?= $addr['id'] ?>" 
                                               <?= $addr['is_default'] ? 'checked' : '' ?>
                                               onchange="toggleNewAddress(false)">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong>
                                                <?php if($addr['is_default']): ?>
                                                    <span class="badge bg-primary">Mặc định</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-muted small d-block">SĐT: <?= htmlspecialchars($addr['phone']) ?></span>
                                            <div class="small text-secondary mt-1"><i class="fa-solid fa-map-pin me-1"></i> <?= htmlspecialchars($addr['address']) ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <label class="list-group-item d-flex gap-3 align-items-center cursor-pointer bg-light list-group-item-action">
                                <input class="form-check-input flex-shrink-0" type="radio" name="address_selection" 
                                       value="new" id="radioNewAddress"
                                       <?= empty($user_addresses) ? 'checked' : '' ?>
                                       onchange="toggleNewAddress(true)">
                                <span class="fw-bold text-primary">
                                    <i class="fa-solid fa-plus me-1"></i> Giao đến địa chỉ khác
                                </span>
                            </label>
                        </div>

                        <div id="newAddressForm" class="<?= !empty($user_addresses) ? 'd-none' : '' ?> border p-3 rounded bg-light mb-3">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Tên người nhận <span class="text-danger">*</span></label>
                                    <input type="text" name="new_name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="new_phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                <textarea name="new_address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..."></textarea>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="save_address" id="saveAddr" value="1" data-save-address>
                                <label class="form-check-label small" for="saveAddr">Lưu vào sổ địa chỉ để dùng lần sau</label>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Ghi chú đơn hàng (Tùy chọn)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Giao hàng giờ hành chính..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold py-3"><i class="fa-solid fa-wallet me-2 text-primary"></i>Phương thức thanh toán</div>
                    <div class="card-body">
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 cursor-pointer">
                                <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" value="cod" checked>
                                <i class="fa-solid fa-money-bill-wave fa-lg text-success"></i>
                                <div>
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <div class="small text-muted">Thanh toán tiền mặt cho shipper khi nhận hàng.</div>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 cursor-pointer">
                                <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" value="banking">
                                <i class="fa-solid fa-building-columns fa-lg text-primary"></i>
                                <div>
                                    <strong>Chuyển khoản Ngân hàng</strong>
                                    <div class="small text-muted">Agribank: 7602 205 304 600 - TRAN KIM QUOC</div>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 cursor-pointer">
                                <input class="form-check-input flex-shrink-0" type="radio" name="payment_method" value="momo">
                                <i class="fa-solid fa-qrcode fa-lg text-danger"></i>
                                <div>
                                    <strong>Ví điện tử MoMo / ZaloPay</strong>
                                    <div class="small text-muted">Quét mã QR để thanh toán nhanh chóng.</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4 border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-white fw-bold py-3">Đơn hàng của bạn (<?= count($products) ?> món)</div>
                <div class="card-body p-0">
                    
                    <div class="custom-scrollbar" style="max-height: 380px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            <?php foreach($products as $index => $p): ?>
                                <?php 
                                    $quantity = $p['buy_quantity'] ?? $p['quantity'] ?? 1;
                                    $displayPrice = $p['display_price'] ?? $p['price'];
                                    $rowTotal = $displayPrice * $quantity;
                                    
                                    // Xử lý ảnh
                                    $imgSrc = 'https://placehold.co/60x60?text=No+Img';
                                    if (!empty($p['image'])) {
                                        if (filter_var($p['image'], FILTER_VALIDATE_URL)) {
                                            $imgSrc = $p['image'];
                                        } else {
                                            $imgSrc = '/uploads/products/' . basename($p['image']);
                                        }
                                    }
                                ?>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <div class="me-3 fw-bold text-secondary" style="min-width: 25px;">#<?= $index + 1 ?></div>
                                    <div class="position-relative me-3">
                                        <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                             class="rounded border bg-light" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light shadow-sm">
                                            <?= $quantity ?>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h6 class="mb-1 text-truncate fw-bold text-dark" title="<?= htmlspecialchars($p['name']) ?>">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if ($p['original_price'] != $displayPrice): ?>
                                                <small class="text-muted"><del><?= number_format($p['original_price'], 0, ',', '.') ?> đ</del></small>
                                            <?php else: ?>
                                                <small class="text-muted"><?= number_format($displayPrice, 0, ',', '.') ?> đ</small>
                                            <?php endif; ?>
                                            <span class="fw-bold text-primary">
                                                <?= number_format($displayPrice, 0, ',', '.') ?> đ <span class="text-secondary fw-normal">x <?= $quantity ?></span>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="border-top">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-light mt-0">
                                <form id="couponForm" class="input-group mb-2 mt-2">
                                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::getToken(); ?>">
                                    <input type="text" id="couponInput" name="coupon_code" class="form-control" placeholder="Nhập mã giảm giá" required>
                                    <button type="submit" id="btnApplyCoupon" class="btn btn-danger">Áp dụng</button>
                                </form>
                                
                                <div id="couponAlert" class="alert alert-success py-1 px-2 mb-2 d-flex justify-content-between align-items-center small <?= isset($_SESSION['coupon']) ? '' : 'd-none' ?>">
                                    <span><i class="fa-solid fa-tag me-1"></i> Mã: <strong id="appliedCouponCode"><?= $_SESSION['coupon']['code'] ?? '' ?></strong></span>
                                    <button type="button" class="btn-close btn-close-white" style="font-size: 10px;" onclick="removeCoupon()"></button>
                                </div>

                                <?php if(!empty($coupons)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted fw-bold d-block mb-1">Mã ưu đãi dành cho bạn:</small>
                                        <div class="d-flex flex-wrap gap-2" style="max-height: 100px; overflow-y: auto;">
                                            <?php foreach($coupons as $c): ?>
                                                <?php 
                                                    $desc = ($c['discount_type'] == 'fixed') ? '-'.($c['discount_value']/1000).'k' : '-'.$c['discount_value'].'%';
                                                ?>
                                                <div class="border border-warning rounded px-2 py-1 bg-white cursor-pointer hover-shadow" 
                                                     style="border-style: dashed !important;"
                                                     onclick="fillCoupon('<?= $c['code'] ?>')">
                                                    <strong class="text-danger small"><?= $c['code'] ?></strong>
                                                    <small class="text-muted ms-1"><?= $desc ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </li>

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Tạm tính</span>
                                    <strong id="subTotalDisplay"><?= number_format($sub_total, 0, ',', '.') ?> đ</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary">Phí vận chuyển</span>
                                    <strong id="shippingDisplay">+ <?= number_format($shipping_fee, 0, ',', '.') ?> đ</strong>
                                </div>
                                
                                <div id="discountRow" class="d-flex justify-content-between mb-1 text-success <?= ($discount_amount > 0) ? '' : 'd-none' ?>">
                                    <span>Giảm giá</span>
                                    <strong>- <span id="discountAmount"><?= number_format($discount_amount, 0, ',', '.') . ' đ' ?></span></strong>
                                </div>
                            </li>

                            <li class="list-group-item bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">TỔNG CỘNG</span>
                                    <strong class="text-danger fs-4" id="totalPriceDisplay"><?= number_format($total_price, 0, ',', '.') ?> đ</strong>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer bg-white p-3">
                    <button type="submit" form="checkoutForm" class="btn btn-success w-100 py-3 fw-bold text-uppercase shadow-sm">
                        Đặt hàng ngay
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Điền mã coupon vào ô input
    function fillCoupon(code) {
        document.getElementById('couponInput').value = code;
    }

    // 2. Ẩn hiện form địa chỉ mới
    function toggleNewAddress(show) {
        const form = document.getElementById('newAddressForm');
        const inputs = form.querySelectorAll('input, textarea');
        
        if (show) {
            form.classList.remove('d-none');
            // Bật required
            inputs.forEach(el => {
                if(el.type !== 'checkbox') el.required = true;
            });
        } else {
            form.classList.add('d-none');
            // Tắt required để submit không bị lỗi
            inputs.forEach(el => el.required = false);
        }
    }

    // 3. Xử lý AJAX Coupon (Quan trọng)
    document.addEventListener('DOMContentLoaded', function() {
        
        // Khởi tạo trạng thái form địa chỉ ban đầu
        const isNewAddress = document.getElementById('radioNewAddress').checked;
        toggleNewAddress(isNewAddress);

        // Đảm bảo checkbox save_address luôn được gửi trong form (nếu checked)
        const checkoutForm = document.getElementById('checkoutForm');
        const saveAddressCheckbox = document.getElementById('saveAddr');
        
        checkoutForm.addEventListener('submit', function(e) {
            // Nếu checkbox được check, đảm bảo nó có value='1' được gửi
            if (saveAddressCheckbox && saveAddressCheckbox.checked) {
                // Checkbox sẽ tự động gửi value khi checked
                console.log('Save address checked: YES');
            } else {
                console.log('Save address checked: NO');
            }
        });

        // Bắt sự kiện submit form Coupon
        const couponForm = document.getElementById('couponForm');
        
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Chặn reload trang
            
            const btn = document.getElementById('btnApplyCoupon');
            const originalText = btn.innerHTML;
            const formData = new FormData(this);

            // Hiệu ứng loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch('/checkout/apply_coupon', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    // A. Thông báo thành công
                    showToast(data.message);
                    
                    // B. Cập nhật giao diện số tiền (Lấy từ JSON trả về)
                    updateOrderSummary(data);

                    // C. Hiển thị box coupon đã áp dụng
                    document.getElementById('couponAlert').classList.remove('d-none');
                    document.getElementById('appliedCouponCode').innerText = data.coupon.code;
                    document.getElementById('couponInput').value = ''; // Xóa ô input
                } else {
                    // Thông báo lỗi
                    showToast(data.message, 'danger');
                    // Reset về giá gốc (do server trả về giá gốc khi lỗi)
                    updateOrderSummary(data);
                    
                    // Ẩn box coupon
                    document.getElementById('couponAlert').classList.add('d-none');
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showToast('Có lỗi xảy ra, vui lòng thử lại!', 'danger');
            });
        });
    });

    // Hàm hiển thị toast
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
        toastMessage.textContent = message;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // 4. Hàm cập nhật lại các con số trên giao diện
    function updateOrderSummary(data) {
        // Cập nhật Tổng tiền & Giảm giá bằng chuỗi formatted từ server
        document.getElementById('totalPriceDisplay').innerText = data.total_formatted;
        
        // Xử lý dòng Discount
        const discountRow = document.getElementById('discountRow');
        const discountAmount = document.getElementById('discountAmount');
        
        if (data.discount_amount > 0) {
            discountRow.classList.remove('d-none');
            discountAmount.innerText = data.discount_formatted;
        } else {
            discountRow.classList.add('d-none');
            discountAmount.innerText = '0 đ';
        }
    }

    // 5. Hàm xóa Coupon (Bạn cần xử lý logic này bên server nếu muốn hoàn hảo)
    // Ở đây mình mẹo dùng lại hàm apply_coupon với mã rỗng hoặc sai để reset
    function removeCoupon() {
        if(!confirm('Bạn muốn bỏ mã giảm giá này?')) return;

        const formData = new FormData();
        formData.append('coupon_code', ''); // Gửi mã rỗng
        // CSRF Token lấy từ input hidden có sẵn trong form checkout
        const csrf = document.querySelector('input[name="csrf_token"]').value;
        formData.append('csrf_token', csrf);

        fetch('/checkout/apply_coupon', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            // Khi gửi mã rỗng, controller (đã sửa ở bước trước) sẽ trả về success=false và giá gốc
            // Chúng ta dùng data đó để reset giao diện
            updateOrderSummary(data);
            document.getElementById('couponAlert').classList.add('d-none');
            document.getElementById('couponInput').value = '';
        });
    }
</script>

<!-- Toast notification -->
<div id="toast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
    <div class="d-flex">
        <div class="toast-body" id="toastMessage"></div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<style>
    .hover-shadow:hover { box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important; background-color: #fff3cd !important; }
    .cursor-pointer { cursor: pointer; }
    /* CSS Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>