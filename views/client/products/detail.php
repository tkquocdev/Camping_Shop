<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $product['name'] ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6 mb-4">
            <img src="<?= htmlspecialchars(strpos($product['image'], '/') !== false ? $product['image'] : '/uploads/products/' . $product['image']) ?? 'https://placehold.co/600x400?text=No+Image' ?>" 
                 class="img-fluid rounded shadow w-100" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 onerror="this.src='https://placehold.co/600x400?text=No+Image'">
        </div>

        <div class="col-md-6">
            <h1 class="fw-bold mb-3"><?= $product['name'] ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-secondary me-2">Danh mục: <?= $product['category_name'] ?? 'Khác' ?></span>
                
                <?php if($product['stock'] > 0): ?>
                    <span class="badge bg-success">Còn hàng (<?= $product['stock'] ?>)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Hết hàng</span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($sale_info)): ?>
                <div class="p-3 mb-3 rounded" style="background: linear-gradient(to right, #fff0f0, #ffecec); border: 1px solid #ffcccc;">
                    
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fa-solid fa-bolt me-1"></i> FLASH SALE
                        </span>
                        <span class="text-danger fw-bold small">
                            Kết thúc trong: <span id="countdown-timer" class="badge bg-dark text-white">Loading...</span>
                        </span>
                    </div>

                    <div class="d-flex align-items-end gap-2 mb-2">
                        <h2 class="text-danger fw-bold mb-0">
                            <?= number_format($sale_info['sale_price']) ?> VNĐ
                        </h2>
                        <del class="text-muted fs-5">
                            <?= number_format($product['price']) ?> VNĐ
                        </del>
                        <span class="badge bg-warning text-dark">
                            -<?= round((($product['price'] - $sale_info['sale_price']) / $product['price']) * 100) ?>%
                        </span>
                    </div>

                    <div class="mt-2">
                        <?php 
                            // Tính phần trăm đã bán
                            $percent = 0;
                            if($sale_info['quantity'] > 0) {
                                $percent = ($sale_info['sold'] / $sale_info['quantity']) * 100;
                            }
                        ?>
                        <div class="progress" style="height: 12px; border-radius: 10px;">
                            <div class="progress-bar bg-danger progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $percent ?>%">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-danger fw-bold">🔥 Đã bán: <?= $sale_info['sold'] ?></small>
                            <small class="text-muted">Tổng: <?= $sale_info['quantity'] ?></small>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <h2 class="text-danger fw-bold mb-4"><?= number_format($product['price']) ?> VNĐ</h2>
            <?php endif; ?>
            <p class="fw-bold mt-3">Mô tả sản phẩm:</p>
            <p class="text-secondary"><?= nl2br($product['description']) ?></p>
            
            <hr class="my-4">

            <form id="cartForm" method="POST" action="/cart/add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <div class="d-flex align-items-center mb-4 gap-3">
                    <div class="d-flex align-items-center">
                        <label class="me-2 fw-bold">SL:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control text-center" 
                               style="width: 70px;" value="1" min="1" max="<?= $product['stock'] ?>">
                    </div>
                    
                    <button type="button" id="addToCartBtn" 
                            class="btn btn-outline-primary btn-lg" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-cart-plus me-1"></i> Thêm giỏ hàng
                    </button>

                    <button type="submit" name="action" value="buy_now" 
                            class="btn btn-danger btn-lg flex-grow-1" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-bolt me-1"></i> Mua Ngay
                    </button>
                </div>
            </form>
            
            <div class="bg-light p-3 rounded">
                <small class="d-block mb-1"><i class="fa-solid fa-truck text-primary"></i> Giao hàng toàn quốc</small>
                <small class="d-block mb-1"><i class="fa-solid fa-rotate-left text-primary"></i> Đổi trả trong 7 ngày</small>
                <small class="d-block"><i class="fa-solid fa-shield-halved text-primary"></i> Bảo hành chính hãng</small>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-4">
                <div style="flex: 1;">
                    <h3 class="mb-0 me-3 d-inline-block">Đánh giá từ khách hàng</h3>
                    <?php if(!empty($reviews)): ?>
                        <span class="badge bg-warning text-dark fs-5 d-inline-block">
                            <?= $avg_rating ?> <i class="fa-solid fa-star text-white"></i>
                        </span>
                        <span class="text-muted ms-2">(<?= count($reviews) ?> đánh giá)</span>
                    <?php endif; ?>
                </div>
                
                <?php if(isset($_SESSION['user'])): ?>
                    <?php 
                        $reviewModel = new \App\Models\Review(new \App\Config\Database());
                        $canReview = $reviewModel->hasPurchasedProduct($_SESSION['user']['id'], $product['id']);
                        $hasReviewed = $reviewModel->userHasReviewed($_SESSION['user']['id'], $product['id']);
                    ?>
                    <?php if($canReview && !$hasReviewed): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Viết đánh giá
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if(empty($reviews)): ?>
                <div class="text-center py-5 bg-light rounded-3">
                    <i class="fa-regular fa-comment-dots fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Chưa có đánh giá nào cho sản phẩm này.</p>
                    <p class="small text-secondary">Hãy mua hàng để là người đầu tiên đánh giá!</p>
                </div>
            <?php else: ?>
                <div class="review-list">
                    <?php foreach($reviews as $review): ?>
                        <div class="review-card card border-0 mb-3 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-body p-4">
                                <div class="d-flex gap-3 mb-3">
                                    <div class="flex-shrink-0">
                                        <?php 
                                            // Avatar logic từ profile/index.php
                                            $reviewerAvatar = $review['reviewer_avatar'] ?? $review['user_avatar'] ?? null;
                                            $reviewerName = $review['reviewer_name'] ?? $review['user_name'] ?? 'Khách hàng';
                                            $defaultAvatar = "https://ui-avatars.com/api/?name=" . urlencode($reviewerName) . "&background=0D8ABC&color=fff&size=48";
                                            $avatarPath = $defaultAvatar;

                                            if (!empty($reviewerAvatar)) {
                                                if (filter_var($reviewerAvatar, FILTER_VALIDATE_URL)) {
                                                    // If it's already a URL (e.g., Gmail avatar)
                                                    $avatarPath = $reviewerAvatar;
                                                } else {
                                                    // Try /uploads/{avatar} first
                                                    $relative = '/uploads/' . ltrim($reviewerAvatar, '/');
                                                    $fullPath = ROOT_PATH . '/public' . $relative;

                                                    if (!file_exists($fullPath)) {
                                                        // Try /uploads/users/{basename}
                                                        $relative = '/uploads/users/' . basename($reviewerAvatar);
                                                        $fullPath = ROOT_PATH . '/public' . $relative;
                                                    }

                                                    if (file_exists($fullPath)) {
                                                        $avatarPath = $relative . '?v=' . time();
                                                    }
                                                }
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($avatarPath) ?>" 
                                             alt="Avatar" 
                                             class="rounded-circle"
                                             style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #f0f0f0;">
                                    </div>
                                    
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold mb-0" style="color: #333;"><?= htmlspecialchars($review['reviewer_name'] ?? $review['user_name']) ?></h6>
                                                <small class="text-muted" style="font-size: 12px;">
                                                    <i class="fa-solid fa-calendar-days me-1"></i><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Edit/Delete buttons for own reviews -->
                                            <?php if(isset($_SESSION['user']) && $_SESSION['user']['id'] == $review['user_id']): ?>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editReviewModal"
                                                            onclick="loadEditReview(<?= $review['id'] ?>, <?= $review['rating'] ?>, '<?= htmlspecialchars($review['comment']) ?>')">
                                                        <i class="fa-solid fa-pencil"></i>
                                                    </button>
                                                    <form action="/review/delete" method="POST" style="display: inline;">
                                                        <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                        <input type="hidden" name="return_url" value="<?= $_SERVER['REQUEST_URI'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                onclick="return confirm('Bạn chắc chắn muốn xóa đánh giá này?')">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="text-warning small" style="letter-spacing: 2px;">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <?php if($i <= $review['rating']): ?>
                                                        <i class="fa-solid fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="fa-regular fa-star" style="color: #ddd;"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                <span class="ms-2 fw-bold text-dark" style="font-size: 13px;"><?= $review['rating'] ?>.0/5.0</span>
                                            </div>
                                        </div>
                                        
                                        <?php if(!empty($review['comment'])): ?>
                                            <p class="review-comment mb-0" style="color: #555; font-size: 14px; line-height: 1.6; padding: 12px 14px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #667eea;">
                                                <?= htmlspecialchars($review['comment']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Review Modal -->
<?php if(isset($_SESSION['user']) && isset($reviewModel) && $reviewModel->hasPurchasedProduct($_SESSION['user']['id'], $product['id'] ?? 0)): ?>
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Đánh giá sản phẩm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="/review/store" method="POST">
                <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                <div class="modal-body">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Đánh giá của bạn</label>
                        <div class="d-flex gap-2" id="ratingStars">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="d-none">
                                <label for="star<?= $i ?>" class="fs-3 text-muted cursor-pointer" style="cursor: pointer;">
                                    <i class="fa-solid fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nhận xét (tùy chọn)</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Chia sẻ cảm nhận của bạn..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<?php if(isset($_SESSION['user'])): ?>
<div class="modal fade" id="editReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Chỉnh sửa đánh giá
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="/review/update" method="POST">
                <?php echo \App\Core\Csrf::getHtmlInput(); ?>
                <div class="modal-body">
                    <input type="hidden" name="review_id" id="editReviewId" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Đánh giá của bạn</label>
                        <div class="d-flex gap-2" id="editRatingStars">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="editStar<?= $i ?>" class="d-none">
                                <label for="editStar<?= $i ?>" class="fs-3 text-muted cursor-pointer" style="cursor: pointer;">
                                    <i class="fa-solid fa-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nhận xét (tùy chọn)</label>
                        <textarea name="comment" id="editComment" class="form-control" rows="4" placeholder="Chia sẻ cảm nhận của bạn..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật đánh giá</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function loadEditReview(reviewId, rating, comment) {
    document.getElementById('editReviewId').value = reviewId;
    document.getElementById('editComment').value = comment;
    
    // Set rating
    const editRatingLabels = document.querySelectorAll('#editRatingStars label');
    editRatingLabels.forEach((l, idx) => {
        if(idx < rating) {
            l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
        } else {
            l.innerHTML = '<i class="fa-solid fa-star text-muted"></i>';
        }
    });
    
    // Set radio button
    document.getElementById('editStar' + rating).checked = true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit modal star rating interaction
    const editRatingLabels = document.querySelectorAll('#editRatingStars label');
    editRatingLabels.forEach(label => {
        label.addEventListener('click', function() {
            const value = this.previousElementSibling.value;
            editRatingLabels.forEach((l, idx) => {
                if(idx < value) {
                    l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
                } else {
                    l.innerHTML = '<i class="fa-solid fa-star"></i>';
                }
            });
        });
        
        label.addEventListener('mouseenter', function() {
            const value = this.previousElementSibling.value;
            editRatingLabels.forEach((l, idx) => {
                if(idx < value) {
                    l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
                } else {
                    l.innerHTML = '<i class="fa-solid fa-star text-muted"></i>';
                }
            });
        });
    });
    
    document.getElementById('editRatingStars').addEventListener('mouseleave', function() {
        const checked = document.querySelector('#editRatingStars input[type="radio"]:checked');
        editRatingLabels.forEach((l, idx) => {
            if(checked && idx < checked.value) {
                l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
            } else {
                l.innerHTML = '<i class="fa-solid fa-star text-muted"></i>';
            }
        });
    });
    
    // Star rating interaction for create review modal
    const ratingLabels = document.querySelectorAll('#ratingStars label');
    ratingLabels.forEach(label => {
        label.addEventListener('click', function() {
            const value = this.previousElementSibling.value;
            ratingLabels.forEach((l, idx) => {
                if(idx < value) {
                    l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
                } else {
                    l.innerHTML = '<i class="fa-solid fa-star"></i>';
                }
            });
        });
        
        label.addEventListener('mouseenter', function() {
            const value = this.previousElementSibling.value;
            ratingLabels.forEach((l, idx) => {
                if(idx < value) {
                    l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
                } else {
                    l.innerHTML = '<i class="fa-solid fa-star text-muted"></i>';
                }
            });
        });
    });
    
    document.getElementById('ratingStars').addEventListener('mouseleave', function() {
        const checked = document.querySelector('#ratingStars input[type="radio"]:checked');
        ratingLabels.forEach((l, idx) => {
            if(checked && idx < checked.value) {
                l.innerHTML = '<i class="fa-solid fa-star text-warning"></i>';
            } else {
                l.innerHTML = '<i class="fa-solid fa-star text-muted"></i>';
            }
        });
    });
});
</script>
<?php endif; ?>

