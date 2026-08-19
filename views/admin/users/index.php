<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - Camping Shop</title>
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
        <?php $active = 'users'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản lý Người dùng</h2>
            <button class="btn btn-primary">
                <i class="fa-solid fa-plus me-2"></i>
                Thêm người dùng
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Avatar</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td>
                                    <img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://placehold.co/40x40?text=User' ?>"
                                         class="rounded-circle" width="40" height="40" alt="Avatar" onerror="this.src='https://placehold.co/40x40?text=User'">
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'secondary' ?>">
                                        <?= $user['role'] === 'admin' ? 'Admin' : 'User' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $status = $user['status'] ?? 'active';
                                        $statusBadge = $status === 'banned' ? 'bg-danger' : 'bg-success';
                                        $statusText = $status === 'banned' ? 'Khóa' : 'Hoạt động';
                                    ?>
                                    <span class="badge <?= $statusBadge ?>">
                                        <?= $statusText ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </td>
                                <td>
                                    <a href="/admin/users/detail/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Chỉnh sửa">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <?php if (($user['status'] ?? 'active') !== 'banned'): ?>
                                        <a href="/admin/users/ban/<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger me-1" title="Khóa" onclick="return confirm('Bạn chắc chắn muốn khóa người dùng này?')">
                                            <i class="fa-solid fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="/admin/users/unban/<?= $user['id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Mở khóa">
                                            <i class="fa-solid fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="/admin/users/delete/<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn chắc chắn muốn xóa người dùng này?')">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>