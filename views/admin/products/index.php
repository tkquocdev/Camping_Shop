<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Camping Shop</title>
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
                height: auto;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'products'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Quản lý Sản phẩm</h4>
            <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fa-solid fa-plus me-2"></i> Thêm mới
            </button>
        </div>

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Kho</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $p['id'] ?></td>
                                <td>
                                    <img src="/uploads/products/<?= $p['image'] ?>" 
                                         class="rounded border" width="50" height="50" style="object-fit: cover;"
                                         onerror="this.src='https://placehold.co/50'">
                                </td>
                                <td style="max-width: 200px;">
                                    <div class="fw-bold text-truncate" title="<?= $p['name'] ?>"><?= $p['name'] ?></div>
                                </td>
                                <td><span class="badge bg-secondary"><?= $p['category_name'] ?? 'N/A' ?></span></td>
                                <td class="text-danger fw-bold"><?= number_format($p['price']) ?> đ</td>
                                <td>
                                    <?php if($p['stock'] > 0): ?>
                                        <span class="text-success fw-bold"><?= $p['stock'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-warning me-2"
                                            data-id="<?= $p['id'] ?>"
                                            data-name="<?= htmlspecialchars($p['name']) ?>"
                                            data-category="<?= $p['category_id'] ?>"
                                            data-price="<?= $p['price'] ?>"
                                            data-stock="<?= $p['stock'] ?>"
                                            data-description="<?= htmlspecialchars($p['description']) ?>"
                                            data-image="<?= $p['image'] ?>"
                                            onclick="openEditModal(this)">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <a href="/admin/products/delete/<?= $p['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form action="/admin/products/store" method="POST" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Thêm sản phẩm mới</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        <?php foreach($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số lượng kho <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control" required min="0" value="10">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Hình ảnh</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả chi tiết</label>
                                    <textarea name="description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form action="/admin/products/update" method="POST" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header bg-warning-subtle">
                            <h5 class="modal-title fw-bold">Cập nhật sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_id">
                            <input type="hidden" name="current_image" id="edit_current_image"> <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select name="category_id" id="edit_category" class="form-select" required>
                                        <?php foreach($categories as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giá bán</label>
                                    <input type="number" name="price" id="edit_price" class="form-control" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số lượng kho</label>
                                    <input type="number" name="stock" id="edit_stock" class="form-control" required min="0">
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label class="form-label">Hình ảnh (Chỉ chọn nếu muốn thay đổi)</label>
                                    <div class="d-flex align-items-center">
                                        <img src="" id="preview_image" class="rounded border me-3" width="60" height="60" style="object-fit: cover;">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả chi tiết</label>
                                    <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-warning">Lưu thay đổi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(btn) {
            // Lấy dữ liệu từ data-attributes
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_category').value = btn.getAttribute('data-category');
            document.getElementById('edit_price').value = btn.getAttribute('data-price');
            document.getElementById('edit_stock').value = btn.getAttribute('data-stock');
            document.getElementById('edit_description').value = btn.getAttribute('data-description');
            
            // Xử lý ảnh
            var imageName = btn.getAttribute('data-image');
            document.getElementById('edit_current_image').value = imageName;
            document.getElementById('preview_image').src = '/uploads/products/' + imageName;

            // Mở Modal
            var myModal = new bootstrap.Modal(document.getElementById('editModal'));
            myModal.show();
        }
    </script>
</body>
</html>
