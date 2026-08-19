<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<?php
    // --- XỬ LÝ ẢNH CHI TIẾT ---
    $imageName = $news['image'] ?? '';
    $imgSrc = '';

    if (empty($imageName)) {
        $imgSrc = 'https://placehold.co/1200x600?text=No+Image';
    } 
    elseif (filter_var($imageName, FILTER_VALIDATE_URL)) {
        $imgSrc = $imageName;
    } 
    else {
        // Chỉ lấy tên file, bỏ các phần đường dẫn thừa nếu có
        $cleanName = basename($imageName);
        // Trỏ đúng vào đường dẫn bạn đã test thành công
        $imgSrc = '/uploads/news/' . $cleanName;
    }
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/news" class="text-decoration-none text-muted">Tin tức</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">Chi tiết</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-4">
                <span class="badge bg-warning text-dark rounded-pill px-3 mb-2">Tin tức & Sự kiện</span>
                <h1 class="fw-bold display-5 mb-3"><?= htmlspecialchars($news['title']) ?></h1>
                
                <div class="d-flex justify-content-center gap-4 text-muted small">
                    <div><i class="fa-regular fa-user me-1"></i> Admin</div>
                    <div><i class="fa-regular fa-calendar me-1"></i> <?= date('d/m/Y', strtotime($news['created_at'])) ?></div>
                    <div><i class="fa-regular fa-clock me-1"></i> <?= date('H:i', strtotime($news['created_at'])) ?></div>
                </div>
            </div>

            <div class="mb-5">
                <img src="<?= htmlspecialchars($imgSrc) ?>" 
                     class="img-fluid rounded shadow w-100" 
                     style="max-height:500px; object-fit:cover;" 
                     alt="<?= htmlspecialchars($news['title']) ?>"
                     onerror="this.onerror=null; this.src='https://placehold.co/1200x600?text=Image+Not+Found';">
            </div>

            <?php if (!empty($news['summary'])): ?>
                <div class="p-4 bg-light border-start border-4 border-warning rounded-end fst-italic text-muted fs-5 mb-4">
                    <?= nl2br(htmlspecialchars($news['summary'])) ?>
                </div>
            <?php endif; ?>

            <div class="content-body fs-5 text-dark" style="line-height:1.8; text-align:justify;">
                <?= html_entity_decode($news['content']) ?>
            </div>

            <div class="mt-5 py-4 border-top d-flex justify-content-between align-items-center">
                <a href="/news" class="btn btn-outline-secondary rounded-pill hover-scale">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách
                </a>
                <div class="d-flex gap-2">
                    <button class="btn btn-light rounded-circle shadow-sm text-primary hover-scale"><i class="fa-brands fa-facebook-f"></i></button>
                    <button class="btn btn-light rounded-circle shadow-sm text-info hover-scale"><i class="fa-brands fa-twitter"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="rounded p-5 text-white text-center position-relative overflow-hidden shadow mt-4" style="background: linear-gradient(45deg, #212529, #343a40);">
        <div class="position-relative z-1">
            <h2 class="fw-bold mb-3">Bạn đã sẵn sàng cho chuyến đi tiếp theo?</h2>
            <p class="mb-4 fs-5 text-light opacity-75">Ghé thăm cửa hàng để trang bị những vật dụng cắm trại tốt nhất ngay hôm nay</p>
            <a href="/products" class="btn btn-warning btn-lg rounded-pill fw-bold shadow-sm px-5 hover-scale">
                Mua sắm ngay <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
        <i class="fa-solid fa-campground fa-10x position-absolute bottom-0 end-0 mb-n4 me-n4 opacity-10"></i>
    </div>
</div>

<style>
    .hover-scale { transition: transform .2s; }
    .hover-scale:hover { transform: scale(1.05); }
    .content-body img { max-width: 100% !important; height: auto !important; border-radius: 8px; margin: 15px 0; box-shadow: 0 4px 6px rgba(0,0,0,.1); }
    .content-body ul, .content-body ol { margin-left: 1rem; margin-bottom: 1rem; }
    .content-body li { margin-bottom: .5rem; }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>