<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($sale) ? 'Cập nhật Flash Sale' : 'Tạo Flash Sale' ?> - Camping Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 300px;
            background-color: #343a40;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 300px;
            padding: 20px;
            min-height: 100vh;
        }
        .sale-price-input { position: relative; }
        .discount-badge {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            transform: translateY(-2px);
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
        <?php $active = 'flash_sale'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <?php 
            $isEdit = isset($sale);
            $title = $isEdit ? 'Cập nhật Flash Sale' : 'Tạo Flash Sale Mới';
            $subtitle = $isEdit ? 'Điều chỉnh thông tin chương trình #' . $sale['id'] : 'Tạo một chương trình khuyến mãi chớp nhoáng mới';
        ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid <?= $isEdit ? 'fa-pen-to-square' : 'fa-bolt' ?> me-2"></i> <?= $title ?>
                </h3>
                <p class="text-muted mb-0"><?= $subtitle ?></p>
            </div>
            <a href="/admin/flash_sale" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-circle me-2"></i> <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Basic Info Section -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-info-circle me-2"></i>Thông tin chương trình</h5>
                
                <form action="<?= $isEdit ? '/admin/flash_sale/update/' . $sale['id'] : '/admin/flash_sale/store' ?>" method="POST" id="basicForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên chương trình <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required 
                               placeholder="VD: Flash Sale - Mùa Hè 2026"
                               value="<?= $isEdit ? htmlspecialchars($sale['name']) : '' ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Thời gian bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" class="form-control" required
                                   value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($sale['start_time'])) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Thời gian kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_time" class="form-control" required
                                   value="<?= $isEdit ? date('Y-m-d\TH:i', strtotime($sale['end_time'])) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="1" id="statusActive" 
                                       <?= !$isEdit || $sale['status'] == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="statusActive">
                                    <i class="fa-solid fa-circle-check text-success me-2"></i> Kích hoạt
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" value="0" id="statusInactive"
                                       <?= $isEdit && $sale['status'] == 0 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="statusInactive">
                                    <i class="fa-solid fa-circle-xmark text-danger me-2"></i> Ẩn
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-2"></i> <?= $isEdit ? 'Cập nhật' : 'Tiếp tục' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Section -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light py-3">
                <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-box me-2"></i>Sản phẩm trong Flash Sale</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($isEdit): ?>
                    <!-- Edit Mode: Show existing products + add new -->
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus me-2"></i>Thêm sản phẩm</h6>
                                </div>
                                <div class="card-body">
                                    <form action="/admin/flash_sale/add_item" method="POST" id="addProductForm">
                                        <input type="hidden" name="flash_sale_id" value="<?= $sale['id'] ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Chọn sản phẩm</label>
                                            <select name="product_id" id="productSelect" class="form-select" required>
                                                <option value="">-- Chọn sản phẩm --</option>
                                                <?php foreach ($all_products as $p): ?>
                                                    <option value="<?= $p['id'] ?>" 
                                                            data-price="<?= $p['price'] ?>"
                                                            data-img="<?= !empty($p['image']) ? $p['image'] : 'https://placehold.co/50x50' ?>"
                                                            data-name="<?= htmlspecialchars($p['name']) ?>">
                                                        <?= $p['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div id="productPreview" class="d-none bg-light p-3 rounded mb-3 border text-center">
                                            <img id="previewImg" src="" class="rounded mb-2" width="80" height="80">
                                            <h6 class="small fw-bold mb-1" id="previewName">Tên SP</h6>
                                            <div class="text-muted small">Giá gốc: <span class="fw-bold text-dark" id="previewPriceOrigin">0</span>đ</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Giá Flash Sale (đ)</label>
                                            <div class="input-group">
                                                <input type="number" name="sale_price" id="salePriceInput" class="form-control fw-bold text-danger" min="1000" placeholder="Nhập giá" required>
                                                <span class="input-group-text bg-white text-danger fw-bold" id="discountBadge">-0%</span>
                                            </div>
                                            <div id="priceWarning" class="form-text text-danger d-none"><i class="fa-solid fa-triangle-exclamation me-1"></i>Giá Sale cao hơn giá gốc!</div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold small">Số lượng tối đa</label>
                                            <input type="number" name="quantity" class="form-control" value="10" min="1" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                            <i class="fa-solid fa-check me-2"></i>Thêm sản phẩm
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white py-3">
                                    <h6 class="fw-bold mb-0 text-dark">Danh sách sản phẩm (<?= isset($sale_items) ? count($sale_items) : 0 ?>)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="bg-light text-secondary small text-uppercase">
                                                <tr>
                                                    <th class="ps-4">Sản phẩm</th>
                                                    <th>Giá & Giảm</th>
                                                    <th style="width: 200px;">Tiến độ bán</th>
                                                    <th class="text-center">Xóa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($sale_items)): ?>
                                                    <?php foreach ($sale_items as $item): ?>
                                                        <?php 
                                                            $percentSold = ($item['quantity'] > 0) ? ($item['sold'] / $item['quantity']) * 100 : 0;
                                                            $discountPercent = 0;
                                                            if($item['original_price'] > 0) {
                                                                $discountPercent = round((($item['original_price'] - $item['sale_price']) / $item['original_price']) * 100);
                                                            }
                                                            $imgSrc = !empty($item['product_image']) ? $item['product_image'] : 'https://placehold.co/50';
                                                        ?>
                                                        <tr>
                                                            <td class="ps-4 py-3">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="rounded border me-3" width="50" height="50">
                                                                    <div>
                                                                        <div class="fw-bold text-dark text-truncate" style="max-width: 200px;">
                                                                            <?= htmlspecialchars($item['product_name']) ?>
                                                                        </div>
                                                                        <span class="badge bg-light text-secondary border">ID: <?= $item['product_id'] ?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-muted text-decoration-line-through small">
                                                                        <?= number_format($item['original_price']) ?>đ
                                                                    </span>
                                                                    <span class="text-danger fw-bold">
                                                                        <?= number_format($item['sale_price']) ?>đ
                                                                    </span>
                                                                    <span class="badge bg-danger bg-opacity-10 text-danger mt-1" style="width: fit-content;">
                                                                        -<?= $discountPercent ?>%
                                                                    </span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex justify-content-between small mb-1">
                                                                    <span class="text-muted">Đã bán: <b><?= $item['sold'] ?></b></span>
                                                                    <span class="text-muted">Tổng: <b><?= $item['quantity'] ?></b></span>
                                                                </div>
                                                                <div class="progress" style="height: 6px;">
                                                                    <div class="progress-bar bg-warning" role="progressbar" 
                                                                         style="width: <?= $percentSold ?>%" 
                                                                         aria-valuenow="<?= $percentSold ?>" aria-valuemin="0" aria-valuemax="100">
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center">
                                                                <button onclick="removeItem(<?= $item['id'] ?>)" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                                    <i class="fa-solid fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-muted">Chưa có sản phẩm nào</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Create Mode: Bulk selection from categories -->
                    <p class="text-muted mb-4">Bạn có thể thêm sản phẩm ngay bây giờ hoặc thêm sau khi tạo chương trình</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn danh mục</label>
                        <select id="categorySelect" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                        </select>
                    </div>

                    <div id="productList" class="mb-3">
                        <div class="alert alert-info">
                            <i class="fa-solid fa-arrow-pointers me-2"></i> Chọn danh mục để xem sản phẩm
                        </div>
                    </div>

                    <div id="selectedProducts" class="mb-3">
                        <h6 class="fw-bold">Sản phẩm đã chọn:</h6>
                        <div id="selectedList" class="border rounded p-3 bg-light"></div>
                    </div>

                    <input type="hidden" id="productsJson" name="products" value="[]">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($isEdit): ?>
            // Edit mode scripts
            setupEditMode();
        <?php else: ?>
            // Create mode scripts
            setupCreateMode();
        <?php endif; ?>
    });

    function setupEditMode() {
        const productSelect = document.getElementById('productSelect');
        const salePriceInput = document.getElementById('salePriceInput');
        const productPreview = document.getElementById('productPreview');
        const previewImg = document.getElementById('previewImg');
        const previewName = document.getElementById('previewName');
        const previewPriceOrigin = document.getElementById('previewPriceOrigin');
        const discountBadge = document.getElementById('discountBadge');
        const priceWarning = document.getElementById('priceWarning');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value === '') {
                productPreview.classList.add('d-none');
                return;
            }

            const price = selectedOption.dataset.price;
            const img = selectedOption.dataset.img;
            const name = selectedOption.dataset.name;

            previewImg.src = img;
            previewName.textContent = name;
            previewPriceOrigin.textContent = parseInt(price).toLocaleString();

            productPreview.classList.remove('d-none');
            salePriceInput.focus();
        });

        salePriceInput.addEventListener('input', function() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const originalPrice = parseInt(selectedOption.dataset.price) || 0;
            const salePrice = parseInt(this.value) || 0;

            if (salePrice > originalPrice && salePrice > 0) {
                priceWarning.classList.remove('d-none');
                discountBadge.textContent = '+' + ((salePrice - originalPrice) / originalPrice * 100).toFixed(0) + '%';
                discountBadge.classList.remove('text-danger');
                discountBadge.classList.add('text-warning');
            } else if (salePrice > 0) {
                priceWarning.classList.add('d-none');
                const discount = Math.round(((originalPrice - salePrice) / originalPrice) * 100);
                discountBadge.textContent = '-' + discount + '%';
                discountBadge.classList.remove('text-warning');
                discountBadge.classList.add('text-danger');
            } else {
                discountBadge.textContent = '-0%';
            }
        });

        // Handle add product form submission
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/admin/flash_sale/add_item', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Thêm sản phẩm thành công!');
                    location.reload(); // Reload to show the new product
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm'));
                }
            })
            .catch(err => {
                console.error('Lỗi:', err);
                alert('Có lỗi xảy ra khi thêm sản phẩm');
            });
        });
    }

    function calculateDiscount() {
        const productId = this.dataset.id;
        const originalPrice = parseFloat(this.dataset.original);
        const salePrice = parseFloat(this.value);

        if (salePrice && salePrice > 0 && originalPrice) {
            const discountPercent = Math.round(((originalPrice - salePrice) / originalPrice) * 100);
            const discountLabel = document.querySelector(`.discount-label-${productId}`);
            const discountPercent_el = document.querySelector(`.discount-percent-${productId}`);
            
            if (discountPercent > 0) {
                discountLabel.classList.remove('d-none');
                discountPercent_el.textContent = discountPercent;
            } else {
                discountLabel.classList.add('d-none');
            }
        } else {
            document.querySelector(`.discount-label-${productId}`).classList.add('d-none');
        }
    }

    function updateSelectedProducts() {
        const selected = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            const id = checkbox.dataset.id;
            const salePrice = document.querySelector(`.sale-price[data-id="${id}"]`).value;
            const saleQty = document.querySelector(`.sale-qty[data-id="${id}"]`).value;
            
            if (salePrice && saleQty) {
                selected.push({
                    product_id: parseInt(id),
                    sale_price: parseInt(salePrice),
                    quantity: parseInt(saleQty)
                });
            }
        });

        document.getElementById('productsJson').value = JSON.stringify(selected);

        let html = '';
        if (selected.length === 0) {
            html = '<p class="text-muted"><i class="fa-solid fa-info-circle me-2"></i>Chưa chọn sản phẩm nào</p>';
        } else {
            html = '<ul class="list-group">';
            selected.forEach(item => {
                const originalPrice = parseInt(document.querySelector(`[data-id="${item.product_id}"][data-original]`).dataset.original);
                const discountPercent = Math.round(((originalPrice - item.sale_price) / originalPrice) * 100);
                const productName = document.querySelector(`[data-id="${item.product_id}"][data-name]`).dataset.name;
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">${productName}</div>
                        <small class="text-muted">${item.quantity} × ${parseInt(item.sale_price).toLocaleString()}đ</small>
                    </div>
                    <span class="badge bg-success">${discountPercent}% OFF</span>
                </li>`;
            });
            html += '</ul>';
        }
        document.getElementById('selectedList').innerHTML = html;
    }

    function removeItem(itemId) {
        if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi đợt sale?')) {
            const formData = new FormData();
            formData.append('item_id', itemId);

            fetch('/admin/flash_sale/remove_item', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa sản phẩm'));
                }
            })
            .catch(err => console.error('Lỗi:', err));
        }
    }

    function setupCreateMode() {
        const categorySelect = document.getElementById('categorySelect');
        const productList = document.getElementById('productList');

        // Load categories on page load
        fetch('/admin/flash_sale/get_categories')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.categories.length > 0) {
                    data.categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        categorySelect.appendChild(option);
                    });
                }
            })
            .catch(err => console.error('Lỗi tải danh mục:', err));

        // Load products when category changes
        categorySelect.addEventListener('change', function() {
            if (!this.value) {
                productList.innerHTML = '<div class="alert alert-info"><i class="fa-solid fa-arrow-pointers me-2"></i> Chọn danh mục để xem sản phẩm</div>';
                return;
            }

            fetch(`/admin/flash_sale/get_products?category_id=${this.value}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.products.length > 0) {
                        let html = '<div class="row g-3">';
                        data.products.forEach(product => {
                            html += `
                                <div class="col-12">
                                    <div class="card shadow-sm border-0 p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input product-checkbox" type="checkbox" 
                                                           data-id="${product.id}" 
                                                           data-name="${htmlEscape(product.name)}"
                                                           data-original="${product.price}"
                                                           id="product${product.id}">
                                                    <label class="form-check-label fw-bold" for="product${product.id}">
                                                        ${htmlEscape(product.name)}
                                                    </label>
                                                </div>
                                                <div class="text-muted small mb-3">Giá gốc: ${parseInt(product.price).toLocaleString()}đ</div>
                                            </div>
                                        </div>
                                        <div class="product-inputs d-none">
                                            <div class="row g-2">
                                                <div class="col-8">
                                                    <label class="form-label small fw-bold">Giá sale (đ)</label>
                                                    <input type="number" 
                                                           class="form-control sale-price" 
                                                           data-id="${product.id}"
                                                           placeholder="Giá sale" 
                                                           min="1000"
                                                           data-original="${product.price}">
                                                </div>
                                                <div class="col-4">
                                                    <label class="form-label small fw-bold">SL</label>
                                                    <input type="number" 
                                                           class="form-control sale-qty" 
                                                           data-id="${product.id}"
                                                           placeholder="SL" 
                                                           value="10"
                                                           min="1">
                                                </div>
                                            </div>
                                            <div class="discount-label discount-label-${product.id} d-none mt-2">
                                                <small class="badge bg-danger">
                                                    Giảm <span class="discount-percent-${product.id}">0</span>%
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        productList.innerHTML = html;

                        // Add event listeners to checkboxes
                        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                const inputs = this.closest('.card').querySelector('.product-inputs');
                                if (this.checked) {
                                    inputs.classList.remove('d-none');
                                } else {
                                    inputs.classList.add('d-none');
                                }
                                updateSelectedProducts();
                            });
                        });

                        // Add event listeners to price inputs
                        document.querySelectorAll('.sale-price').forEach(input => {
                            input.addEventListener('input', calculateDiscount);
                            input.addEventListener('input', updateSelectedProducts);
                        });

                        // Add event listeners to quantity inputs
                        document.querySelectorAll('.sale-qty').forEach(input => {
                            input.addEventListener('input', updateSelectedProducts);
                        });
                    } else {
                        productList.innerHTML = '<div class="alert alert-warning"><i class="fa-solid fa-exclamation-triangle me-2"></i> Danh mục này chưa có sản phẩm</div>';
                    }
                })
                .catch(err => {
                    console.error('Lỗi tải sản phẩm:', err);
                    productList.innerHTML = '<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i> Lỗi tải sản phẩm</div>';
                });
        });
    }

    function htmlEscape(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
