<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tin tức - Camping Shop</title>
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
        <?php $active = 'news'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fa-solid fa-newspaper me-2"></i> Quản lý Tin tức
                </h3>
                <p class="text-muted mb-0">Danh sách các bài viết, tin tức và sự kiện.</p>
            </div>
            <a href="/admin/news/create" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i> Thêm bài viết mới
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
                                <th class="px-4" style="width:50px;">ID</th>
                                <th style="width:120px;">Hình ảnh</th>
                                <th>Tiêu đề & Nội dung</th>
                                <th style="width:150px;">Ngày đăng</th>
                                <th class="text-center" style="width:120px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($newsList) && is_array($newsList)): ?>
                            <?php foreach ($newsList as $news): ?>
                                <tr>
                                    <td class="px-4"><span class="text-muted fw-bold">#<?= $news['id'] ?></span></td>
                                    
                                    <td>
                                        <?php 
                                            // --- LOGIC XỬ LÝ ẢNH (Đồng bộ với Client) ---
                                            $imageName = $news['image'] ?? '';
                                            $imgSrc = '';

                                            if (empty($imageName)) {
                                                $imgSrc = 'https://placehold.co/100x70?text=No+Img';
                                            } 
                                            elseif (filter_var($imageName, FILTER_VALIDATE_URL)) {
                                                $imgSrc = $imageName;
                                            } 
                                            else {
                                                // Chỉ lấy tên file và trỏ về /uploads/news/ (bỏ /public đi)
                                                $cleanName = basename($imageName);
                                                $imgSrc = '/uploads/news/' . $cleanName;
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                             class="rounded shadow-sm object-fit-cover" 
                                             width="80" height="60" 
                                             alt="Thumb"
                                             onerror="this.onerror=null; this.src='https://placehold.co/100x70?text=Err';">
                                    </td>

                                    <td class="py-3">
                                        <div class="fw-bold text-dark mb-1">
                                            <a href="/news/detail?id=<?= $news['id'] ?>" target="_blank" class="text-decoration-none text-dark">
                                                <?= htmlspecialchars($news['title']) ?> <i class="fa-solid fa-arrow-up-right-from-square fa-xs text-muted ms-1"></i>
                                            </a>
                                        </div>
                                        <div class="text-muted small" style="max-width: 600px; word-wrap: break-word; white-space: normal;">
                                            <?= htmlspecialchars($news['summary']) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($news['created_at'])) ?></span>
                                            <span class="text-muted small"><?= date('H:i', strtotime($news['created_at'])) ?></span>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="/admin/news/edit?id=<?= $news['id'] ?>" class="btn btn-sm btn-outline-primary border-0" title="Sửa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            
                                            <form action="/admin/news/delete" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này? Hành động không thể hoàn tác.');">
                                                <input type="hidden" name="id" value="<?= $news['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Xóa">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open fa-3x text-secondary opacity-25 mb-3"></i>
                                    <h6 class="fw-bold">Chưa có bài viết nào</h6>
                                    <a href="/admin/news/create" class="btn btn-sm btn-primary mt-2">Thêm ngay</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if(isset($totalPages) && $totalPages > 1): ?>
            <div class="card-footer bg-white py-3">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        </ul>
                </nav>
            </div>
            <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
