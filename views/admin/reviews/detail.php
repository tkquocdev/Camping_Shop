<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Đánh giá - Camping Shop</title>
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
        <?php $active = 'reviews'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    
    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Chi tiết Đánh giá #<?= $review['id'] ?></h4>
                <span class="text-muted small">Xem thông tin chi tiết phản hồi của khách hàng</span>
            </div>
            <a href="/admin/reviews" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-box me-2"></i>Sản phẩm</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3 position-relative d-inline-block">
                            <img src="/uploads/products/<?= htmlspecialchars($review['product_image'] ?? '') ?>" 
                                 class="img-fluid rounded border" 
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 onerror="this.src='https://placehold.co/150?text=No+Image'">
                        </div>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($review['product_name'] ?? 'N/A') ?></h6>
                        <p class="text-muted small">ID Sản phẩm: #<?= $review['product_id'] ?></p>
                        <a href="/product/detail/<?= $review['product_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fa-solid fa-external-link-alt me-1"></i>Xem sản phẩm trên web
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-success"><i class="fa-solid fa-user me-2"></i>Người đánh giá</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-user text-secondary fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($review['reviewer_name'] ?? 'Ẩn danh') ?></div>
                                <div class="text-muted small">Vào lúc: <?= date('H:i - d/m/Y', strtotime($review['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-regular fa-comment-dots me-2"></i>Nội dung phản hồi</h6>
                        
                        <span class="badge bg-warning text-dark fs-6">
                            <?= $review['rating'] ?> <i class="fa-solid fa-star small"></i>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div class="mb-4 text-center py-3 bg-light rounded">
                            <label class="text-muted small d-block mb-2">Mức độ hài lòng</label>
                            <div class="fs-2 text-warning">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-<?= ($i <= $review['rating']) ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="small fw-bold mt-1 text-secondary">
                                <?php 
                                    $status = [
                                        1 => 'Rất tệ', 
                                        2 => 'Tệ', 
                                        3 => 'Bình thường', 
                                        4 => 'Hài lòng', 
                                        5 => 'Tuyệt vời'
                                    ];
                                    echo $status[$review['rating']] ?? 'Đánh giá';
                                ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold mb-2 text-secondary">Lời bình:</label>
                            <div class="p-4 rounded border bg-white position-relative">
                                <i class="fa-solid fa-quote-left text-secondary opacity-25 position-absolute top-0 start-0 m-3 fs-3"></i>
                                <p class="mb-0 ps-4 text-dark" style="font-size: 1.1rem; line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($review['comment'] ?? 'Không có bình luận')) ?>
                                </p>
                            </div>
                        </div>

                        <div class="border-top pt-4 d-flex justify-content-end gap-2">
                            <form action="/admin/reviews/delete/<?= $review['id'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Hành động này không thể hoàn tác. Bạn chắc chắn muốn xóa?')">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fa-solid fa-trash-can me-2"></i>Xóa đánh giá này
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>