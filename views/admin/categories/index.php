<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - Camping Shop</title>
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
        <?php $active = 'categories'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Danh mục</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCategoryModal()">
                <i class="fa-solid fa-plus me-2"></i>
                Thêm danh mục
            </button>
        </div>

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên danh mục</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-success">Hoạt động</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-2"
                                            onclick="loadCategoryData(<?= $category['id'] ?>)"
                                            type="button">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <form action="/admin/categories/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa danh mục này?');">
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Create / Edit Category -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="categoryForm" method="POST" action="/admin/categories/store">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">Thêm danh mục</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="category_id">
                        <div class="mb-3">
                            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="category_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" id="category_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" id="categorySubmitBtn">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        const categoryForm = document.getElementById('categoryForm');

        // Fetch category data and open modal for editing
        function loadCategoryData(id) {
            fetch(`/admin/categories/edit/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('category_id').value = data.id;
                    document.getElementById('category_name').value = data.name;
                    document.getElementById('category_description').value = data.description || '';
                    document.getElementById('categoryModalTitle').textContent = 'Chỉnh sửa danh mục';
                    document.getElementById('categorySubmitBtn').textContent = 'Cập nhật';
                    categoryForm.action = `/admin/categories/update/${data.id}`;
                    categoryModal.show();
                })
                .catch(err => {
                    console.error('Error loading category:', err);
                    alert('Lỗi khi tải dữ liệu danh mục');
                });
        }

        // Open modal for creating new category
        function openCategoryModal() {
            document.getElementById('category_id').value = '';
            document.getElementById('category_name').value = '';
            document.getElementById('category_description').value = '';
            document.getElementById('categoryModalTitle').textContent = 'Thêm danh mục';
            document.getElementById('categorySubmitBtn').textContent = 'Tạo mới';
            categoryForm.action = '/admin/categories/store';
            categoryModal.show();
        }
    </script>
</body>
</html>