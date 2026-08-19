<style>
    /* CSS tùy chỉnh cho Footer */
    .footer-custom {
        background-color: #121519; /* Màu nền tối xanh đen giống thiết kế */
        color: #8c92a0; /* Màu chữ xám nhạt */
    }
    .footer-custom h5 {
        color: #ffffff;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .footer-custom a.text-muted-custom {
        color: #8c92a0;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .footer-custom a.text-muted-custom:hover,
    .footer-custom .social-icons a:hover {
        color: #ffffff; /* Sáng lên khi di chuột vào */
    }
    .footer-custom .social-icons a {
        color: #8c92a0;
        font-size: 1.2rem;
        transition: color 0.3s ease;
    }
    
    /* Nút quà tặng trôi nổi */
    .floating-gift-btn {
        width: 60px;
        height: 60px;
        background-color: #f39c12; /* Màu vàng cam */
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: transform 0.3s;
        border: none;
    }
    .floating-gift-btn:hover {
        transform: scale(1.1);
        background-color: #e67e22;
    }
</style>

<footer class="footer-custom pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-4 pe-md-5">
                <h5>Về Camping Shop</h5>
                <p style="font-size: 0.95rem; line-height: 1.6;">
                    Chuyên cung cấp các vật dụng cắm trại, dã ngoại chất lượng cao.<br>
                    Đồng hành cùng bạn trên mọi nẻo đường khám phá thiên nhiên.
                </p>
            </div>

            <div class="col-md-3 mb-4">
                <h5>Liên kết nhanh</h5>
                <ul class="list-unstyled" style="font-size: 0.95rem; line-height: 2;">
                    <li><a href="/" class="text-muted-custom">Trang chủ</a></li>
                    <li><a href="/product" class="text-muted-custom">Sản phẩm</a></li>
                    <li><a href="/coupon" class="text-muted-custom">Kho Voucher</a></li>
                    <li><a href="/about" class="text-muted-custom">Giới thiệu</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h5>Liên hệ</h5>
                <ul class="list-unstyled" style="font-size: 0.95rem; line-height: 2;">
                    <li class="d-flex align-items-start mb-2">
                        <i class="fas fa-map-marker-alt mt-1 me-3"></i>
                        <span>Thới An Hội, Kế Sách, Sóc Trăng</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone-alt me-3"></i>
                        <span>0868.285.284</span>
                    </li>
                    <li class="d-flex align-items-center mb-4">
                        <i class="fas fa-envelope me-3"></i>
                        <span>tkquocdev_support@campingshop.com</span>
                    </li>
                </ul>
                
                <div class="social-icons d-flex gap-3">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center" style="border-top: 1px solid #2a2e35; padding-top: 20px; font-size: 0.85rem;">
                &copy; 2026 Camping Shop. All rights reserved.
            </div>
        </div>
    </div>
</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toastEl = document.getElementById('globalToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    });
</script>

<!-- Game Lucky Spin Widget (Floating Bubble) -->
<?php
// Không hiển thị widget nếu đang ở trang auth
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$authPages = ['/auth/login', '/auth/register', '/auth/forgot', '/auth/forgot-password', '/auth/reset-password'];
$isAuthPage = false;
foreach ($authPages as $authPath) {
    if (strpos($currentPath, $authPath) === 0) {
        $isAuthPage = true;
        break;
    }
}

if (!$isAuthPage):
    // Fetch game widget data
    $gameController = new \App\Controllers\Client\GameController();
    $gameData = $gameController->getWidgetData();
    $prizes = $gameData['prizes'] ?? [];
    $has_played = $gameData['has_played'] ?? false;
    require_once ROOT_PATH . '/views/layouts/game_widget.php';
endif;
?>

<!-- Chatbot Widget (Floating Bubble) -->
<?php 
if (!$isAuthPage):
    require_once ROOT_PATH . '/views/layouts/chatbot.php';
endif;
?>

</body>
</html>