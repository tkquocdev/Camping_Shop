<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Tin Tức Mới - Camping Shop</title>

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
    <?php $active = 'news'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
</div>

<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-primary mb-1">
                <i class="fa-solid fa-pen-nib me-2"></i>Đăng Tin Tức Mới
            </h3>
            <p class="text-muted mb-0">Soạn thảo bài viết blog hoặc tin tức khuyến mãi</p>
        </div>
        <a href="/admin/news" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $_SESSION['flash_error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="/admin/news/store" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Tiêu đề bài viết <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control form-control-lg" placeholder="Nhập tiêu đề hấp dẫn..." required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Ảnh đại diện <span class="text-danger">*</span></label>
                                <input class="form-control" type="file" name="image" id="imageInput" accept="image/*" required>
                                <div class="form-text">Định dạng hỗ trợ: JPG, PNG, WEBP. Dung lượng tối đa 2MB.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold text-uppercase small text-muted">Mô tả ngắn (Summary) <span class="text-danger">*</span></label>
                                <textarea name="summary" rows="4" class="form-control" placeholder="Đoạn văn ngắn giới thiệu nội dung bài viết..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-uppercase small text-muted">Nội dung chi tiết <span class="text-danger">*</span></label>
                                <textarea name="content" rows="12" class="form-control" placeholder="Viết nội dung đầy đủ tại đây..." required></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-3 sticky-top" style="top: 20px; z-index: 0;">
                        <div class="card-header bg-white py-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-eye me-2"></i>Xem trước thẻ tin tức</h6>
                        </div>
                        <div class="card-body bg-light">
                            <div class="card border-0 shadow-sm mx-auto" style="max-width: 350px;">
                                <div class="bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center overflow-hidden" style="height: 200px;">
                                    <img id="previewImage" src="https://placehold.co/600x400?text=Preview+Image" class="w-100 h-100 object-fit-cover" alt="Preview">
                                </div>
                                
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 text-muted small">
                                        <i class="fa-regular fa-calendar me-1"></i> <span><?= date('d/m/Y') ?></span>
                                    </div>
                                    <h5 class="card-title fw-bold text-dark text-clamp-2" id="previewTitle" style="font-size: 1.1rem;">
                                        Tiêu đề sẽ hiện ở đây
                                    </h5>
                                    <p class="card-text text-muted small text-clamp-3" id="previewSummary">
                                        Mô tả ngắn của bài viết sẽ hiển thị ở đây để thu hút người đọc...
                                    </p>
                                    <a href="#" class="btn btn-sm btn-outline-warning disabled text-dark fw-bold border-0 ps-0">
                                        Đọc tiếp <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top text-center">
                                <p class="small text-muted fst-italic">Đây là cách bài viết hiển thị trên danh sách tin tức.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Đăng bài viết
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .text-clamp-2 {
        display: -webkit-box;

        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .text-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Logic Javascript giữ nguyên
    const titleInput = document.querySelector('input[name="title"]');
    const summaryInput = document.querySelector('textarea[name="summary"]');
    const imageInput = document.getElementById('imageInput');
    
    const previewTitle = document.getElementById('previewTitle');
    const previewSummary = document.getElementById('previewSummary');
    const previewImage = document.getElementById('previewImage');

    titleInput.addEventListener('input', () => {
        previewTitle.textContent = titleInput.value.trim() !== '' ? titleInput.value : 'Tiêu đề sẽ hiện ở đây';
    });
    
    summaryInput.addEventListener('input', () => {
        previewSummary.textContent = summaryInput.value.trim() !== '' ? summaryInput.value : 'Mô tả ngắn của bài viết sẽ hiển thị ở đây để thu hút người đọc...';
    });

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
            previewImage.src = "https://placehold.co/600x400?text=Preview+Image";
        }
    });
});
</script>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>