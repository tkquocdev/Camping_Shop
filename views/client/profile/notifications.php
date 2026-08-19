<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<style>
    /* --- CSS TÙY CHỈNH CHO TRANG THÔNG BÁO --- */
    .profile-container {
        min-height: 80vh;
        background-color: #f8f9fa;
    }
    
    .notify-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .notification-item {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        cursor: pointer;
    }

    .notification-item:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
    }

    /* CHƯA ĐỌC */
    .notification-item.unread {
        background-color: #eef6fc;
        border-left-color: #0d6efd;
    }
    .notification-item.unread .notify-title {
        font-weight: 700;
        color: #0d6efd;
    }
    .notification-item.unread .notify-content {
        color: #212529;
        font-weight: 500;
    }

    /* ĐÃ ĐỌC */
    .notification-item.read {
        background-color: #fff;
    }
    .notification-item.read .notify-title {
        font-weight: 600;
        color: #495057;
    }
    .notification-item.read .notify-content {
        color: #6c757d;
    }

    .icon-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>

<div class="container py-5 profile-container">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm">
                <a href="/profile/index" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-user me-2"></i> Thông tin tài khoản
                </a>
                
                <a href="/profile/addresses" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-map-location-dot me-2"></i> Địa chỉ nhận hàng
                </a>

                <a href="/profile/history" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử đơn hàng
                </a>

                <a href="/profile/notifications" class="list-group-item list-group-item-action active fw-bold">
                    <i class="fa-solid fa-bell me-2"></i> Thông báo của tôi
                    
                    <?php 
                        // Logic đếm badge chưa đọc
                        $unreadCount = 0;
                        if (!empty($notifications)) {
                            foreach($notifications as $n) if($n['is_read'] == 0) $unreadCount++;
                        }
                    ?>
                    <?php if($unreadCount > 0): ?>
                        <span id="sidebar-badge" class="badge bg-danger rounded-pill float-end"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>

                <a href="/profile/loyalty" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-gift me-2"></i> Đổi thưởng & Quà tặng
                </a>

                <a href="/profile/coupons" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-ticket me-2"></i> Kho voucher của tôi
                </a>

                <a href="/auth/logout" class="list-group-item list-group-item-action text-danger">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card notify-card h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="mb-0 fw-bold text-dark">
                        Thông báo
                    </h5>
                    
                    <?php if ($unreadCount > 0): ?>
                        <button id="btnMarkAll" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="markAllRead()">
                            <i class="fa-solid fa-check-double me-1"></i> Đánh dấu đã đọc hết
                        </button>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="notificationList">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notify): ?>
                                <?php 
                                    // Xử lý dữ liệu hiển thị
                                    $isRead = ($notify['is_read'] == 1);
                                    $statusClass = $isRead ? 'read' : 'unread'; 
                                    
                                    // Link đích
                                    $link = !empty($notify['link']) ? $notify['link'] : '#';
                                    
                                    // Nội dung fallback
                                    $content = $notify['content'] ?? $notify['message'] ?? 'Không có nội dung';
                                ?>
                                
                                <a href="<?= $link ?>" 
                                   class="list-group-item list-group-item-action p-3 notification-item <?= $statusClass ?>"
                                   id="notify-item-<?= $notify['id'] ?>"
                                   onclick="handleItemClick(event, <?= $notify['id'] ?>, '<?= $link ?>', <?= $isRead ? 1 : 0 ?>)">
                                   
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 mt-1">
                                            <div class="icon-circle shadow-sm <?= $isRead ? 'bg-light' : 'bg-primary bg-gradient' ?>">
                                                <i class="fa-solid <?= $isRead ? 'fa-envelope-open text-secondary' : 'fa-envelope text-white' ?> icon-status fs-5"></i>
                                            </div>
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <h6 class="mb-1 notify-title"><?= htmlspecialchars($notify['title']) ?></h6>
                                                
                                                <small class="text-muted ms-2" style="white-space: nowrap; font-size: 0.75rem;">
                                                    <?= date('H:i d/m', strtotime($notify['created_at'])) ?>
                                                </small>
                                            </div>
                                            
                                            <p class="mb-1 small notify-content">
                                                <?= htmlspecialchars($content) ?>
                                            </p>

                                            <?php if(!$isRead): ?>
                                                <span class="badge bg-danger badge-new mt-1" style="font-size: 0.65rem;">MỚI</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fa-regular fa-bell-slash text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                </div>
                                <h6 class="text-muted fw-bold">Chưa có thông báo nào</h6>
                                <p class="small text-muted mb-0">Hệ thống sẽ gửi thông báo khi có cập nhật đơn hàng hoặc khuyến mãi.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($notifications) && count($notifications) >= 10): ?>
                <div class="card-footer bg-white text-center py-3 border-top-0">
                    <button class="btn btn-light btn-sm text-muted px-4 rounded-pill">Xem thêm thông báo cũ</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * HÀM 1: Xử lý click vào 1 thông báo
 */
async function handleItemClick(event, id, link, isRead) {
    event.preventDefault(); // Chặn chuyển trang ngay lập tức

    // Nếu link rỗng, chỉ đánh dấu đọc
    const shouldRedirect = (link && link !== '#' && link !== 'javascript:void(0);');

    // Nếu đã đọc rồi: Chuyển trang ngay (nếu có link)
    if (isRead) {
        if (shouldRedirect) window.location.href = link;
        return;
    }

    // --- OPTIMISTIC UI: Cập nhật giao diện NGAY LẬP TỨC ---
    const item = document.getElementById('notify-item-' + id);
    if(item) {
        item.classList.remove('unread');
        item.classList.add('read');
        
        // Đổi Icon
        const iconContainer = item.querySelector('.icon-circle');
        const icon = item.querySelector('.icon-status');
        
        if(iconContainer) {
            iconContainer.classList.remove('bg-primary', 'bg-gradient');
            iconContainer.classList.add('bg-light');
        }
        if(icon) {
            icon.classList.remove('fa-envelope', 'text-white');
            icon.classList.add('fa-envelope-open', 'text-secondary');
        }

        // Ẩn badge "Mới" trong item
        const badge = item.querySelector('.badge-new');
        if(badge) badge.style.display = 'none';
        
        // Giảm số trên Sidebar Badge
        const sidebarBadge = document.getElementById('sidebar-badge');
        if(sidebarBadge) {
            let count = parseInt(sidebarBadge.innerText) || 0;
            if(count > 0) {
                count--;
                sidebarBadge.innerText = count > 0 ? count : '';
                if(count === 0) sidebarBadge.style.display = 'none';
            }
        }
    }

    // --- GỌI API SERVER (Giữ nguyên logic Backend) ---
    try {
        await fetch('/index.php?controller=notification&action=ajaxRead', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id }),
            keepalive: true
        });
    } catch (error) {
        console.error("Lỗi sync server:", error);
    }

    // --- CHUYỂN TRANG ---
    if (shouldRedirect) {
        window.location.href = link;
    }
}

/**
 * HÀM 2: Đánh dấu tất cả đã đọc
 */
function markAllRead() {
    const btn = document.getElementById('btnMarkAll');
    if(!btn) return;
    
    // Hiệu ứng loading
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Đang xử lý...';
    btn.disabled = true;

    fetch('/index.php?controller=notification&action=ajaxMarkAll', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + (data.message || 'Lỗi không xác định'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối server');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>