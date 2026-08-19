<?php
// Lấy dữ liệu an toàn
$prizes = $prizes ?? [];
$has_played = $has_played ?? false;
?>

<style>
    /* --- 1. GAME TOGGLER (Nằm trên nút Chatbot) --- */
    .game-toggler {
        position: fixed;
        bottom: 95px; /* Chatbot là 28px + 54px cao + khoảng cách -> đặt tầm 95px */
        right: 32px;  /* Bằng right của Chatbot */
        width: 54px;
        height: 54px;
        border: none;
        border-radius: 50%;
        /* Gradient Vàng/Cam tạo cảm giác quà tặng/may mắn */
        background: linear-gradient(135deg, #f1c40f, #d35400); 
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 9990;
        box-shadow: 0 8px 20px rgba(211, 84, 0, 0.45);
        transition: transform .2s ease, box-shadow .2s ease;
        animation: game-shake 3s infinite;
    }
    .game-toggler:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(211, 84, 0, 0.55);
    }
    .game-toggler i {
        color: #fff;
        font-size: 24px;
    }

    @keyframes game-shake {
        0%, 100% { transform: rotate(0deg); }
        10%, 30% { transform: rotate(-10deg); }
        20%, 40% { transform: rotate(10deg); }
        50% { transform: rotate(0deg); }
    }

    /* --- 2. GAME CONTAINER (Cấu trúc y hệt Chatbot) --- */
    .game-widget {
        position: fixed;
        right: 32px;
        bottom: 160px; /* Cao hơn nút toggle game */
        width: 360px;
        max-width: calc(100vw - 24px);
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: scale(.85);
        transform-origin: bottom right;
        transition: all .18s ease;
        z-index: 9999;
        box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        border: 1px solid #f1c40f;
    }
    
    body.show-game .game-widget {
        opacity: 1;
        pointer-events: auto;
        transform: scale(1);
    }

    /* HEADER */
    .game-widget header {
        background: linear-gradient(135deg, #f1c40f, #d35400);
        padding: 12px 18px;
        color: #fff;
        font-weight: 700;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .game-widget header span {
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .game-widget header .close-btn {
        cursor: pointer;
        opacity: .85;
        font-size: 1.2rem;
        transition: opacity 0.2s;
    }
    .game-widget header .close-btn:hover { opacity: 1; }

    /* TABS */
    .game-tabs {
        display: flex;
        gap: 0;
        background: #f8f9fa;
        padding: 8px;
        border-bottom: 1px solid #e9ecef;
    }
    .game-tabs button {
        flex: 1;
        background: transparent;
        border: none;
        padding: 10px 12px;
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .game-tabs button.active {
        background: linear-gradient(135deg, #f1c40f, #d35400);
        color: white;
    }
    .game-tabs button:hover:not(.active) {
        background: #e9ecef;
    }

    /* BODY */
    .game-body {
        padding: 20px;
        background: #f5f7fb; /* Giống nền chatbox */
        min-height: 350px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    /* --- 3. VÒNG QUAY --- */
    .wheel-container {
        position: relative;
        width: 260px;
        height: 260px;
        margin-bottom: 20px;
        border-radius: 50%;
        border: 8px solid #fff;
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .wheel {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        position: relative;
        transition: transform 5s cubic-bezier(0.17, 0.67, 0.12, 0.99);
    }

    /* Text trên múi */
    .wheel-segment-text {
        position: absolute; top: 50%; left: 50%;
        transform-origin: 0 0;
        width: 50%; 
        padding-left: 20px; 
        box-sizing: border-box;
        display: flex; justify-content: flex-end; align-items: center; padding-right: 12px;
        font-size: 11px; font-weight: bold; color: #fff; 
        text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        pointer-events: none;
    }

    /* Kim chỉ */
    .wheel-pointer {
        position: absolute; top: -5px; left: 50%; transform: translateX(-50%);
        z-index: 50; color: #e74c3c; font-size: 32px;
        filter: drop-shadow(0 2px 2px rgba(0,0,0,0.2));
    }

    /* Nút Quay */
    .spin-btn {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        width: 60px; height: 60px; border-radius: 50%;
        background: #fff; border: 4px solid #e74c3c;
        color: #e74c3c; font-weight: 800; font-size: 13px;
        cursor: pointer; z-index: 60; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        transition: transform 0.1s;
    }
    .spin-btn:active { transform: translate(-50%, -50%) scale(0.95); }
    .spin-btn:disabled { border-color: #95a5a6; color: #95a5a6; cursor: not-allowed; }

    /* --- 4. RESULT OVERLAY (Hiện đè lên khi quay xong) --- */
    .result-layer {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.98);
        z-index: 100;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center;
        padding: 20px;
        opacity: 0; visibility: hidden; transition: opacity 0.3s;
    }
    .result-layer.active { opacity: 1; visibility: visible; }

    .coupon-box {
        background: #fdf2e9;
        border: 1px dashed #d35400;
        padding: 10px;
        border-radius: 8px;
        margin-top: 15px;
        width: 100%;
    }
    .coupon-input {
        width: 100%; background: transparent; border: none; 
        text-align: center; font-weight: bold; color: #d35400; font-size: 1.2rem; outline: none;
        margin-bottom: 8px;
    }
    .btn-copy {
        background: linear-gradient(135deg, #f1c40f, #d35400);
        color: white; border: none; padding: 6px 20px; border-radius: 20px;
        font-weight: 600; font-size: 0.9rem; cursor: pointer;
        box-shadow: 0 4px 10px rgba(211, 84, 0, 0.3);
        transition: transform 0.1s;
    }
    .btn-copy:active { transform: scale(0.95); }

    /* HISTORY STYLES */
    .game-history {
        display: none;
        max-height: 350px;
        overflow-y: auto;
        padding: 12px;
        background: #f5f7fb;
    }
    .game-history.active {
        display: block;
    }
    .history-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 0.9rem;
    }
    .history-item i {
        font-size: 1.5rem;
        color: #d35400;
        width: 30px;
        text-align: center;
    }
    .history-info {
        flex: 1;
    }
    .history-prize {
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }
    .history-date {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .history-empty {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
    .history-empty i {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 12px;
        display: block;
    }

</style>

<button class="game-toggler" onclick="toggleGame()">
    <i class="fa-solid fa-gift"></i>
</button>

<div class="game-widget" id="gameWidget">
    <header>
        <span><i class="fa-solid fa-star me-2"></i>Vòng Quay May Mắn</span>
        <span class="close-btn" onclick="toggleGame()">
            <i class="fa-solid fa-xmark"></i>
        </span>
    </header>

    <!-- TABS -->
    <div class="game-tabs">
        <button class="active" onclick="switchGameTab('spin')">
            <i class="fa-solid fa-dice me-1"></i>Quay
        </button>
        <button onclick="switchGameTab('history')">
            <i class="fa-solid fa-history me-1"></i>Lịch sử
        </button>
    </div>

    <!-- SPIN TAB -->
    <div class="game-body" id="gameSpinTab">
        
        <?php if($has_played): ?>
            <div class="text-center">
                <i class="fa-regular fa-calendar-check fa-4x text-muted mb-3"></i>
                <h5 class="fw-bold text-secondary">Hẹn gặp lại ngày mai!</h5>
                <p class="small text-muted mb-4">Bạn đã sử dụng hết lượt quay miễn phí hôm nay.</p>
                <button class="btn-copy" style="background: #95a5a6; box-shadow:none;" onclick="toggleGame()">Đóng lại</button>
            </div>

        <?php else: ?>
            <div class="wheel-container">
                <div class="wheel-pointer"><i class="fa-solid fa-caret-down"></i></div>
                
                <div class="wheel" id="gameWheel">
                    <?php if(!empty($prizes)): 
                        $count = count($prizes);
                        $deg = 360 / $count;
                        foreach($prizes as $i => $p):
                            $rotate = ($i * $deg) + ($deg / 2);
                    ?>
                        <div class="wheel-segment-text" 
                             style="transform: rotate(<?= $rotate - 90 ?>deg) translate(0, -50%); height: <?= $deg ?>px;">
                            <span style="display:inline-block; max-width: 80px; line-height: 1.1;">
                                <?= htmlspecialchars($p['name']) ?>
                            </span>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <button class="spin-btn" id="btnSpin">QUAY</button>
            </div>
            
            <p class="small text-muted fst-italic mb-0">
                <i class="fa-solid fa-circle-info me-1"></i>100% trúng thưởng Voucher
            </p>

            <div class="result-layer" id="resultLayer">
                <div class="mb-3">
                    <i class="fa-solid fa-trophy fa-3x text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark">CHÚC MỪNG!</h5>
                <p class="text-secondary small mb-1">Phần thưởng của bạn:</p>
                <h6 class="text-danger fw-bold text-uppercase fs-5" id="resPrizeName">...</h6>
                
                <div id="resCouponArea" class="d-none w-100">
                    <div class="coupon-box">
                        <p class="small text-muted mb-1">Mã giảm giá:</p>
                        <input type="text" id="resCouponCode" class="coupon-input" readonly>
                        <button class="btn-copy" onclick="copyCoupon()">
                            <i class="fa-regular fa-copy me-1"></i>COPY MÃ
                        </button>
                    </div>
                </div>
                
                <button class="btn btn-link text-muted text-decoration-none btn-sm mt-2" onclick="toggleGame()">Để sau</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- HISTORY TAB -->
    <div class="game-history" id="gameHistoryTab">
        <div id="historyContent">
            <!-- Loaded by JS -->
        </div>
    </div>
</div>

<script>
    // --- BIẾN DỮ LIỆU ---
    const prizesData = <?= json_encode($prizes ?? []) ?>;
    const gameWheel = document.getElementById('gameWheel');
    const btnSpin = document.getElementById('btnSpin');
    const resultLayer = document.getElementById('resultLayer');
    const resName = document.getElementById('resPrizeName');
    const resCodeInput = document.getElementById('resCouponCode');
    const resCouponArea = document.getElementById('resCouponArea');

    // --- TABS FUNCTIONS ---
    function switchGameTab(tab) {
        // Update active button
        document.querySelectorAll('.game-tabs button').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Hide/show tabs
        const spinTab = document.getElementById('gameSpinTab');
        const historyTab = document.getElementById('gameHistoryTab');

        if (tab === 'spin') {
            spinTab.style.display = 'flex';
            historyTab.style.display = 'none';
        } else {
            spinTab.style.display = 'none';
            historyTab.style.display = 'block';
            loadGameHistory();
        }
    }

    function loadGameHistory() {
        const historyContent = document.getElementById('historyContent');
        historyContent.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm" role="status"></div> <span class="ms-2">Đang tải...</span></div>';

        fetch('/game/history')
            .then(r => r.json())
            .then(res => {
                if (!res.status) {
                    historyContent.innerHTML = '<div class="history-empty"><i class="fa-regular fa-circle-xmark"></i><p>' + res.msg + '</p></div>';
                    return;
                }

                const history = res.data;
                if (history.length === 0) {
                    historyContent.innerHTML = '<div class="history-empty"><i class="fa-regular fa-calendar-blank"></i><p>Chưa có lịch sử quay</p></div>';
                    return;
                }

                let html = '';
                history.forEach(item => {
                    const date = new Date(item.created_at);
                    const dateStr = date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
                    const couponCode = item.coupon_code ? item.coupon_code : '(Không có mã)';
                    const couponId = 'coupon_' + Math.random().toString(36).substr(2, 9);
                    
                    html += `
                        <div class="history-item">
                            <i class="fa-solid fa-gift"></i>
                            <div class="history-info">
                                <div class="history-prize">${item.prize_name}</div>
                                <div class="history-date">${dateStr}</div>
                                ${item.coupon_code ? `<div style="margin-top: 8px; display: flex; align-items: center; gap: 8px;">
                                    <code id="${couponId}" style="background: #f0f0f0; padding: 4px 12px; border-radius: 4px; font-weight: bold; color: #d35400; flex: 1;">${couponCode}</code>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="copyCouponCode('${couponId}', '${couponCode}')">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
                });

                historyContent.innerHTML = html;
            })
            .catch(err => {
                console.error(err);
                historyContent.innerHTML = '<div class="history-empty"><i class="fa-regular fa-circle-xmark"></i><p>Lỗi khi tải dữ liệu</p></div>';
            });
    }

    // --- 1. HÀM TOGGLE UI ---
    function toggleGame() {
        document.body.classList.toggle('show-game');
        // Nếu mở game thì đóng chatbot (tránh rối mắt)
        if(document.body.classList.contains('show-game')){
            document.body.classList.remove('show-chatbot');
        }
    }

    // --- 2. VẼ MÀU VÒNG QUAY (CSS CONIC GRADIENT) ---
    if(gameWheel && prizesData.length > 0) {
        let gradients = [];
        const percent = 100 / prizesData.length;
        prizesData.forEach((p, i) => {
            // Sử dụng màu từ DB (ví dụ: #FF0000)
            const color = p.color || '#cccccc'; 
            gradients.push(`${color} ${i * percent}% ${(i + 1) * percent}%`);
        });
        gameWheel.style.background = `conic-gradient(${gradients.join(', ')})`;
    }

    // --- 3. XỬ LÝ QUAY ---
    if(btnSpin) {
        btnSpin.addEventListener('click', () => {
            if(btnSpin.disabled) return;

            // Gọi API
            fetch('/game/spin')
                .then(r => r.json())
                .then(res => {
                    if(!res.status) {
                        alert(res.msg);
                        if(res.msg.includes('đăng nhập')) window.location.href = '/auth/login';
                        return;
                    }

                    // Bắt đầu hiệu ứng
                    btnSpin.disabled = true;
                    btnSpin.innerText = "...";

                    // Tính toán góc quay
                    const count = prizesData.length;
                    const degPerItem = 360 / count;
                    const winIndex = res.data.index; // Index giải thưởng trúng (0, 1, 2...)

                    // Góc tâm của phần thưởng
                    const centerAngle = (winIndex * degPerItem) + (degPerItem / 2);
                    
                    // Thêm độ lệch ngẫu nhiên (Random Offset) để kim chỉ không bị cứng
                    // Lấy ngẫu nhiên từ -(nửa múi - 5 độ) đến +(nửa múi - 5 độ)
                    const offset = Math.floor(Math.random() * (degPerItem - 10)) - (degPerItem/2 - 5);

                    // Tổng góc quay = 5 vòng (1800 độ) + (360 - góc đích) + độ lệch
                    // (360 - centerAngle) để đảo chiều vì CSS rotate theo chiều kim đồng hồ
                    const finalDeg = 1800 + (360 - centerAngle) + offset;

                    gameWheel.style.transform = `rotate(${finalDeg}deg)`;

                    // Hiển thị kết quả sau 5s (khớp transition CSS)
                    setTimeout(() => {
                        showResult(res.data);
                        btnSpin.innerText = "XONG";
                    }, 5000);
                })
                .catch(err => {
                    console.error(err);
                    alert("Có lỗi kết nối, vui lòng thử lại!");
                    btnSpin.disabled = false;
                });
        });
    }

    function showResult(data) {
        resName.innerText = data.name;
        
        if(data.code) {
            resCouponArea.classList.remove('d-none');
            resCodeInput.value = data.code;
        } else {
            resCouponArea.classList.add('d-none');
        }
        
        resultLayer.classList.add('active');
    }

    // --- 4. COPY CODE ---
    function copyCoupon() {
        resCodeInput.select();
        resCodeInput.setSelectionRange(0, 99999); // Mobile support
        navigator.clipboard.writeText(resCodeInput.value);
        
        // Hiệu ứng nút bấm
        const btn = event.currentTarget;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>ĐÃ COPY';
        btn.style.background = '#27ae60';
        
        // Toast thông báo
        showToast('Đã sao chép mã voucher!', 'success');
        
        setTimeout(() => {
            btn.innerHTML = oldHtml;
            btn.style.background = 'linear-gradient(135deg, #f1c40f, #d35400)';
        }, 2000);
    }

    // Hàm copy mã voucher trong lịch sử
    function copyCouponCode(elementId, code) {
        const element = document.getElementById(elementId);
        const text = element.textContent || element.innerText;
        
        navigator.clipboard.writeText(code).then(() => {
            showToast('Đã sao chép mã: ' + code, 'success');
        }).catch(err => {
            console.error('Sao chép thất bại:', err);
            showToast('Không thể sao chép mã!', 'danger');
        });
    }

    // Hàm hiển thị toast thông báo
    function showToast(message, type = 'success') {
        const toastHtml = `
            <div style="position: fixed; top: 20px; right: 20px; z-index: 10000;">
                <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert" style="margin: 0; min-width: 300px;">
                    <i class="fa-solid fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        `;
        
        const container = document.createElement('div');
        container.innerHTML = toastHtml;
        document.body.appendChild(container);
        
        setTimeout(() => {
            container.remove();
        }, 3000);
    }
</script>