<?php if (!empty($sale_info)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lấy thời gian kết thúc từ PHP (đã được truyền vào $sale_info['end_time'])
    const endTimeString = "<?= $sale_info['end_time'] ?>";
    
    // Xử lý định dạng ngày cho Safari/Firefox nếu cần (thay - bằng /)
    const endTime = new Date(endTimeString.replace(/-/g, "/")).getTime();

    const timerElement = document.getElementById("countdown-timer");

    // Cập nhật mỗi giây
    const x = setInterval(function() {
        const now = new Date().getTime();
        const distance = endTime - now;

        // Nếu hết giờ
        if (distance < 0) {
            clearInterval(x);
            timerElement.innerHTML = "ĐÃ KẾT THÚC";
            // Có thể reload trang để cập nhật lại giá: location.reload();
            return;
        }

        // Tính toán ngày, giờ, phút, giây
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Hiển thị (thêm số 0 đằng trước nếu < 10)
        timerElement.innerHTML = 
            (days < 10 ? "0" + days : days) + " ngày " + 
            (hours < 10 ? "0" + hours : hours) + " : " + 
            (minutes < 10 ? "0" + minutes : minutes) + " : " + 
            (seconds < 10 ? "0" + seconds : seconds);
    }, 1000);
});
</script>
<?php endif; ?>

