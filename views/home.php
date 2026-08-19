<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid p-0 mb-5">
    <div class="position-relative text-white" 
         style="background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80') center/cover no-repeat; height: 550px; display: flex; align-items: center; justify-content: center;">
        
        <div class="text-center px-3" style="max-width: 800px;">
            <h1 class="display-3 fw-bold mb-3" style="text-shadow: 2px 2px 10px rgba(0,0,0,0.7);">Khám Phá Thiên Nhiên</h1>
            <p class="lead mb-4 fs-4 text-light">Trang bị tốt nhất cho chuyến đi của bạn. Chất lượng đỉnh cao, bền bỉ mọi địa hình.</p>
            <a href="/product" class="btn btn-warning btn-lg fw-bold px-5 rounded-pill shadow-lg hover-scale">
                Mua Sắm Ngay <i class="fa-solid fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>

<div class="container">

    <?php if (!empty($flash_sale) && !empty($flash_sale_items)): ?>
    <div class="bg-white rounded shadow-sm border border-danger mb-5 overflow-hidden">
        <div class="p-3 d-flex flex-wrap justify-content-between align-items-center" style="background: linear-gradient(90deg, #d32f2f, #ef5350);">
            <div class="d-flex align-items-center gap-3">
                <h3 class="m-0 text-white fw-bold fst-italic"><i class="fa-solid fa-bolt text-warning"></i> FLASH SALE</h3>
                
                <div class="d-flex align-items-center text-white gap-2" id="flash-countdown">
                    <span class="small text-uppercase fw-bold opacity-75">Kết thúc trong:</span>
                    <div class="d-flex gap-1 fw-bold">
                        <span class="bg-dark rounded px-2 py-1" id="cd-days">00</span> Ngày
                        <span class="bg-dark rounded px-2 py-1" id="cd-hours">00</span> :
                        <span class="bg-dark rounded px-2 py-1" id="cd-minutes">00</span> :
                        <span class="bg-dark rounded px-2 py-1" id="cd-seconds">00</span>
                    </div>
                </div>
            </div>
            <input type="hidden" id="flash-end-time" value="<?= $flash_sale['end_time'] ?>">
        </div>

        <div class="p-4">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach($flash_sale_items as $item): ?>
                    <?php 
                        // Tính % giảm giá
                        $discountPercent = 0;
                        if ($item['original_price'] > 0) {
                            $discountPercent = round((($item['original_price'] - $item['sale_price']) / $item['original_price']) * 100);
                        }
                        // Tính % đã bán (cho thanh progress bar)
                        $soldPercent = ($item['quantity'] > 0) ? round(($item['sold'] / $item['quantity']) * 100) : 100;
                        
                        // Xử lý đường dẫn ảnh
                        $imgSrc = $item['image'];
                        if (!str_contains($imgSrc, 'http')) {
                            $imgSrc = '/uploads/products/' . $imgSrc;
                        }
                    ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm product-card">
                            <div class="position-relative">
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 shadow px-2 py-2">
                                    -<?= $discountPercent ?>%
                                </span>
                                <a href="/product/detail/<?= $item['product_id'] ?>">
                                    <img src="<?= $imgSrc ?>" class="card-img-top p-3" alt="<?= $item['name'] ?>" style="height: 200px; object-fit: contain;">
                                </a>
                            </div>
                            <div class="card-body pt-0">
                                <h6 class="card-title text-truncate">
                                    <a href="/product/detail/<?= $item['product_id'] ?>" class="text-dark text-decoration-none"><?= $item['name'] ?></a>
                                </h6>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="text-danger fw-bold fs-5"><?= number_format($item['sale_price']) ?>đ</span>
                                    <span class="text-muted text-decoration-line-through small"><?= number_format($item['original_price']) ?>đ</span>
                                </div>
                                
                                <div class="progress position-relative" style="height: 18px; border-radius: 10px; background-color: #ffcccc;">
                                    <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: <?= $soldPercent ?>%">
                                    </div>
                                    <span class="position-absolute w-100 text-center text-white small fw-bold" style="line-height: 18px; font-size: 0.75rem; text-shadow: 0 0 2px black;">
                                        Đã bán <?= $item['sold'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
        <h3 class="fw-bold border-start border-4 border-danger ps-3 text-uppercase">Sản phẩm mới về</h3>
        <a href="/product?sort=newest" class="text-decoration-none fw-bold">Xem tất cả <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
        <?php foreach($new_products as $product): ?>
             <div class="col">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <?php 
                        $imgSrc = $product['image'];
                        if (!str_contains($imgSrc, 'http')) {
                            $imgSrc = '/uploads/products/' . $imgSrc;
                        }
                    ?>
                    <div class="position-relative overflow-hidden bg-light" style="height: 250px;">
                        <span class="badge bg-danger position-absolute top-0 start-0 m-3 shadow px-3 py-2">Mới</span>
                        
                        <a href="/product/detail/<?= $product['id'] ?>">
                            <img src="<?= $imgSrc ?>" class="card-img-top w-100 h-100 object-fit-contain p-3" alt="<?= $product['name'] ?>" onerror="this.src='https://placehold.co/400x300?text=No+Image'">
                        </a>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?= $product['category_name'] ?? 'Sản phẩm' ?></small>
                        
                        <h6 class="card-title text-truncate my-2">
                            <a href="/product/detail/<?= $product['id'] ?>" class="text-dark text-decoration-none fw-bold"><?= $product['name'] ?></a>
                        </h6>

                        <div class="mb-2 small text-warning d-flex align-items-center">
                            <?php 
                                $rating = round($product['avg_rating'] ?? 0); 
                                $count = $product['review_count'] ?? 0;
                            ?>
                            <?php if ($count > 0): ?>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-<?= ($i <= $rating) ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                                <span class="text-muted ms-1" style="font-size: 0.8rem;">(<?= $count ?>)</span>
                            <?php else: ?>
                                <span class="text-muted text-opacity-50" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-star"></i> Chưa có đánh giá
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <?php 
                                    $displayPrice = $product['price'];
                                    $originalPrice = $product['price'];
                                    $hasFlashSale = false;
                                    
                                    // Check if product is in active flash sale
                                    if (!empty($flash_sale) && !empty($flash_sale_items)) {
                                        foreach ($flash_sale_items as $saleItem) {
                                            if ($saleItem['product_id'] == $product['id'] && $saleItem['quantity'] > $saleItem['sold']) {
                                                $displayPrice = $saleItem['sale_price'];
                                                $hasFlashSale = true;
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <?php if ($hasFlashSale): ?>
                                    <div>
                                        <del class="text-muted small"><?= number_format($originalPrice) ?> đ</del>
                                        <span class="fw-bold text-danger fs-5"><?= number_format($displayPrice) ?> đ</span>
                                        <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 6px;">SALE</span>
                                    </div>
                                <?php else: ?>
                                    <span class="fw-bold text-danger fs-5"><?= number_format($product['price']) ?> đ</span>
                                <?php endif; ?>
                            </div>

                            <form action="/cart/add" method="POST" class="d-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                
                                <button type="submit" name="action" value="buy_now" 
                                        class="btn btn-primary btn-sm flex-grow-1 fw-bold"
                                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    Mua ngay
                                </button>
                                
                                <button type="submit" name="action" value="add" 
                                        class="btn btn-outline-primary btn-sm"
                                        title="Thêm vào giỏ"
                                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-md-6">
            <div class="rounded p-4 text-white d-flex align-items-center position-relative overflow-hidden shadow-sm hover-scale" 
                 style="background: linear-gradient(45deg, #ff6b6b, #ee5253); min-height: 200px; cursor: pointer;"
                 onclick="window.location.href='/coupon'">
                <div class="position-relative z-1">
                    <span class="badge bg-white text-danger mb-2">Hot Deal</span>
                    <h4 class="fw-bold">Kho Mã Giảm Giá</h4>
                    <p class="mb-3">Săn voucher giảm tới 50%</p>
                    <a href="/coupon" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-danger">Lấy mã ngay</a>
                </div>
                <i class="fa-solid fa-ticket fa-6x position-absolute end-0 bottom-0 mb-n2 me-3 opacity-25"></i>
            </div>
        </div>
        <div class="col-md-6">
            <div class="rounded p-4 text-white d-flex align-items-center position-relative overflow-hidden shadow-sm hover-scale" 
                 style="background: linear-gradient(45deg, #48dbfb, #0abde3); min-height: 200px; cursor: pointer;"
                 onclick="window.location.href='/coupon'">
                <div class="position-relative z-1">
                    <span class="badge bg-white text-primary mb-2">Ưu đãi</span>
                    <h4 class="fw-bold">Miễn Phí Vận Chuyển</h4>
                    <p class="mb-3">Cho đơn hàng từ 150k</p>
                    <a href="/coupon" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-primary">Xem chi tiết</a>
                </div>
                <i class="fa-solid fa-truck-fast fa-6x position-absolute end-0 bottom-0 mb-n2 me-3 opacity-25"></i>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold border-start border-4 border-success ps-3 text-uppercase">Gợi ý hôm nay</h3>
        <a href="/product" class="text-decoration-none fw-bold">Xem cửa hàng <i class="fa-solid fa-angle-right"></i></a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-5">
        <?php foreach($featured_products as $product): ?>
             <div class="col">
                <div class="card h-100 shadow-sm border-0 product-card">
                    <?php 
                        $imgSrc = $product['image'];
                        if (!str_contains($imgSrc, 'http')) {
                            $imgSrc = '/uploads/products/' . $imgSrc;
                        }
                    ?>
                    <div class="position-relative overflow-hidden bg-light" style="height: 220px;">
                        <a href="/product/detail/<?= $product['id'] ?>">
                            <img src="<?= $imgSrc ?>" class="card-img-top w-100 h-100 object-fit-contain p-3" alt="<?= $product['name'] ?>" onerror="this.src='https://placehold.co/400x300?text=No+Image'">
                        </a>
                        <?php if($product['stock'] <= 0): ?>
                             <span class="position-absolute top-0 end-0 bg-secondary text-white px-2 py-1 m-2 rounded small">Hết hàng</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?= $product['category_name'] ?? 'Thiết bị' ?></small>
                        <h6 class="card-title text-truncate my-2">
                            <a href="/product/detail/<?= $product['id'] ?>" class="text-dark text-decoration-none fw-bold"><?= $product['name'] ?></a>
                        </h6>

                        <div class="mb-2 small text-warning d-flex align-items-center">
                            <?php 
                                $rating = round($product['avg_rating'] ?? 0); 
                                $count = $product['review_count'] ?? 0;
                            ?>
                            <?php if ($count > 0): ?>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="fa-<?= ($i <= $rating) ? 'solid' : 'regular' ?> fa-star"></i>
                                <?php endfor; ?>
                                <span class="text-muted ms-1" style="font-size: 0.8rem;">(<?= $count ?>)</span>
                            <?php else: ?>
                                <span class="text-muted text-opacity-50" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-star"></i> Chưa có đánh giá
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="mb-3">
                                <span class="fw-bold text-danger fs-5"><?= number_format($product['price']) ?> đ</span>
                            </div>
                            
                            <form action="/cart/add" method="POST" class="d-grid gap-2">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="action" value="buy_now" 
                                        class="btn btn-primary btn-sm fw-bold"
                                        <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <i class="fa-solid fa-bolt me-1"></i> Mua Ngay
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="row mt-5 py-5 border-top">
        <div class="col-md-4 text-center mb-3">
            <i class="fa-solid fa-truck-fast fa-3x text-primary mb-3"></i>
            <h5>Giao hàng siêu tốc</h5>
            <p class="text-muted">Nhận hàng trong 2-4 giờ tại nội thành</p>
        </div>
        <div class="col-md-4 text-center mb-3">
            <i class="fa-solid fa-shield-halved fa-3x text-success mb-3"></i>
            <h5>Bảo hành chính hãng</h5>
            <p class="text-muted">Cam kết 100% hàng chính hãng, bảo hành 12 tháng</p>
        </div>
        <div class="col-md-4 text-center mb-3">
            <i class="fa-solid fa-headset fa-3x text-warning mb-3"></i>
            <h5>Hỗ trợ 24/7</h5>
            <p class="text-muted">Đội ngũ kỹ thuật luôn sẵn sàng hỗ trợ bạn</p>
        </div>
    </div>
</div>

<style>
    .product-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important; }
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); }
    .object-fit-contain { object-fit: contain; }
</style>

<?php if (!empty($flash_sale)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Lấy thời gian kết thúc từ input hidden
        const endTimeStr = document.getElementById('flash-end-time').value; 
        const endTime = new Date(endTimeStr).getTime();

        const x = setInterval(function() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("flash-countdown").innerHTML = "ĐÃ KẾT THÚC";
                // Có thể reload lại trang để ẩn section
                // window.location.reload();
                return;
            }

            // Tính ngày, giờ, phút, giây
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Cập nhật lên giao diện, thêm số 0 đằng trước nếu < 10
            document.getElementById("cd-days").innerText = days < 10 ? "0" + days : days;
            document.getElementById("cd-hours").innerText = hours < 10 ? "0" + hours : hours;
            document.getElementById("cd-minutes").innerText = minutes < 10 ? "0" + minutes : minutes;
            document.getElementById("cd-seconds").innerText = seconds < 10 ? "0" + seconds : seconds;
        }, 1000);
    });
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>
