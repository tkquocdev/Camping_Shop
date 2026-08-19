<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Thông báo - Camping Shop Admin</title>
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
        <?php $active = 'notifications'; require_once ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

<div class="main-content" style="margin-left: 300px; padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-bullhorn text-primary me-2"></i>Gửi Thông báo Mới
                </h3>
                <p class="text-muted mb-0">Soạn thảo và gửi thông báo đến toàn bộ khách hàng</p>
            </div>
            <a href="/admin/notifications" class="btn btn-white border shadow-sm bg-white">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="/admin/notifications/create" method="POST">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-lg" placeholder="Ví dụ: Siêu sale 12.12..." required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Loại thông báo</label>
                                <div class="d-flex gap-3">
                                    <label class="card p-3 border w-50 bg-light" style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="radio" name="type" value="promotion" checked>
                                            <span class="fw-semibold">🎁 Khuyến mãi</span>
                                        </div>
                                    </label>
                                    <label class="card p-3 border w-50 bg-light" style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <input class="form-check-input mt-0 me-2" type="radio" name="type" value="system">
                                            <span class="fw-semibold">⚙️ Hệ thống</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Nội dung <span class="text-danger">*</span></label>
                                <textarea name="message" rows="10" class="form-control" placeholder="Nhập nội dung chi tiết..." required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Liên kết (tùy chọn)</label>
                                <input type="text" name="link" class="form-control" placeholder="Ví dụ: /product, /orders, ...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Người dùng (để trống = gửi cho tất cả)</label>
                                <input type="number" name="user_id" class="form-control" placeholder="ID người dùng">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-mobile-screen me-2"></i>Xem trước</h6>
                        </div>
                        <div class="card-body bg-light">
                            <div class="bg-white p-3 rounded border shadow-sm">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px;">
                                        <i class="fa-solid fa-bell fa-xs"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small" id="previewTitle">Tiêu đề sẽ hiện ở đây</div>
                                        <div class="text-muted extra-small" style="font-size: 11px;">Vừa xong</div>
                                    </div>
                                </div>
                                <p class="text-secondary small mb-0" id="previewContent">Nội dung sẽ hiển thị ở đây...</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold"><i class="fa-solid fa-paper-plane me-2"></i> Gửi Ngay</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const titleInput = document.querySelector('input[name="title"]');
    const contentInput = document.querySelector('textarea[name="message"]');
    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');

    titleInput.addEventListener('input', () => {
        previewTitle.textContent = titleInput.value.trim() !== '' ? titleInput.value : 'Tiêu đề sẽ hiện ở đây';
    });
    contentInput.addEventListener('input', () => {
        previewContent.textContent = contentInput.value.trim() !== '' ? contentInput.value : 'Nội dung sẽ hiển thị ở đây...';
    });
});
</script>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>