<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<style>
/* Ensure action buttons stay on one line */
.action-buttons {
    white-space: nowrap;
    overflow: auto;
    display: flex;
    gap: 0.25rem;
}
.action-buttons .btn {
    flex-shrink: 0;
    font-size: 0.75rem;
    padding: 0.35rem 0.5rem;
}
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm">
                <a href="/profile/index" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-user me-2"></i> Thông tin tài khoản
                </a>
                <a href="/profile/addresses" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-map-location-dot me-2"></i> Địa chỉ nhận hàng
                </a>
                <a href="/profile/history" class="list-group-item list-group-item-action active fw-bold">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Lịch sử đơn hàng
                </a>
                <a href="/profile/notifications" class="list-group-item list-group-item-action">
                    <i class="fa-solid fa-bell me-2"></i> Thông báo của tôi
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-primary mb-0">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử đơn hàng
                </h4>
                <a href="/product" class="btn btn-primary shadow-sm">
                    <i class="fa-solid fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="card shadow border-0 rounded-3">
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Bạn chưa có đơn hàng nào</h5>
                        <p class="text-secondary mb-4">Hãy bắt đầu mua sắm để tạo đơn hàng đầu tiên!</p>
                        <a href="/product" class="btn btn-primary">
                            <i class="fa-solid fa-arrow-right me-2"></i>Đến cửa hàng
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-secondary">
                                    <tr>
                                        <th class="py-3 ps-4">Mã đơn hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Trạng thái</th>
                                        <th>Tổng tiền</th>
                                        <th class="text-end pe-4">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <?php
                                        $status = $order['status'] ?? 'pending';

                                        // Mapping trạng thái
                                        $statusMap = [
                                            'pending'    => ['label' => 'Chờ xử lý', 'class' => 'bg-warning text-dark'],
                                            'processing' => ['label' => 'Đang xử lý', 'class' => 'bg-info text-white'],
                                            'shipped'    => ['label' => 'Đang giao hàng', 'class' => 'bg-primary text-white'],
                                            'delivered'  => ['label' => 'Đã giao', 'class' => 'bg-success text-white'],
                                            'completed'  => ['label' => 'Hoàn thành', 'class' => 'bg-success text-white'],
                                            'cancelled'  => ['label' => 'Đã hủy', 'class' => 'bg-danger text-white'],
                                        ];

                                        $statusInfo = $statusMap[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-secondary'];
                                        ?>
                                        <tr id="order-row-<?= $order['id'] ?>">
                                            <td class="ps-4 fw-bold text-primary">#<?= $order['id'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $statusInfo['class'] ?> rounded-pill px-3 py-2">
                                                    <?= $statusInfo['label'] ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-danger">
                                                <?= number_format($order['total_amount']) ?> đ
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="action-buttons">
                                                <a href="/profile/order_detail?id=<?= $order['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary me-2">
                                                    <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                                </a>
                                                <?php if ($order['status'] === 'completed' && !empty($order['items'])): ?>
                                                    <?php 
                                                        $firstItem = $order['items'][0];
                                                        $productId = $firstItem['product_id'] ?? null;
                                                    ?>
                                                    <?php if ($productId): ?>
                                                        <a href="/product/detail/<?= $productId ?>#reviewModal"
                                                           class="btn btn-sm btn-outline-success me-2"
                                                           title="Đánh giá sản phẩm trong đơn này">
                                                            <i class="fa-solid fa-star me-1"></i>Đánh giá
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($order['status'] === 'completed'): ?>
                                                    <button class="btn btn-sm btn-outline-info me-2" 
                                                            onclick="buyAgain(<?= $order['id'] ?>)"
                                                            title="Mua lại đơn hàng này">
                                                        <i class="fa-solid fa-redo me-1"></i>Mua lại
                                                    </button>
                                                <?php endif; ?>
                                                <?php if ($order['status'] === 'pending'): ?>
                                                    <a href="/profile/cancel_order?id=<?= $order['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger me-2"
                                                       onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                                                        <i class="fa-solid fa-ban me-1"></i>Hủy
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="removeOrder(<?= $order['id'] ?>)">
                                                    <i class="fa-solid fa-trash me-1"></i>Xóa
                                                </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function removeOrder(orderId) {
    const row = document.getElementById('order-row-' + orderId);
    if (!row) return;

    if (!confirm('Bạn có muốn xóa đơn hàng này khỏi lịch sử?')) return;

    row.remove();
    // Show toast
    const toastEl = document.getElementById('globalToast');
    if (toastEl) {
        toastEl.querySelector('.toast-body').textContent = 'Đã xóa đơn hàng khỏi lịch sử.';
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}

function buyAgain(orderId) {
    if (!confirm('Bạn có muốn mua lại những sản phẩm trong đơn hàng này?')) return;
    
    // Create form to submit to cart with reorder_id
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/cart/add';
    
    // Find CSRF token - look for _csrf_token field name
    const csrfToken = document.querySelector('input[name="_csrf_token"]') || 
                      document.querySelector('input[name="csrf_token"]') || 
                      document.querySelector('meta[name="csrf-token"]');
    const tokenValue = csrfToken ? (csrfToken.value || csrfToken.content) : '';
    
    if (tokenValue) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_csrf_token';
        csrfInput.value = tokenValue;
        form.appendChild(csrfInput);
    }
    
    const orderIdInput = document.createElement('input');
    orderIdInput.type = 'hidden';
    orderIdInput.name = 'reorder_id';
    orderIdInput.value = orderId;
    form.appendChild(orderIdInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>