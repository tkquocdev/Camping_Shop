<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử Thông báo - Camping Shop</title>
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
            min-height: 100vh;
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
        <?php $active = 'notifications'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-bell me-2"></i> Lịch sử Thông báo
                </h3>
                <p class="text-muted mb-0">Quản lý danh sách các thông báo đã gửi hệ thống.</p>
            </div>
            <a href="/admin/notifications/create" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-paper-plane me-2"></i> Gửi thông báo mới
            </a>
        </div>

        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= $_SESSION['flash_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold text-secondary">
                            <tr>
                                <th class="px-4" style="width:80px;">ID</th>
                                <th style="width:300px;">Nội dung thông báo</th>
                                <th style="width:140px;">Loại tin</th>
                                <th style="width:160px;">Ngày gửi</th>
                                <th class="text-center" style="width:100px;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($notifications) && is_array($notifications)): ?>
                            <?php foreach ($notifications as $n): ?>
                                <tr>
                                    <td class="px-4"><span class="text-muted">#<?= $n['id'] ?></span></td>
                                    <td class="py-3">
                                        <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($n['title']) ?></div>
                                        <div class="text-muted small" style="max-width: 280px; word-wrap: break-word; white-space: normal;">
                                            <?= htmlspecialchars($n['message'] ?? '') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (($n['type'] ?? '') === 'promotion'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                <i class="fa-solid fa-gift me-1"></i>Sale
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">
                                                <i class="fa-solid fa-gear me-1"></i>System
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
                                            <span class="text-muted small"><?= date('H:i', strtotime($n['created_at'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <form action="/admin/notifications/delete/<?= $n['id'] ?>" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');" style="display: inline;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-bell-slash fa-3x text-secondary opacity-25 mb-3"></i>
                                    <h6 class="fw-bold">Chưa có thông báo nào</h6>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>