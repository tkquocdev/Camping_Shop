<?php require_once ROOT_PATH . '/views/layouts/header.php'; ?>

<style>
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
    position: relative;
    overflow: hidden;
}
.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}
.about-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}
.about-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}
.team-member {
    text-align: center;
    margin-bottom: 30px;
}
.team-member img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #667eea;
    margin-bottom: 15px;
}
.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
}
</style>

<!-- Hero Section -->
<div class="about-hero">
    <div class="container text-center position-relative">
        <h1 class="display-4 fw-bold mb-4">Về Camping Shop</h1>
        <p class="lead fs-5 mb-0">Nơi mang đến những trải nghiệm dã ngoại tuyệt vời nhất cho mọi người</p>
    </div>
</div>

<div class="container py-5">
    <!-- Giới thiệu -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="mb-4">Câu chuyện của chúng tôi</h2>
            <p class="lead text-muted mb-4">
                Camping Shop được thành lập với sứ mệnh mang đến cho mọi người những sản phẩm chất lượng cao
                và trải nghiệm dã ngoại tuyệt vời. Chúng tôi tin rằng việc khám phá thiên nhiên không chỉ
                giúp thư giãn mà còn mang lại những kỷ niệm đáng nhớ.
            </p>
            <p class="text-muted">
                Với đội ngũ giàu kinh nghiệm và đam mê dã ngoại, chúng tôi cam kết cung cấp những sản phẩm
                tốt nhất từ các thương hiệu uy tín trên thế giới, cùng với dịch vụ tư vấn chuyên nghiệp.
            </p>
        </div>
    </div>

    <!-- Giá trị cốt lõi -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Giá trị cốt lõi</h2>
        </div>
        <div class="col-md-4 mb-4">
            <div class="about-card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">Chất lượng</h5>
                    <p class="card-text text-muted">
                        Chúng tôi chỉ cung cấp những sản phẩm chất lượng cao, được kiểm định kỹ lưỡng
                        trước khi đến tay khách hàng.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="about-card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-heart fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title">Đam mê</h5>
                    <p class="card-text text-muted">
                        Đội ngũ của chúng tôi đều là những người yêu thích dã ngoại và sẵn sàng chia sẻ
                        kinh nghiệm với bạn.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="about-card h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-handshake fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title">Tin cậy</h5>
                    <p class="card-text text-muted">
                        Sự tin cậy là nền tảng của chúng tôi. Chúng tôi cam kết mang đến dịch vụ tốt nhất
                        cho khách hàng.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Camping Shop trong số</h2>
        </div>
        <div class="col-md-3 mb-4 text-center">
            <div class="stats-number">10,000+</div>
            <p class="text-muted">Khách hàng hài lòng</p>
        </div>
        <div class="col-md-3 mb-4 text-center">
            <div class="stats-number">5,000+</div>
            <p class="text-muted">Sản phẩm chất lượng</p>
        </div>
        <div class="col-md-3 mb-4 text-center">
            <div class="stats-number">50+</div>
            <p class="text-muted">Thương hiệu uy tín</p>
        </div>
        <div class="col-md-3 mb-4 text-center">
            <div class="stats-number">24/7</div>
            <p class="text-muted">Hỗ trợ khách hàng</p>
        </div>
    </div>

    <!-- Đội ngũ -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Đội ngũ của chúng tôi</h2>
        </div>
        <div class="col-md-4">
            <div class="team-member">
                <img src="/assets/images/team1.jpg" alt="Team Member" onerror="this.src='https://via.placeholder.com/120x120/667eea/white?text=Team'">
                <h5>Nguyễn Văn A</h5>
                <p class="text-muted">Founder & CEO</p>
                <p class="small text-muted">10+ năm kinh nghiệm trong ngành dã ngoại</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="team-member">
                <img src="/assets/images/team2.jpg" alt="Team Member" onerror="this.src='https://via.placeholder.com/120x120/764ba2/white?text=Team'">
                <h5>Trần Thị B</h5>
                <p class="text-muted">Product Manager</p>
                <p class="small text-muted">Chuyên gia tư vấn sản phẩm dã ngoại</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="team-member">
                <img src="/assets/images/team3.jpg" alt="Team Member" onerror="this.src='https://via.placeholder.com/120x120/f093fb/white?text=Team'">
                <h5>Lê Văn C</h5>
                <p class="text-muted">Customer Service</p>
                <p class="small text-muted">Luôn sẵn sàng hỗ trợ khách hàng 24/7</p>
            </div>
        </div>
    </div>

    <!-- Cam kết -->
    <div class="row">
        <div class="col-12">
            <div class="about-card">
                <div class="card-body text-center p-5">
                    <h3 class="mb-4">Cam kết của chúng tôi</h3>
                    <p class="lead text-muted mb-4">
                        Chúng tôi cam kết mang đến cho bạn những sản phẩm tốt nhất với giá cả hợp lý
                        và dịch vụ chăm sóc khách hàng tận tâm.
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            <i class="fa-solid fa-truck fa-2x text-primary mb-2"></i>
                            <h6>Miễn phí vận chuyển</h6>
                            <p class="small text-muted">Đơn hàng từ 1.000.000đ</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fa-solid fa-undo fa-2x text-success mb-2"></i>
                            <h6>Đổi trả miễn phí</h6>
                            <p class="small text-muted">Trong 30 ngày</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fa-solid fa-headset fa-2x text-warning mb-2"></i>
                            <h6>Hỗ trợ 24/7</h6>
                            <p class="small text-muted">Luôn bên bạn</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layouts/footer.php'; ?>