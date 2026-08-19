<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Người dùng - Camping Shop</title>
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
            <h2>Chi tiết Người dùng</h2>
            <div>
                <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-primary me-2">
                    <i class="fa-solid fa-edit me-2"></i>Chỉnh sửa
                </a>
                <a href="/admin/users" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Thông tin cá nhân</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 text-center">
                                <img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://placehold.co/150x150?text=User' ?>"
                                     class="rounded-circle" width="150" height="150"
                                     onerror="this.src='https://placehold.co/150x150?text=User'" style="object-fit: cover;">
                            </div>
                            <div class="col-md-9">
                                <p><strong>Họ tên:</strong> <?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
                                <p><strong>Vai trò:</strong>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'secondary' ?>">
                                        <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng' ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Thông tin tài khoản</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>ID:</strong> #<?= $user['id'] ?></p>
                        <p><strong>Username:</strong> <?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
                        <p><strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                        <p><strong>Lần cập nhật:</strong> <?= !empty($user['updated_at']) ? date('d/m/Y H:i', strtotime($user['updated_at'])) : 'Chưa cập nhật' ?></p>
                        <p><strong>Hình đại diện:</strong><br>
                            <img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://placehold.co/120x120?text=No+Avatar' ?>"
                                 class="rounded mt-2" width="120" height="120"
                                 onerror="this.src='https://placehold.co/120x120?text=No+Avatar'" style="object-fit: cover;">
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
