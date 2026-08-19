<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Bài Viết - Camping Shop Admin</title>
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
        <?php $active = 'news'; require_once ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

<div class="main-content" style="margin-left: 300px; padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/news">Tin tức</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa bài viết</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Chỉnh sửa bài viết</h3>
            <a href="/admin/news" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form action="/admin/news/update" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id" value="<?= $news['id'] ?>">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3">Nội dung bài viết</h5>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?= htmlspecialchars($news['title']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="summary" class="form-label fw-bold">Tóm tắt ngắn</label>
                                <textarea class="form-control" id="summary" name="summary" rows="4"><?= htmlspecialchars($news['summary']) ?></textarea>
                                <div class="form-text">Mô tả ngắn gọn nội dung để hiển thị bên ngoài danh sách.</div>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label fw-bold">Nội dung chi tiết</label>
                                <textarea class="form-control" id="content" name="content" rows="12"><?= $news['content'] ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3">Hình ảnh đại diện</h5>

                            <?php
                                $imageName = $news['image'] ?? '';
                                $currentImgSrc = '';

                                if (empty($imageName)) {
                                    $currentImgSrc = 'https://placehold.co/600x400?text=No+Image';
                                } elseif (filter_var($imageName, FILTER_VALIDATE_URL)) {
                                    $currentImgSrc = $imageName;
                                } else {
                                    // Logic chuẩn: lấy tên file và trỏ về uploads/news/
                                    $currentImgSrc = '/uploads/news/' . basename($imageName);
                                }
                            ?>

                            <div class="mb-3 text-center bg-light p-3 rounded border">
                                <img src="<?= $currentImgSrc ?>" 
                                     id="imgPreview" 
                                     class="img-fluid rounded shadow-sm" 
                                     style="max-height: 200px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Image+Error';">
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-bold">Thay đổi ảnh (nếu muốn)</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                <div class="form-text small">Chấp nhận: jpg, jpeg, png, webp.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-3">Hành động</h5>
                            <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-bold">
                                <i class="fa-solid fa-save me-1"></i> Cập nhật thay đổi
                            </button>
                            <a href="/admin/news" class="btn btn-outline-secondary w-100">Hủy bỏ</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('imgPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>