<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<style>
.product-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 10px;
    overflow: hidden;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.product-img {
    height: 200px;
    object-fit: cover;
    cursor: pointer;
}
.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
}
.product-price {
    font-size: 1.2rem;
    color: #e74c3c;
    font-weight: bold;
}
.btn-view-detail {
    background: linear-gradient(45deg, #3498db, #2980b9);
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    color: white;
    text-decoration: none;
    transition: background 0.3s;
}
.btn-view-detail:hover {
    background: linear-gradient(45deg, #2980b9, #21618c);
    color: white;
}
.sidebar-category {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
.sidebar-category:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}
.sidebar-category .card-header {
    background: linear-gradient(45deg, #3498db, #2980b9) !important;
    border-bottom: none;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.sidebar-category .list-group-item {
    border: none;
    border-radius: 0;
    margin-bottom: 0;
    padding: 12px 16px;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    font-size: 0.95rem;
    color: #555;
}
.sidebar-category .list-group-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
    border-left-color: #3498db;
    color: #333;
    font-weight: 500;
}
.sidebar-category .list-group-item.active {
    background: linear-gradient(45deg, #3498db, #2980b9) !important;
    color: white;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.1);
    border-left-color: #fff;
    font-weight: 600;
}
.sort-select {
    border-radius: 20px;
    border: 2px solid #e9ecef;
    padding: 8px 16px;
    font-weight: 500;
    transition: border-color 0.3s;
}
.sort-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}
</style>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar: Danh mục -->
        <div class="col-md-3">
            <div class="card sidebar-category">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh Mục Sản Phẩm</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/product" class="list-group-item list-group-item-action <?= !$current_category_id ? 'active' : '' ?>">
                        Tất cả sản phẩm
                    </a>
                    <?php foreach ($categories as $category): ?>
                        <a href="/product?category_id=<?= $category['id'] ?>" class="list-group-item list-group-item-action <?= $current_category_id == $category['id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main content: Sản phẩm -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><?= htmlspecialchars($page_title) ?></h4>
                <div class="d-flex">
                    <select class="form-select me-2 sort-select" style="width: auto;" onchange="changeSort(this.value)">
                        <option value="newest" <?= $current_sort == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="price_asc" <?= $current_sort == 'price_asc' ? 'selected' : '' ?>>Giá thấp đến cao</option>
                        <option value="price_desc" <?= $current_sort == 'price_desc' ? 'selected' : '' ?>>Giá cao đến thấp</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <?php if (empty($products)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">Không có sản phẩm nào.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 product-card">
                                <a href="/product/detail/<?= $product['id'] ?>">
                                    <img src="<?= htmlspecialchars(strpos($product['image'], '/') !== false ? $product['image'] : '/uploads/products/' . $product['image']) ?? '/assets/images/no-image.png' ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                                </a>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title product-title">
                                        <a href="/product/detail/<?= $product['id'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 100)) ?>...</p>
                                    
                                    <!-- Star Rating Display -->
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
                                        <?php if (!empty($product['flash_sale_price'])): ?>
                                            <p class="product-price mb-2" style="font-size: 0.95rem;">
                                                <del class="text-muted" style="font-size: 0.65rem; margin-right: 4px;"><?= number_format($product['flash_sale_original_price']) ?> VND</del>
                                                <span class="text-danger" style="display: inline-block; margin-right: 4px;"><?= number_format($product['flash_sale_price']) ?> VND</span>
                                                <span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 6px;">SALE</span>
                                            </p>
                                        <?php elseif (!empty($product['original_price'])): ?>
                                            <p class="product-price mb-2">
                                                <del class="text-muted" style="font-size: 0.9rem;"><?= number_format($product['original_price']) ?> VND</del>
                                                <span><?= number_format($product['display_price']) ?> VND</span>
                                            </p>
                                        <?php else: ?>
                                            <p class="product-price mb-2"><?= number_format($product['price']) ?> VND</p>
                                        <?php endif; ?>
                                        <div class="d-flex gap-2">
                                            <form action="/cart/add" method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-outline-success btn-sm" 
                                                        title="Thêm vào giỏ hàng" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                                    <i class="fa-solid fa-cart-plus"></i>
                                                </button>
                                            </form>
                                            <form action="/cart/add" method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="action" value="buy_now">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        title="Mua ngay" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                                    <i class="fa-solid fa-bolt"></i>
                                                </button>
                                            </form>
                                            <a href="/product/detail/<?= $product['id'] ?>" class="btn btn-view-detail btn-sm">Xem chi tiết</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function changeSort(sort) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sort);
    window.location = url;
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>