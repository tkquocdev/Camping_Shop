<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid p-0 mb-5">
    <div class="position-relative text-white" 
         style="background: linear-gradient(rgba(0,0,0,.5), rgba(0,0,0,.7)), url('https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat; height:350px; display:flex; align-items:center; justify-content:center;">
        <div class="text-center px-3" style="max-width:800px;">
            <h1 class="display-4 fw-bold mb-3">Tin Tức & Sự Kiện</h1>
            <p class="lead mb-0 fs-5 text-light">Cập nhật những xu hướng cắm trại mới nhất và mẹo hữu ích cho chuyến đi của bạn</p>
        </div>
    </div>
</div>

<div class="container mb-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Trang chủ</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">Tin tức</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center mb-4 border-bottom pb-2">
        <h3 class="fw-bold border-start border-4 border-warning ps-3 text-uppercase mb-0">Bài viết mới nhất</h3>
    </div>

    <?php if (empty($newsList)): ?>
        <div class="text-center py-5 bg-light rounded">
            <i class="fa-regular fa-newspaper fa-4x text-muted mb-3"></i>
            <p class="text-muted fs-5 mb-0">Chưa có tin tức nào được đăng tải</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($newsList as $item): ?>
                <?php
                    // --- XỬ LÝ ẢNH (PHƯƠNG PHÁP MOI) ---
                    $imageName = $item['image'] ?? '';
                    $imgSrc = '';

                    // Trường hợp 1: Database trống
                    if (empty($imageName)) {
                        $imgSrc = 'https://placehold.co/600x400?text=No+Image';
                    } 
                    // Trường hợp 2: Là link online (http...)
                    elseif (filter_var($imageName, FILTER_VALIDATE_URL)) {
                        $imgSrc = $imageName;
                    } 
                    // Trường hợp 3: Ảnh upload
                    else {
                        // Lấy mỗi tên file để tránh lỗi đường dẫn cũ (vd: uploads/news/anh.jpg -> anh.jpg)
                        $cleanName = basename($imageName);
                        // Gán cứng đường dẫn theo link bạn test chạy được
                        $imgSrc = '/uploads/news/' . $cleanName;
                    }
                ?>

                <div class="col">
                    <div class="card h-100 shadow-sm border-0 news-card">
                        <div class="position-relative overflow-hidden bg-light" style="height:220px;">
                            <a href="/news/detail?id=<?= $item['id'] ?>">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" 
                                     class="w-100 h-100 object-fit-cover" 
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=Image+Not+Found';">
                            </a>
                            <div class="position-absolute bottom-0 start-0 bg-white px-3 py-1 m-2 rounded shadow-sm text-warning fw-bold small">
                                <i class="fa-regular fa-calendar me-1"></i> <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold mb-2 text-clamp-2">
                                <a href="/news/detail?id=<?= $item['id'] ?>" class="text-dark text-decoration-none title-hover">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1 text-clamp-3">
                                <?= strip_tags($item['summary']) ?>
                            </p>
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <a href="/news/detail?id=<?= $item['id'] ?>" class="text-warning text-decoration-none fw-bold small">
                                    Đọc tiếp <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                                <span class="text-muted small"><i class="fa-regular fa-eye me-1"></i> Chi tiết</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .news-card { transition: all .3s ease; }
    .news-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,.1); }
    .object-fit-cover { object-fit: cover; }
    .title-hover:hover { color: #ffc107; }
    .text-clamp-2, .text-clamp-3 { display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; }
    .text-clamp-2 { -webkit-line-clamp: 2; }
    .text-clamp-3 { -webkit-line-clamp: 3; }
</style>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>