<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Phiếu Xuất Kho - Camping Shop Admin</title>
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
        <?php $active = 'stock_issue'; require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    </div>

<div class="d-flex">
    <div class="main-content w-100 p-4 bg-light">
        
        <form action="/admin/StockIssue/store" method="POST" id="formStockIssue">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-danger mb-1">
                        <i class="fa-solid fa-dolly me-2"></i>Tạo Phiếu Xuất Kho
                    </h3>
                    <p class="text-muted mb-0">Xuất hàng bán, xuất hủy hoặc xuất dùng nội bộ.</p>
                </div>
                <a href="/admin/StockIssue" class="btn btn-outline-secondary shadow-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-danger">
                                <i class="fa-solid fa-circle-info me-2"></i>Thông tin phiếu
                            </h6>
                        </div>
                        <div class="card-body">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày xuất</label>
                                <input type="text" class="form-control bg-light" value="<?= date('d/m/Y H:i') ?>" readonly>
                                <div class="form-text small">Thời gian được lấy tự động hiện tại.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Người thực hiện</label>
                                <input type="text" class="form-control bg-light" value="<?= $_SESSION['user']['full_name'] ?? 'Admin' ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label fw-bold">Lý do xuất kho / Ghi chú <span class="text-danger">*</span></label>
                                <textarea name="note" id="note" class="form-control" rows="5" placeholder="Ví dụ: Xuất bán đơn hàng #123, Xuất hủy hàng hỏng..." required></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-danger">
                                <i class="fa-solid fa-list me-2"></i>Chi tiết hàng hóa
                            </h6>
                            <button type="button" class="btn btn-sm btn-danger shadow-sm" id="btnAddRow">
                                <i class="fa fa-plus me-1"></i> Thêm dòng
                            </button>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0 align-middle" id="tblProducts">
                                    <thead class="table-light text-secondary small">
                                        <tr>
                                            <th style="width: 40%;">Sản phẩm</th>
                                            <th style="width: 15%;" class="text-center">Tồn kho</th>
                                            <th style="width: 15%;">Số lượng xuất</th>
                                            <th style="width: 20%;">Giá vốn (VNĐ)</th>
                                            <th style="width: 10%;" class="text-center">Xóa</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-list">
                                        <tr class="product-row">
                                            <td>
                                                <select name="product_id[]" class="form-select product-select" required onchange="updateProductInfo(this)">
                                                    <option value="">-- Chọn sản phẩm --</option>
                                                    <?php if (!empty($products)): ?>
                                                        <?php foreach ($products as $p): ?>
                                                            <option value="<?= $p['id'] ?>" 
                                                                    data-stock="<?= $p['stock'] ?? 0 ?>" 
                                                                    data-price="<?= $p['price'] ?? 0 ?>">
                                                                <?= htmlspecialchars($p['name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary stock-display">0</span>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control text-center quantity-input" value="1" min="1" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-end price-display" value="0" data-raw="0" onchange="updatePriceValue(this)">
                                                <input type="hidden" name="price[]" class="price-input" value="0">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-white p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-light border" onclick="window.location.reload()">
                                        <i class="fa-solid fa-rotate me-1"></i> Làm mới
                                    </button>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-2"></i> LƯU & XUẤT KHO
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div> 
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAddRow = document.getElementById('btnAddRow');
        const productList = document.getElementById('product-list');

        // 1. Chức năng Thêm dòng mới
        btnAddRow.addEventListener('click', function() {
            const firstRow = document.querySelector('.product-row');
            if (firstRow) {
                const newRow = firstRow.cloneNode(true);
                
                // Reset giá trị của dòng mới
                newRow.querySelector('select').value = "";
                newRow.querySelector('.stock-display').innerText = "0";
                newRow.querySelector('.quantity-input').value = "1";
                newRow.querySelector('.price-display').value = "0";
                newRow.querySelector('.price-display').setAttribute('data-raw', "0");
                newRow.querySelector('.price-input').value = "0";
                
                productList.appendChild(newRow);
                attachEvents(); // Gán lại sự kiện cho nút xóa
            }
        });

        // 2. Hàm xử lý khi chọn sản phẩm
        window.updateProductInfo = function(selectElement) {
            const row = selectElement.closest('tr');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            
            // Lấy dữ liệu từ data-attributes
            const stock = selectedOption.getAttribute('data-stock') || 0;
            const price = selectedOption.getAttribute('data-price') || 0;

            // Cập nhật giao diện
            row.querySelector('.stock-display').innerText = stock;
            
            // Format tiền tệ Việt Nam
            const formattedPrice = new Intl.NumberFormat('vi-VN').format(price);
            row.querySelector('.price-display').value = formattedPrice;
            row.querySelector('.price-display').setAttribute('data-raw', price);
            row.querySelector('.price-input').value = price;
        };

        // 2b. Hàm xử lý thay đổi giá xuất
        window.updatePriceValue = function(inputElement) {
            const row = inputElement.closest('tr');
            const priceText = inputElement.value.replace(/\./g, '').replace(/,/g, '');
            const priceNum = parseInt(priceText) || 0;
            
            // Format và cập nhật hiển thị
            const formattedPrice = new Intl.NumberFormat('vi-VN').format(priceNum);
            inputElement.value = formattedPrice;
            inputElement.setAttribute('data-raw', priceNum);
            
            // Cập nhật giá trị hidden
            row.querySelector('.price-input').value = priceNum;
        };

        // 3. Xử lý nút Xóa dòng
        function attachEvents() {
            const removeButtons = document.querySelectorAll('.btn-remove');
            removeButtons.forEach(btn => {
                btn.onclick = function() {
                    if (document.querySelectorAll('.product-row').length > 1) {
                        this.closest('tr').remove();
                    } else {
                        alert("Không thể xóa dòng cuối cùng!");
                    }
                };
            });
        }

        // Khởi chạy sự kiện lần đầu
        attachEvents();
    });
</script>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>