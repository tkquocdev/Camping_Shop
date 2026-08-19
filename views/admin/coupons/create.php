<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Mã Giảm Giá - Camping Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { margin: 0; background-color: #f8f9fa; }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            min-width: 280px;
            background-color: #343a40;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 300px;
            padding: 20px;
        }

        @media (max-width: 992px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="sidebar">
    <?php $active = 'coupons'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fa-solid fa-ticket me-2"></i>Tạo Mã Giảm Giá
            </h3>
            <p class="text-muted mb-0">Thiết lập mã khuyến mãi và voucher cho khách hàng</p>
        </div>
        <a href="/admin/coupons" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="/admin/coupons/store" method="POST">
            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            
                            <h6 class="fw-bold text-primary mb-3 text-uppercase small">Thông tin cơ bản</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Mã Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="code" id="inputCode" class="form-control text-uppercase fw-bold text-primary" 
                                               placeholder="VD: SALE2025" required oninput="updatePreview()">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mã viết liền không dấu.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tên hiển thị <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="inputName" class="form-control" 
                                           placeholder="VD: Voucher trúng thưởng 50K" required oninput="updatePreview()">
                                    <div class="form-text">Tên hiển thị cho khách hàng thấy.</div>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25 my-3">

                            <h6 class="fw-bold text-primary mb-3 text-uppercase small">Giá trị ưu đãi</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Loại giảm <span class="text-danger">*</span></label>
                                    <select name="discount_type" id="inputType" class="form-select" onchange="updateLabelAndPreview()">
                                        <option value="fixed">Giảm tiền (VNĐ)</option>
                                        <option value="amount">Giảm theo %</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold" id="valueLabel">Số tiền giảm <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_value" id="inputValue" class="form-control" 
                                           placeholder="VD: 50000" required min="0" oninput="updatePreview()">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Đơn tối thiểu</label>
                                    <div class="input-group">
                                        <input type="number" name="min_order_value" id="inputMinOrder" class="form-control" 
                                               value="0" min="0" oninput="updatePreview()">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                    <div class="form-text">Nhập 0 nếu áp dụng mọi đơn hàng.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Số lượng phát hành</label>
                                    <input type="number" name="quantity" class="form-control" value="100" min="1">
                                </div>
                            </div>

                            <hr class="text-muted opacity-25 my-3">

                            <h6 class="fw-bold text-primary mb-3 text-uppercase small">Thời gian & Cài đặt</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày bắt đầu</label>
                                    <input type="datetime-local" name="start_date" class="form-control" 
                                           value="<?= date('Y-m-d\TH:i') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="expiration_date" id="inputDate" class="form-control" required oninput="updatePreview()">
                                </div>
                            </div>

                            <div class="bg-light p-3 rounded border mt-2">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="inputStatus" name="status" value="1" checked onchange="updatePreview()">
                                    <label class="form-check-label fw-bold text-success" for="inputStatus">
                                        <i class="fa-solid fa-circle-check me-1"></i> Kích hoạt mã (Đang chạy)
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="inputIsPrivate" name="is_private" value="1" onchange="updatePreview()">
                                    <label class="form-check-label fw-bold text-danger" for="inputIsPrivate">
                                        Mã riêng tư (Dành cho Game/Sự kiện)
                                    </label>
                                </div>
                                <div class="form-text mb-0">
                                    Nếu bật, mã này sẽ <b>không công khai</b>. Khách hàng chỉ dùng được nếu hệ thống (Game Lucky Wheel) trao tặng cho họ.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-3 sticky-top" style="top: 20px;">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-eye me-2"></i>Xem trước Voucher</h6>
                        </div>
                        <div class="card-body bg-light d-flex justify-content-center align-items-center p-4" style="min-height: 350px;">
                            
                            <div class="coupon-preview-card bg-white shadow w-100 position-relative overflow-hidden rounded" style="max-width: 320px;">
                                <div class="position-absolute top-0 start-0 bottom-0 bg-warning" id="previewColorStrip" style="width: 8px;"></div>
                                
                                <div class="p-4 text-center">
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark shadow-sm" id="previewTypeBadge">Giảm tiền</span>
                                        <span class="badge bg-danger text-white shadow-sm d-none" id="previewPrivateBadge">GAME ONLY</span>
                                    </div>
                                    
                                    <h6 class="text-muted fw-bold text-uppercase mb-1" id="previewName" style="font-size: 0.8rem;">TÊN VOUCHER</h6>
                                    
                                    <h3 class="text-danger fw-bold my-1" id="previewCode" style="letter-spacing: 1px;">CODE_DEMO</h3>
                                    
                                    <div class="my-3">
                                        <h2 class="fw-bold text-dark mb-0" id="previewValue">0đ</h2>
                                        <small class="text-muted" id="previewMinOrder">Cho mọi đơn hàng</small>
                                    </div>

                                    <div class="border-top border-dashed my-3"></div>

                                    <p class="text-secondary small mb-3">
                                        <i class="fa-regular fa-clock me-1"></i> HSD: <span id="previewDate">--/--/----</span>
                                    </p>
                                </div>
                            </div>
                            </div>
                        <div class="card-footer bg-white border-top-0 p-3">
                             <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-2"></i> Lưu khuyến mãi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<style>
    .border-dashed { border-top: 2px dashed #dee2e6; }
</style>

<script>
    // 1. Elements
    const inputCode = document.getElementById('inputCode');
    const inputName = document.getElementById('inputName');
    const inputType = document.getElementById('inputType');
    const inputValue = document.getElementById('inputValue');
    const inputMinOrder = document.getElementById('inputMinOrder');
    const inputDate = document.getElementById('inputDate');
    const inputIsPrivate = document.getElementById('inputIsPrivate');

    const previewCode = document.getElementById('previewCode');
    const previewName = document.getElementById('previewName');
    const previewTypeBadge = document.getElementById('previewTypeBadge');
    const previewPrivateBadge = document.getElementById('previewPrivateBadge');
    const previewValue = document.getElementById('previewValue');
    const previewMinOrder = document.getElementById('previewMinOrder');
    const previewDate = document.getElementById('previewDate');
    const valueLabel = document.getElementById('valueLabel');
    const previewColorStrip = document.getElementById('previewColorStrip');

    // 2. Hàm Random Code
    function generateCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let result = '';
        for (let i = 0; i < 8; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        inputCode.value = 'LUCKY-' + result; // Prefix LUCKY cho vui
        updatePreview();
    }

    // 3. Update Label theo Type
    function updateLabelAndPreview() {
        if (inputType.value === 'amount') {
            valueLabel.innerHTML = 'Phần trăm giảm (%) <span class="text-danger">*</span>';
            inputValue.placeholder = "VD: 10 (tức là 10%)";
            if(inputValue.value > 100) inputValue.value = 100;
            inputValue.setAttribute('max', '100');
        } else {
            valueLabel.innerHTML = 'Số tiền giảm (VNĐ) <span class="text-danger">*</span>';
            inputValue.placeholder = "VD: 50000";
            inputValue.removeAttribute('max');
        }
        updatePreview();
    }

    // 4. Update Preview
    function updatePreview() {
        // Code & Name
        previewCode.innerText = inputCode.value ? inputCode.value.toUpperCase() : 'MÃ_CỦA_BẠN';
        previewName.innerText = inputName.value ? inputName.value : 'TÊN VOUCHER';

        // Type Badge
        previewTypeBadge.innerText = (inputType.value === 'fixed') ? 'Giảm tiền' : 'Giảm giá %';

        // Private Badge Check
        if (inputIsPrivate.checked) {
            previewPrivateBadge.classList.remove('d-none');
            previewColorStrip.classList.remove('bg-warning');
            previewColorStrip.classList.add('bg-danger');
        } else {
            previewPrivateBadge.classList.add('d-none');
            previewColorStrip.classList.add('bg-warning');
            previewColorStrip.classList.remove('bg-danger');
        }

        // Value Big Text
        let val = inputValue.value ? parseFloat(inputValue.value) : 0;
        if (inputType.value === 'fixed') {
            previewValue.innerText = 'Giảm ' + new Intl.NumberFormat('vi-VN').format(val) + 'đ';
        } else {
            previewValue.innerText = 'Giảm ' + val + '%';
        }

        // Min Order
        let min = inputMinOrder.value ? parseFloat(inputMinOrder.value) : 0;
        if (min > 0) {
            previewMinOrder.innerText = 'Đơn từ ' + new Intl.NumberFormat('vi-VN').format(min) + ' đ';
        } else {
            previewMinOrder.innerText = 'Cho mọi đơn hàng';
        }
        
        // Date
        if (inputDate.value) {
            const dateObj = new Date(inputDate.value);
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            previewDate.innerText = `${day}/${month}/${year}`;
        } else {
            previewDate.innerText = "--/--/----";
        }
    }

    // Init
    updateLabelAndPreview();
</script>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>