<!-- Toast notification container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fa-solid fa-check-circle me-2"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Handle add to cart AJAX
    document.getElementById('addToCartBtn').addEventListener('click', function() {
        const formData = new FormData();
        formData.append('product_id', document.querySelector('input[name="product_id"]').value);
        formData.append('quantity', document.getElementById('quantity').value);

        fetch('/cart/add_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success toast
                document.getElementById('toastMessage').textContent = data.message;
                const toast = new bootstrap.Toast(document.getElementById('cartToast'));
                toast.show();

                // Update cart count in header if exists
                updateCartCount();
            } else {
                // Show error toast
                document.getElementById('toastMessage').textContent = data.message;
                document.getElementById('cartToast').className = 'toast align-items-center text-white bg-danger border-0';
                const toast = new bootstrap.Toast(document.getElementById('cartToast'));
                toast.show();

                // Reset to success class for next use
                setTimeout(() => {
                    document.getElementById('cartToast').className = 'toast align-items-center text-white bg-success border-0';
                }, 100);

                // Redirect if login required
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('toastMessage').textContent = 'Có lỗi xảy ra. Vui lòng thử lại!';
            document.getElementById('cartToast').className = 'toast align-items-center text-white bg-danger border-0';
            const toast = new bootstrap.Toast(document.getElementById('cartToast'));
            toast.show();
            setTimeout(() => {
                document.getElementById('cartToast').className = 'toast align-items-center text-white bg-success border-0';
            }, 100);
        });
    });

    // Function to update cart count (if header has cart count element)
    function updateCartCount() {
        // Fetch updated cart count from server
        fetch('/cart/get_count', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badgeElement = document.getElementById('cartCountBadge');
                if (data.count > 0) {
                    if (badgeElement) {
                        badgeElement.textContent = data.count;
                        badgeElement.style.display = 'inline-block';
                    } else {
                        // Create badge if it doesn't exist
                        const cartIcon = document.querySelector('.fa-cart-shopping').parentElement;
                        const newBadge = document.createElement('span');
                        newBadge.id = 'cartCountBadge';
                        newBadge.className = 'badge rounded-pill bg-danger badge-count';
                        newBadge.textContent = data.count;
                        cartIcon.appendChild(newBadge);
                    }
                } else {
                    if (badgeElement) {
                        badgeElement.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
    }
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>