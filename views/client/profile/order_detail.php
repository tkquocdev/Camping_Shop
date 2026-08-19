<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">

    <div class="mb-4">
        <a href="/profile/history" class="text-decoration-none text-secondary">
            <i class="fa-solid fa-arrow-left"></i> Quay lại lịch sử đơn hàng
        </a>
    </div>

    <?php
    $statusSteps = [
        'pending'    => ['label' => 'Đã đặt', 'icon' => 'fa-check', 'color' => 'primary'],
        'processing' => ['label' => 'Đang xử lý', 'icon' => 'fa-cogs', 'color' => 'info'],
        'shipped'    => ['label' => 'Đang vận chuyển', 'icon' => 'fa-truck', 'color' => 'warning'],
        'delivered'  => ['label' => 'Đã giao', 'icon' => 'fa-box', 'color' => 'success'],
        'completed'  => ['label' => 'Hoàn thành', 'icon' => 'fa-check-circle', 'color' => 'success'],
    ];

    $isCancelled = $order['status'] === 'cancelled';
    
    if ($isCancelled) {
        // If cancelled, show pipeline up to cancelled status
        $pipeline = ['pending', 'processing', 'shipped', 'cancelled'];
    } else {
        // Show full pipeline for active orders
        $pipeline = ['pending', 'processing', 'shipped', 'delivered', 'completed'];
    }

    $currentIndex = array_search($order['status'], $pipeline);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h5 class="mb-1 fw-bold">Đơn hàng #<?= $order['id'] ?></h5>
                    <small class="text-muted">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                </div>

                <div class="d-flex gap-2">
                    <a href="/checkout/invoice_pdf?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i>Xem hóa đơn
                    </a>
                    <?php if ($order['status'] === 'completed' && !empty($items)): ?>
                        <?php 
                            $firstItem = $items[0];
                            $productId = $firstItem['product_id'] ?? null;
                        ?>
                        <?php if ($productId): ?>
                            <a href="/product/detail/<?= $productId ?>#reviewModal" class="btn btn-outline-success btn-sm">
                                <i class="fa-solid fa-star me-1"></i>Đánh giá
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-info btn-sm" onclick="buyAgain(<?= $order['id'] ?>)">
                            <i class="fa-solid fa-redo me-1"></i>Mua lại
                        </button>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'pending'): ?>
                        <button class="btn btn-outline-danger btn-sm" onclick="confirmCancel(<?= $order['id'] ?>)">
                            <i class="fa-solid fa-ban me-1"></i>Hủy đơn
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex align-items-center">
                    <?php foreach ($pipeline as $index => $step):
                        if ($step === 'cancelled') {
                            // Cancelled status - special handling
                            $stepInfo = ['label' => 'Đã hủy', 'icon' => 'fa-ban', 'color' => 'danger'];
                        } else {
                            $stepInfo = $statusSteps[$step] ?? ['label' => ucfirst($step), 'icon' => 'fa-circle', 'color' => 'secondary'];
                        }
                        $isDone = $index <= $currentIndex;
                        $isActive = $index === $currentIndex;
                    ?>
                        <div class="text-center flex-fill">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle border" style="width: 40px; height: 40px; <?= $isDone ? "background: #0d6efd; color: #fff; border-color: #0d6efd;" : ($isActive && $isCancelled ? "background: #dc3545; color: #fff; border-color: #dc3545;" : "background: #f1f3f5; color: #6c757d;") ?>">
                                <i class="fa-solid <?= $stepInfo['icon'] ?>"></i>
                            </div>
                            <div class="mt-2 small <?= $isActive ? ($isCancelled ? 'fw-bold text-danger' : 'fw-bold text-primary') : 'text-muted' ?>" style="max-width: 120px;">
                                <?= $stepInfo['label'] ?>
                            </div>
                        </div>

                        <?php if ($index < count($pipeline) - 1): ?>
                            <div class="flex-grow-1 px-2">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar <?= $isDone ? ($isCancelled && $currentIndex > $index ? 'bg-danger' : 'bg-primary') : 'bg-light' ?>" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold text-secondary">Thông tin đơn hàng</h6>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> <?= ucfirst($order['payment_method']) ?></p>
                    <p class="mb-0"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note'] ?? 'Không có') ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold text-secondary">Địa chỉ giao hàng</h6>
                    <p class="mb-1"><strong>Người nhận:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                    <p class="mb-1"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                </div>
            </div>

            <hr>

            <h6 class="fw-bold text-secondary mb-3">Chi tiết sản phẩm</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                            <?php if ($order['status'] === 'completed'): ?>
                                <th class="text-center">Hành động</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-end"><?= number_format($item['price']) ?> đ</td>
                                <td class="text-end fw-bold"><?= number_format($item['quantity'] * $item['price']) ?> đ</td>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <td class="text-center">
                                        <a href="/product/detail/<?= $item['product_id'] ?>#reviewModal" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Đánh giá sản phẩm">
                                            <i class="fa-solid fa-star me-1"></i>Đánh giá
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Tạm tính:</span>
                        <strong><?= number_format($order['total_amount'] + $order['discount_amount'] - $order['shipping_fee']) ?> đ</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Phí vận chuyển:</span>
                        <strong><?= number_format($order['shipping_fee']) ?> đ</strong>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div class="mb-2 d-flex justify-content-between text-danger">
                            <span>Giảm giá:</span>
                            <strong>-<?= number_format($order['discount_amount']) ?> đ</strong>
                        </div>
                    <?php endif; ?>
                    <div class="border-top pt-2 d-flex justify-content-between" style="font-size: 1.2rem;">
                        <span class="fw-bold">Tổng cộng:</span>
                        <span class="text-danger fw-bold"><?= number_format($order['total_amount']) ?> đ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCancel(orderId) {
    if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        window.location.href = '/profile/cancel_order?id=' + orderId;
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
