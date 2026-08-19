<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Nhập Kho - Camping Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 300px; background-color: #343a40; overflow-y: auto; }
        .main-content { margin-left: 300px; padding: 20px; min-height: 100vh; }
        @media (max-width: 992px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'stock'; require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    </div>

<div class="d-flex">
    <div class="main-content w-100 p-4" style="background-color: #f4f6f9;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Tạo Phiếu Nhập Kho</h4>
                <p class="text-muted small mb-0">Nhập hàng mới vào hệ thống quản lý</p>
            </div>
            <a href="/admin/stock" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <form action="/admin/stock/store" method="POST" id="importForm">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info me-2 text-info"></i>Thông tin phiếu</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nhà cung cấp <span class="text-danger">*</span></label>
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">-- Chọn Nhà cung cấp --</option>
                                    <?php if(!empty($data['suppliers'])): ?>
                                        <?php foreach($data['suppliers'] as $s): ?>
                                            <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày nhập</label>
                                <input type="text" class="form-control bg-light" value="<?= date('d/m/Y H:i') ?>" readonly>
                                <small class="text-muted">Thời gian được lấy tự động hiện tại.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú / Mã lô hàng</label>
                                <textarea name="note" class="form-control" rows="4" placeholder="Ví dụ: Nhập hàng mùa đông đợt 1..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Chi tiết hàng hóa</h6>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="addRow">
                                <i class="fa fa-plus me-1"></i> Thêm dòng
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered mb-0 align-middle" id="productTable">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 40%;">Sản phẩm</th>
                                            <th style="width: 15%;">Số lượng</th>
                                            <th style="width: 20%;">Đơn giá nhập</th>
                                            <th style="width: 20%;">Thành tiền</th>
                                            <th style="width: 5%;">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="product-row">
                                            <td>
                                                <select name="product_id[]" class="form-select form-select-sm" required>
                                                    <option value="">Chọn sản phẩm...</option>
                                                    <?php if(!empty($data['products'])): ?>
                                                        <?php foreach($data['products'] as $p): ?>
                                                            <option value="<?= $p['id'] ?>">
                                                                <?= htmlspecialchars($p['name']) ?> (Tồn: <?= $p['stock'] ?? 0 ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control form-select-sm text-center quantity-input" value="1" min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="price[]" class="form-control form-select-sm text-end price-input" placeholder="0" min="0" required>
                                            </td>
                                            <td class="text-end fw-bold text-success subtotal-display">0 đ</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-light text-danger btn-sm remove-row border">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold text-uppercase">Tổng cộng thanh toán:</td>
                                            <td class="text-end fw-bold text-danger fs-5" id="grandTotal">0 đ</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white p-3 text-end">
                            <button type="reset" class="btn btn-light me-2">Làm mới</button>
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Lưu & Nhập Kho
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#productTable tbody');
    const grandTotalEl = document.getElementById('grandTotal');
    const addBtn = document.getElementById('addRow');

    // Hàm định dạng tiền tệ VNĐ
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    };

    // Hàm tính toán tổng tiền
    const calculateTotal = () => {
        let total = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;
            
            // Hiển thị thành tiền từng dòng
            row.querySelector('.subtotal-display').textContent = formatCurrency(subtotal);
            total += subtotal;
        });
        // Hiển thị tổng cộng
        grandTotalEl.textContent = formatCurrency(total);
    };

    // Lắng nghe sự kiện input để tính tiền ngay lập tức
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input')) {
            calculateTotal();
        }
    });

    // Thêm dòng mới
    addBtn.addEventListener('click', function() {
        const firstRow = document.querySelector('.product-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset giá trị input trong dòng mới
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelector('.quantity-input').value = 1;
        newRow.querySelector('.subtotal-display').textContent = '0 đ';
        
        tableBody.appendChild(newRow);
    });

    // Xóa dòng
    document.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-row');
        if (removeBtn) {
            const rows = document.querySelectorAll('.product-row');
            if (rows.length > 1) {
                removeBtn.closest('tr').remove();
                calculateTotal();
            } else {
                alert('Phải có ít nhất một sản phẩm để nhập kho!');
            }
        }
    });
});
</script>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>