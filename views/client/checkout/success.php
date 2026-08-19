<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <!-- Icon thành công -->
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h2 class="text-success mb-3">Đặt hàng thành công!</h2>

                    <p class="text-muted mb-4">
                        Cảm ơn bạn đã mua hàng tại <strong>Camping Shop</strong>.<br>
                        Đơn hàng của bạn đã được xử lý thành công.
                    </p>

                    <?php if(isset($order_id)): ?>
                        <div class="alert alert-info">
                            <h5>Mã đơn hàng: <strong>#<?= $order_id ?></strong></h5>
                            <p class="mb-0">Bạn có thể theo dõi trạng thái đơn hàng trong phần <a href="/profile/orders">Lịch sử đơn hàng</a></p>
                        </div>

                        <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                            <a href="/" class="btn btn-success btn-sm" style="font-size: 0.9rem; padding: 6px 12px;">
                                <i class="fas fa-shopping-bag me-1"></i>
                                Tiếp tục mua sắm
                            </a>
                            <a href="/checkout/invoice_pdf?id=<?= $order_id ?>" target="_blank" class="btn btn-primary btn-sm" style="font-size: 0.9rem; padding: 6px 12px;">
                                <i class="fas fa-file-pdf me-1"></i>
                                Xem hóa đơn PDF
                            </a>
                            <a href="/profile/history" class="btn btn-outline-primary btn-sm" style="font-size: 0.9rem; padding: 6px 12px; white-space: nowrap;">
                                <i class="fas fa-history me-1"></i>
                                Xem lịch sử
                            </a>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div class="text-start">
                        <h6>Thông tin hỗ trợ:</h6>
                        <p class="mb-1"><i class="fas fa-phone me-2"></i> Hotline: 0868.285.284</p>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i> Email: support@campingshop.com</p>
                        <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> Địa chỉ: Thới An Hội, Kế Sách, Sóc Trăng</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/" class="btn btn-link">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>