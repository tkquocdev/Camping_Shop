<?php
// views/layouts/header.php

// 1. Logic lấy thông tin User & Giỏ hàng
// Kiểm tra session đã start chưa để tránh lỗi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

// --- [SỬA ĐOẠN NÀY] ---
$totalCartItems = 0;

if ($user) {
    // A. NẾU ĐÃ ĐĂNG NHẬP: Lấy số lượng từ Database
    // Khởi tạo Model Cart trực tiếp (vì đây là Layout dùng chung)
    // Lưu ý: Đảm bảo class App\Models\Cart đã tồn tại và autoload hoạt động
    try {
        if (class_exists('\App\Models\Cart')) {
            $cartModelHeader = new \App\Models\Cart();
            // Gọi hàm countItems vừa thêm ở Bước trước
            $totalCartItems = $cartModelHeader->countItems($user['id']);
        }
    } catch (Exception $e) {
        // Nếu lỗi kết nối DB ở header thì tạm để 0
        $totalCartItems = 0;
    }
} else {
    // B. NẾU KHÁCH VÃNG LAI: Lấy số lượng từ Session
    $totalCartItems = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
}
// --- [KẾT THÚC SỬA] ---


// 2. Logic Active Menu (để tô màu menu hiện tại)
$current_uri = $_SERVER['REQUEST_URI'];
function isActive($uri, $keyword) {
    // Logic đơn giản: nếu URL chứa keyword thì active
    return strpos($uri, $keyword) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo \App\Core\Security::getCSRFToken(); ?>">
    <title><?= $page_title ?? 'Camping Shop - Đồ dã ngoại chính hãng' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #ffc107; 
            --dark-bg: #151515;       
            --text-light: #f8f9fa;
        }

        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
        main { flex: 1; }

        /* --- NAVBAR STYLING --- */
        .navbar-custom {
            background-color: var(--dark-bg) !important;
            padding: 10px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .navbar-brand { font-size: 1.4rem; letter-spacing: 0.5px; }

        /* Menu Links */
        .nav-link {
            font-size: 0.85rem; 
            font-weight: 600; 
            text-transform: uppercase;
            color: rgba(255,255,255,0.75) !important;
            transition: all 0.3s ease; 
            position: relative; 
            margin: 0 5px; /* GIẢM TỪ 8px XUỐNG 5px ĐỂ TIẾT KIỆM CHỖ */
            letter-spacing: 0.5px;
            white-space: nowrap; /* QUAN TRỌNG: KHÔNG CHO XUỐNG DÒNG */
        }
        .nav-link:hover, .nav-link.active { color: var(--primary-color) !important; }
        .nav-link::after {
            content: ''; position: absolute; width: 0; height: 2px;
            bottom: -2px; left: 50%; background-color: var(--primary-color);
            transition: all 0.3s ease; transform: translateX(-50%);
        }
        .nav-link:hover::after, .nav-link.active::after { width: 100%; }

        /* Search Bar */
        .search-group {
            background: #fff; 
            border-radius: 50px; 
            padding: 2px;
            overflow: hidden; 
            border: 2px solid transparent;
            transition: all 0.3s; 
            width: 100%; 
            height: 38px;
            max-width: 240px;
        }
        .search-group:focus-within { border-color: var(--primary-color); box-shadow: 0 0 8px rgba(255, 193, 7, 0.4); }
        .search-input { border: none; outline: none; box-shadow: none !important; padding-left: 15px; background: transparent; font-size: 0.9rem; height: 32px; }
        .search-btn {
            border-radius: 50px !important; padding: 0 15px;
            background: var(--dark-bg); color: #fff; transition: 0.3s;
            height: 32px; line-height: 32px; display: flex; align-items: center;
        }
        .search-btn:hover { background: var(--primary-color); color: #000; }

        /* Icons & Actions */
        .action-icon-btn {
            position: relative; color: #fff; font-size: 1.1rem; margin-left: 10px;
            transition: transform 0.2s; display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.05); flex-shrink: 0;
            text-decoration: none;
        }
        .action-icon-btn:hover { background: rgba(255,255,255,0.15); color: var(--primary-color); transform: translateY(-2px); cursor: pointer; }
        
        .badge-count {
            position: absolute; top: -2px; right: -2px;
            font-size: 0.6rem; padding: 0.2em 0.4em; border: 2px solid var(--dark-bg);
        }

        /* Avatar */
        .user-avatar-small {
            width: 30px; height: 30px; object-fit: cover;
            border: 2px solid var(--primary-color); padding: 1px;
        }
        .user-name-text { font-size: 0.9rem; max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #fff; margin-left: 8px;}

        /* Auth Buttons */
        .btn-auth { font-size: 0.85rem; font-weight: 600; border-radius: 50px; padding: 6px 16px; white-space: nowrap; transition: 0.3s; text-decoration: none; display: inline-block;}
        .btn-auth-login { color: #fff; border: 1px solid rgba(255,255,255,0.3); }
        .btn-auth-login:hover { background: rgba(255,255,255,0.1); border-color: #fff; color: #fff; }
        .btn-auth-register { background: var(--primary-color); color: #000; border: none; }
        .btn-auth-register:hover { background: #e0a800; transform: translateY(-1px); }

        /* RESPONSIVE - Tablet (768px to 1024px) */
        @media (max-width: 1024px) {
            .navbar-brand { font-size: 1.2rem; }
            .nav-link { font-size: 0.75rem; margin: 0 3px; }
            .search-group { max-width: 180px; }
            .search-input { font-size: 0.8rem; height: 28px; }
            .search-btn { height: 28px; padding: 0 12px; font-size: 0.85rem; }
            .user-name-text { max-width: 100px; font-size: 0.8rem; }
            .action-icon-btn { width: 32px; height: 32px; font-size: 1rem; margin-left: 8px; }
        }

        /* RESPONSIVE - Mobile (< 768px) */
        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem; }
            .navbar-brand span { display: none !important; }
            .nav-link { font-size: 0.7rem; margin: 0 2px; padding: 0.3rem 0; }
            .search-group { max-width: 120px; }
            .search-input { font-size: 0.75rem; height: 26px; padding-left: 10px; }
            .search-btn { height: 26px; padding: 0 8px; font-size: 0.75rem; }
            .user-name-text { display: none; }
            .user-avatar-small { width: 24px; height: 24px; }
            .action-icon-btn { width: 28px; height: 28px; font-size: 0.9rem; margin-left: 6px; }
            .btn-auth { font-size: 0.7rem; padding: 4px 12px; }
        }

        /* RESPONSIVE - Small Mobile (< 480px) */
        @media (max-width: 480px) {
            .navbar { padding: 5px 0; }
            .navbar-brand { font-size: 0.9rem; }
            .nav-link { font-size: 0.65rem; margin: 0 1px; }
            .search-group { max-width: 80px; }
            .search-input { font-size: 0.7rem; height: 24px; padding-left: 8px; }
            .search-btn { height: 24px; padding: 0 6px; }
            .action-icon-btn { width: 24px; height: 24px; font-size: 0.8rem; margin-left: 4px; }
        }

        @media (max-width: 991px) {
            .search-form { margin: 15px 0; width: 100%; }
            .search-group { max-width: 100%; }
            .action-icon-btn { margin-left: 0; margin-right: 10px; display: inline-flex; }
            .auth-buttons { margin-top: 15px; width: 100%; justify-content: center; }
        }

        @media (min-width: 992px) and (max-width: 1100px) {
            .navbar-brand span { display: none !important; }
        }
    </style>
</head>
<body>

<?php
// Global toast notifications
$toastMessage = null;
$toastType = 'success';
if (isset($_SESSION['flash_message'])) {
    $toastMessage = htmlspecialchars($_SESSION['flash_message']);
    $toastType = 'success';
    unset($_SESSION['flash_message']);
} elseif (isset($_SESSION['flash_error'])) {
    $toastMessage = htmlspecialchars($_SESSION['flash_error']);
    $toastType = 'danger';
    unset($_SESSION['flash_error']);
}
?>

<?php if ($toastMessage): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1099;">
        <div id="globalToast" class="toast align-items-center text-bg-<?= $toastType ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4500">
            <div class="d-flex">
                <div class="toast-body">
                    <?= $toastMessage ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
<?php endif; ?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/">
            <i class="fa-solid fa-campground text-warning display-6" style="font-size: 1.8rem;"></i>
            <span class="d-none d-sm-block">CAMPING SHOP</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?= $current_uri == '/' ? 'active' : '' ?>" href="/">Trang chủ</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= isActive($current_uri, '/product') ?>" href="/product">Sản phẩm</a>
                </li>

                <li class="nav-item position-relative">
                    <a class="nav-link <?= isActive($current_uri, '/coupons') ?>" href="/coupon">
                        Khuyến mãi
                        <span class="badge bg-danger rounded-pill position-absolute badge-sm" 
                            style="font-size: 0.55rem; padding: 0.25em 0.4em; top: 0px; right: -5px;">HOT</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= isActive($current_uri, '/news') ?>" href="/news">Tin tức</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= isActive($current_uri, '/about') ?>" href="/about">Giới thiệu</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= isActive($current_uri, '/contact') ?>" href="/contact">Liên hệ</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center flex-column flex-lg-row">
                
                <form class="d-flex search-form me-lg-2" action="/product/search" method="GET">
                    <div class="input-group search-group">
                        <input class="form-control search-input" type="search" name="keyword" placeholder="Tìm kiếm..." required>
                        <button class="btn search-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                </form>

                <div class="d-flex align-items-center mt-2 mt-lg-0">
                    <a href="/cart/index" class="action-icon-btn" title="Giỏ hàng">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <?php if($totalCartItems > 0): ?>
                            <span id="cartCountBadge" class="badge rounded-pill bg-danger badge-count"><?= $totalCartItems ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if(isset($user)): ?>
                        <div class="dropdown ms-2">
                            <a href="#" class="action-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" id="notifyDropdownBtn" onclick="markAllAsRead(event)">
                                <i class="fa-regular fa-bell"></i>
                                <span class="badge rounded-pill bg-danger badge-count" id="notifyDot" style="display:none">0</span>
                            </a>
                            
                            <ul class="dropdown-menu dropdown-menu-end shadow notify-dropdown animate slideIn" aria-labelledby="notifyDropdownBtn">
                                <li class="dropdown-header fw-bold bg-light py-2 d-flex justify-content-between align-items-center border-bottom">
                                    <span>Thông báo mới</span>
                                    <small><a href="/profile/notifications" class="text-decoration-none" style="font-size: 0.75rem;">Xem tất cả</a></small>
                                </li>
                                
                                <div id="notifyList">
                                    <li><span class="dropdown-item text-muted text-center py-3"><i class="fas fa-spinner fa-spin"></i> Đang tải...</span></li>
                                </div>
                            </ul>
                        </div>

                        <div class="dropdown ms-2">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-light" data-bs-toggle="dropdown">
                                <?php 
                                    $headerUserAvatar = $_SESSION['user']['avatar'] ?? null;
                                    $headerUserFullName = $_SESSION['user']['full_name'] ?? 'User';
                                    $headerDefaultAvatar = 'https://ui-avatars.com/api/?background=random&color=fff&name=' . urlencode($headerUserFullName);
                                    $headerAvatarUrl = $headerDefaultAvatar;

                                    if (!empty($headerUserAvatar)) {
                                        // Nếu avatar là URL (Google profile, v.v.)
                                        if (filter_var($headerUserAvatar, FILTER_VALIDATE_URL)) {
                                            $headerAvatarUrl = $headerUserAvatar;
                                        } else {
                                            // Avatar là file local
                                            $headerRelative = '/uploads/' . ltrim($headerUserAvatar, '/');
                                            $headerFullPath = ROOT_PATH . '/public' . $headerRelative;

                                            if (!file_exists($headerFullPath)) {
                                                $headerRelative = '/uploads/users/' . basename($headerUserAvatar);
                                                $headerFullPath = ROOT_PATH . '/public' . $headerRelative;
                                            }

                                            if (file_exists($headerFullPath)) {
                                                $headerAvatarUrl = $headerRelative . '?v=' . time();
                                            }
                                        }
                                    }
                                ?>
                                <img src="<?= $headerAvatarUrl ?>" class="rounded-circle user-avatar-small" alt="Avatar">
                                <span class="fw-semibold d-none d-lg-block user-name-text">
                                    <?= htmlspecialchars($user['full_name'] ?? 'User') ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow animate slideIn">
                                <li><a class="dropdown-item" href="/profile/index"><i class="fa-solid fa-user me-2"></i> Thông tin tài khoản</a></li>
                                <li><a class="dropdown-item" href="/profile/addresses"><i class="fa-solid fa-map-location-dot me-2"></i> Địa chỉ nhận hàng</a></li>
                                <li><a class="dropdown-item" href="/profile/history"><i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử đơn hàng</a></li>
                                <li><a class="dropdown-item" href="/profile/notifications"><i class="fa-solid fa-bell me-2"></i> Thông báo của tôi</a></li>
                                <li><a class="dropdown-item" href="/profile/loyalty"><i class="fa-solid fa-gift me-2"></i> Đổi thưởng & Quà tặng</a></li>
                                <li><a class="dropdown-item" href="/profile/coupons"><i class="fa-solid fa-ticket me-2"></i> Kho voucher của tôi</a></li>
                                <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fw-bold text-primary" href="/admin/dashboard"><i class="fa-solid fa-gauge me-2"></i> Trang quản trị</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="/auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                            </ul>
                        </div>

                    <?php else: ?>
                        <div class="auth-buttons ms-3 d-flex align-items-center gap-2">
                            <a href="/auth/login" class="btn btn-auth btn-auth-login">
                                <i class="fa-regular fa-user me-1"></i> Đăng nhập
                            </a>
                            <a href="/auth/register" class="btn btn-auth btn-auth-register">
                                Đăng ký
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Message Display -->
<?php if(isset($_SESSION['flash_message'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="max-width: 400px;">
            <i class="fa-solid fa-check-circle me-2"></i>
            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['flash_error'])): ?>
    <div class="position-fixed top-0 start-0 p-3" style="z-index: 1050;">
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="max-width: 400px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<main>

<?php if(isset($user)): ?>
<script>
document.addEventListener("DOMContentLoaded", function() { 
    // 1. Gọi hàm load ngay khi tải trang
    loadHeaderNotifications(); 
    
    // 2. Cập nhật tự động mỗi 30s
    setInterval(loadHeaderNotifications, 30000);
});

// --- HÀM 1: LẤY DỮ LIỆU (Đã sửa URL và Logic hiển thị) ---
function loadHeaderNotifications() {
    const apiUrl = '/notification/ajaxList'; 

    fetch(apiUrl) 
    .then(res => {
        if (!res.ok) throw new Error('Lỗi kết nối mạng');
        return res.json();
    })
    .then(data => {
        const listContainer = document.getElementById('notifyList');
        const badgeDot = document.getElementById('notifyDot');
        
        // A. Xử lý Chấm Đỏ (Badge) - Dựa vào biến count từ Controller
        if (data.count > 0) {
            badgeDot.innerText = data.count > 99 ? '99+' : data.count;
            badgeDot.style.display = 'inline-block'; 
            badgeDot.classList.add('animate', 'bounceIn');
        } else {
            badgeDot.style.display = 'none';
        }

        // B. Xử lý Danh sách (Quan trọng: Controller trả về HTML, không phải JSON mảng)
        if (data.html) {
            listContainer.innerHTML = data.html;
        } else {
             listContainer.innerHTML = '<li><div class="text-muted text-center py-4">Không có dữ liệu</div></li>';
        }
    })
    .catch(e => {
        console.error("Lỗi tải thông báo:", e);
    });
}

// --- HÀM 2: CLICK VÀO TIN (Đã sửa URL) ---
function handleNotifyClick(event, id, link) {
    // Nếu không có link hoặc link là #, thì chỉ mark as read
    if(!link || link === '#') {
        event.preventDefault();
        markSingleAsRead(id);
        return;
    }
    
    // Nếu có link hợp lệ, chuyển trang
    event.preventDefault();
    window.location.href = link;
}

// Mark single notification as read
function markSingleAsRead(id) {
    const apiReadUrl = '/notification/ajaxRead';

    fetch(apiReadUrl, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .catch(err => console.log('Error marking as read:', err));
}

// Mark all notifications as read when clicking bell icon
function markAllAsRead(event) {
    event.preventDefault();
    
    const apiUrl = '/notification/markAllRead';

    fetch(apiUrl, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(response => {
        // Reload notifications after marking all as read
        loadHeaderNotifications();
    })
    .catch(err => console.log('Error:', err));
}
</script>
<?php endif; ?>