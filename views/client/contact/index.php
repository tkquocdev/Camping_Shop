<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-dark">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Liên hệ</li>
        </ol>
    </nav>

    <div class="row g-5">
        <div class="col-md-5">
            <div class="p-4 bg-light rounded shadow-sm h-100">
                <h3 class="fw-bold text-primary mb-4">Camping Shop</h3>
                <p class="text-muted mb-4">
                    Chúng tôi luôn sẵn sàng lắng nghe và giải đáp mọi thắc mắc của bạn về sản phẩm dã ngoại.
                </p>
                
                <div class="d-flex align-items-start mb-3">
                    <div class="fs-4 text-primary me-3"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Địa chỉ</h6>
                        <p class="mb-0 text-secondary">Khu II, ĐH Cần Thơ, đường 3/2, P. Xuân Khánh, Q. Ninh Kiều, TP. Cần Thơ</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-3">
                    <div class="fs-4 text-primary me-3"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Hotline</h6>
                        <p class="mb-0 text-secondary">0868 285 824</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-3">
                    <div class="fs-4 text-primary me-3"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="mb-0 text-secondary">hotro@campingshop.vn</p>
                    </div>
                </div>
                
                <hr>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white p-3">
                    <h5 class="mb-0"><i class="fa-regular fa-pen-to-square me-2"></i>Gửi phiếu hỗ trợ</h5>
                </div>
                <div class="card-body p-4">
                    <form action="/contact/sendRequest" method="POST">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Họ tên</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['full_name'] ?? $_SESSION['user']['name'] ?? '') : '' ?>"
                                       <?= isset($_SESSION['user']) ? 'readonly' : 'required' ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['email'] ?? '') : '' ?>"
                                       <?= isset($_SESSION['user']) ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="tel" name="phone" class="form-control" 
                                   placeholder="Nhập số điện thoại của bạn">
                        </div>

                        <div class="mb-3">
                            <label for="topic" class="form-label fw-bold">Vấn đề cần hỗ trợ <span class="text-danger">*</span></label>
                            <select class="form-select" name="topic" id="topic" required>
                                <option value="" selected disabled>-- Chọn chủ đề --</option>
                                <option value="Tư vấn sản phẩm">Tư vấn sản phẩm</option>
                                <option value="Đổi trả/Bảo hành">Đổi trả / Bảo hành</option>
                                <option value="Vận chuyển">Vấn đề vận chuyển</option>
                                <option value="Khiếu nại">Góp ý / Khiếu nại</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label fw-bold">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="6" placeholder="Mô tả chi tiết vấn đề của bạn..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                GỬI YÊU CẦU NGAY <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>