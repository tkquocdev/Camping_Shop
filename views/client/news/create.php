<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Đánh giá đơn hàng #<?= $_GET['order_id'] ?></h5>
                </div>
                <div class="card-body">
                    <form action="index.php?controller=review&action=store" method="POST">
                        <input type="hidden" name="order_id" value="<?= $_GET['order_id'] ?>">
                        
                        <div class="mb-3">
                            <label>Chọn mức độ hài lòng:</label>
                            <select name="rating" class="form-select">
                                <option value="5">5 Sao - Tuyệt vời</option>
                                <option value="4">4 Sao - Tốt</option>
                                <option value="3">3 Sao - Bình thường</option>
                                <option value="2">2 Sao - Tệ</option>
                                <option value="1">1 Sao - Rất tệ</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Nhận xét của bạn:</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Sản phẩm dùng thế nào?"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>