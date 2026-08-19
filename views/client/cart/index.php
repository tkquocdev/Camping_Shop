<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="position-relative text-white mb-4" 
     style="background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%); padding: 40px 0;">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-0 text-uppercase"><i class="fa-solid fa-bag-shopping me-2"></i> Giỏ hàng</h2>
        </div>
    </div>
</div>

<div class="container pb-5">
    
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_products)): ?>
        <div class="text-center py-5 bg-white rounded shadow-sm border">
            <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" width="100" alt="Empty Cart" class="mb-3 opacity-50">
            <h4 class="mt-3 text-secondary">Giỏ hàng đang trống</h4>
            <a href="/product" class="btn btn-primary px-4 rounded-pill fw-bold mt-3">Quay lại cửa hàng</a>
        </div>
    <?php else: ?>

        <form action="/checkout" method="POST" id="cartForm">
            <?php echo \App\Core\Csrf::getHtmlInput(); ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label fw-bold cursor-pointer" for="checkAll">Chọn tất cả</label>
                        </div>
                        
                        <a href="/cart/clear" class="text-danger text-decoration-none small fw-bold" onclick="return confirm('Bạn chắc chắn muốn xóa toàn bộ giỏ hàng?')">
                            <i class="fa-solid fa-trash-can"></i> Xóa tất cả
                        </a>
                    </div>

                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-secondary small text-uppercase">
                                        <tr>
                                            <th width="40"></th> <th width="100">Ảnh</th>
                                            <th>Thông tin</th>
                                            <th>Đơn giá</th>
                                            <th width="120">Số lượng</th>
                                            <th width="50"></th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_products as $item): ?>
                                        
                                        <?php 
                                            $imgSrc = $item['image'];
                                            if (!str_contains($imgSrc, 'http')) { $imgSrc = '/uploads/products/' . $imgSrc; }
                                        ?>

                                        <tr class="cart-item-row">
                                            <td class="ps-3">
                                                <input class="form-check-input item-checkbox" 
                                                       type="checkbox" 
                                                       name="selected_items[]" 
                                                       value="<?= $item['id'] ?>"
                                                       data-price="<?= $item['price'] ?>">
                                            </td>

                                            <td class="py-3">
                                                <img src="<?= $imgSrc ?>" class="img-thumbnail rounded" width="80" height="80" style="object-fit: cover;">
                                            </td>
                                            
                                            <td>
                                                <a href="/product/detail/<?= $item['id'] ?>" class="text-decoration-none text-dark fw-bold"><?= $item['name'] ?></a>
                                                <br>
                                                <small class="text-muted"><?= $item['category_name'] ?? 'Khác' ?></small>
                                            </td>
                                            
                                            <td class="text-secondary fw-bold">
                                                <?= number_format($item['price']) ?> đ
                                            </td>

                                            <td>
                                                <div class="d-flex border rounded" style="width: 100px;">
                                                    <input type="number" 
                                                           name="quantities[<?= $item['id'] ?>]" 
                                                           value="<?= $item['buy_quantity'] ?>" 
                                                           min="1" 
                                                           class="form-control form-control-sm border-0 text-center fw-bold qty-input"
                                                           data-id="<?= $item['id'] ?>">
                                                </div>
                                            </td>
                                            
                                            <td class="text-end pe-4">
                                                <a href="/cart/remove/<?= $item['id'] ?>" class="text-secondary hover-danger" onclick="return confirm('Xóa sản phẩm này?')">
                                                    <i class="fa-solid fa-trash-can fa-lg"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 90px;">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Thanh toán</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">Đã chọn:</span>
                                <span class="fw-bold"><span id="selectedCount">0</span> sản phẩm</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <span class="text-muted fw-bold">Tạm tính:</span>
                                <span class="fw-bold text-danger fs-4" id="provisionalTotal">0 đ</span>
                            </div>

                            <?php if(isset($_SESSION['user'])): ?>
                                <button type="submit" class="btn btn-dark w-100 py-3 fw-bold text-uppercase shadow-sm" onclick="return validateCheckout()">
                                    Mua hàng <i class="fa-solid fa-arrow-right ms-2"></i>
                                </button>
                            <?php else: ?>
                                <a href="/auth/login" class="btn btn-primary w-100 py-3 fw-bold">Đăng nhập để mua</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
</div>

<script>
    // 1. Định dạng tiền tệ VNĐ
    const formatter = new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
    });

    // 2. Hàm tính tổng tiền hiển thị (Frontend)
    function calculateTotal() {
        let total = 0;
        let count = 0;
        
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        
        checkboxes.forEach(cb => {
            count++;
            const price = parseFloat(cb.getAttribute('data-price'));
            // Tìm ô số lượng tương ứng
            const row = cb.closest('tr');
            const qtyInput = row.querySelector('.qty-input');
            const quantity = parseInt(qtyInput.value);
            
            total += price * quantity;
        });

        document.getElementById('selectedCount').innerText = count;
        document.getElementById('provisionalTotal').innerText = formatter.format(total);
    }

    // 3. Xử lý Checkbox chọn tất cả
    document.getElementById('checkAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        calculateTotal();
    });

    // 4. Xử lý Checkbox con
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) document.getElementById('checkAll').checked = false;
            
            const allChecked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
            if(allChecked && itemCheckboxes.length > 0) document.getElementById('checkAll').checked = true;

            calculateTotal();
        });
    });

    // 5. Xử lý cập nhật số lượng bằng AJAX (Fetch)
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const qty = this.value;
            
            // Cập nhật lại tổng tiền trên giao diện NGAY LẬP TỨC
            calculateTotal();

            // Gửi dữ liệu về Server để lưu Session (Background update)
            const formData = new FormData();
            formData.append('product_id', id);
            formData.append('quantity', qty);
            
            // Lấy CSRF token từ form hoặc meta tag
            const csrfToken = document.querySelector('input[name="_csrf_token"]') || 
                            document.querySelector('input[name="csrf_token"]');
            if (csrfToken) {
                formData.append('csrf_token', csrfToken.value);
            }

            fetch('/cart/update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Cập nhật giỏ hàng thành công');
            })
            .catch(error => {
                console.error('Lỗi cập nhật:', error);
            });
        });
    });

    // Validate trước khi submit
    function validateCheckout() {
        const count = document.querySelectorAll('.item-checkbox:checked').length;
        if (count === 0) {
            // alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán!');
            return false;
        }
        return true;
    }

    // Chạy tính toán lần đầu khi load trang
    window.addEventListener('load', calculateTotal);
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>