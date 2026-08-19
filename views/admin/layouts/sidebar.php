<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark admin-sidebar" style="width: 280px; min-width: 280px; height: 100vh; overflow-x: hidden;">
    <a href="/admin/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fa-solid fa-campground me-2" style="font-size: 24px;"></i>
        <span class="fs-4 fw-bold">Camping Shop</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="/admin/dashboard"
               class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'dashboard') ? 'active bg-primary' : '' ?>">
                <i class="fa-solid fa-gauge me-3" style="width:25px; text-align:center;"></i>
                Dashboard
            </a>
        </li>

    <li class="nav-item">
        <a href="/admin/categories"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'categories') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-layer-group me-3" style="width:25px; text-align:center;"></i>
            Quản lý Danh mục
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/products"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'products') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-box me-3" style="width:25px; text-align:center;"></i>
            Quản lý Sản phẩm
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/coupons"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'coupons') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-ticket me-3" style="width:25px; text-align:center;"></i>
            Quản lý Khuyến mãi
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/flash_sale"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'flash_sale') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-bolt me-3" style="width:25px; text-align:center;"></i>
            Quản lý Flash Sale
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/suppliers"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'suppliers') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-truck me-3" style="width:25px; text-align:center;"></i>
            Quản lý Nhà cung cấp
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/stock"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'stock') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-warehouse me-3" style="width:25px; text-align:center;"></i>
            Quản lý Nhập kho
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/StockIssue"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'stock_issue') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-dolly me-3" style="width:25px; text-align:center;"></i>
            Quản lý Xuất kho
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/orders"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'orders') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-cart-flatbed me-3" style="width:25px; text-align:center;"></i>
            Quản lý Đơn hàng
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/orders/returns"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'returns') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-undo me-3" style="width:25px; text-align:center;"></i>
            Yêu cầu Trả hàng
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/users"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'users') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-users me-3" style="width:25px; text-align:center;"></i>
            Quản lý Người dùng
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/reviews"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'reviews') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-star me-3" style="width:25px; text-align:center;"></i>
            Đánh giá sản phẩm
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/notifications"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'notifications') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-bell me-3" style="width:25px; text-align:center;"></i>
            <span class="me-2">Quản Lý Thông Báo</span>
            <span id="adminNotifyBadge"
                  class="badge bg-danger rounded-pill ms-auto"
                  style="display:none; font-size: 0.75rem;">0</span>
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/news" class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'news') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-newspaper me-3" style="width:25px; text-align:center;"></i>
            Quản lý Tin tức
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/game" class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'game') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-gamepad me-3" style="width:25px; text-align:center;"></i>
            Mini Game (Vòng Quay)
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/loyalty" class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'loyalty') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-gift me-3" style="width:25px; text-align:center;"></i>
            Quản lý Đổi thưởng
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/customercare"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'crm') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-headset me-3" style="width:25px; text-align:center;"></i>
            Chăm sóc khách hàng
        </a>
    </li>

    <li class="nav-item">
        <a href="/admin/settings"
           class="nav-link d-flex align-items-center text-white text-nowrap <?= ($active == 'settings') ? 'active bg-primary' : '' ?>">
            <i class="fa-solid fa-gear me-3" style="width:25px; text-align:center;"></i>
            Cài đặt hệ thống
        </a>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user-circle me-2" style="font-size: 20px;"></i>
            <strong><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin'); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="/profile/index">Hồ sơ cá nhân</a></li>
            <li><a class="dropdown-item" href="/profile/settings">Cài đặt</a></li>
            <!-- Về trang chủ -->
            <li><a class="dropdown-item" href="/">Về trang chủ</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/auth/logout">Đăng xuất</a></li>
        </ul>
    </div>
</div>