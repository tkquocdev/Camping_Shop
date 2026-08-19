<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Khách hàng - Camping Shop Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 300px; background-color: #343a40; overflow-y: auto; }
        .main-content { margin-left: 300px; padding: 20px; min-height: 100vh; }
        @media (max-width: 992px) { .sidebar { position: relative; width: 100%; height: auto; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php $active = 'crm'; require_once __DIR__ . '/../layouts/sidebar.php'; ?>
    </div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<style>
    /* Timeline Style */
    .timeline-container { position: relative; padding-left: 20px; }
    .timeline-item { position: relative; padding-bottom: 1.5rem; border-left: 2px solid #e9ecef; padding-left: 20px; }
    .timeline-item::before { content: ""; position: absolute; left: -9px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 2px solid #0d6efd; }
    .timeline-item:last-child { border-left: 0; }
    
    /* Bong bóng chat - Phân biệt Customer vs Staff */
    .bubble { position: relative; padding: 15px; border-radius: 10px; font-size: 0.95rem; }
    .bubble-customer { background-color: #e7f1ff; border: 1px solid #cce5ff; color: #004085; }
    .bubble-customer::after { content: "Khách hàng"; display: block; font-size: 0.75rem; font-weight: bold; color: #0d6efd; margin-top: 5px; text-transform: uppercase; }
    
    .bubble-staff { background-color: #f8f9fa; border: 1px solid #dee2e6; color: #343a40; }
    .bubble-staff::after { content: "Nhân viên CSKH"; display: block; font-size: 0.75rem; font-weight: bold; color: #6c757d; margin-top: 5px; text-transform: uppercase; }

    /* Highlight Ticket đang xử lý */
    .active-ticket-card { border: 2px solid #ffc107; background-color: #fffbf0; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2); }
    
    /* Animation */
    .blink_me { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0.6; } }

    /* Loading Overlay */
    .loading-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.8); display: none;
        align-items: center; justify-content: center; z-index: 50;
        border-radius: inherit;
    }
</style>

<div class="d-flex">
    <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-content w-100 p-4 bg-light">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="/admin/customercare" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            
            <?php if(isset($data['current_ticket']) && !empty($data['current_ticket'])): ?>
                <span class="badge bg-warning text-dark fs-6 shadow-sm px-3 py-2">
                    <i class="fa-solid fa-ticket me-1"></i> Đang xử lý Ticket #<?= $data['current_ticket']['id'] ?>
                </span>
            <?php else: ?>
                <span class="badge bg-primary fs-6 shadow-sm px-3 py-2">
                    <i class="fa-solid fa-address-card me-1"></i> Hồ sơ khách hàng
                </span>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i>
                <?php 
                    $msgs = [
                        'added' => 'Đã thêm ghi chú chăm sóc thành công!',
                        'updated' => 'Đã cập nhật trạng thái yêu cầu thành công!',
                        'info_updated' => 'Đã cập nhật thông tin khách hàng thành công!'
                    ];
                    echo $msgs[$_GET['msg']] ?? 'Thao tác thành công!';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm border-0 position-sticky" style="top: 20px; z-index: 10;">
                    <div class="card-body text-center pt-5 pb-4">
                        <div class="bg-primary bg-gradient text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 90px; height: 90px; font-size: 36px;">
                            <?= strtoupper(substr($data['customer']['full_name'], 0, 1)) ?>
                        </div>
                        
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($data['customer']['full_name']) ?></h5>
                        <p class="text-muted mb-3 small"><i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($data['customer']['email'] ?? 'N/A') ?></p>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3 rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editInfoModal">
                            <i class="fa-solid fa-pen me-1"></i> Cập nhật thông tin
                        </button>

                        <hr class="my-3 opacity-25">
                        
                        <div class="text-start px-2">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light p-2 rounded-circle me-3"><i class="fa-solid fa-phone text-success"></i></div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Số điện thoại</small>
                                    <?php if (!empty($data['customer']['phone'])): ?>
                                        <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($data['customer']['phone']) ?></span>
                                    <?php else: ?>
                                        <span class="text-danger fst-italic small">Chưa cập nhật</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light p-2 rounded-circle me-3"><i class="fa-solid fa-location-dot text-danger"></i></div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Địa chỉ</small>
                                    <span class="text-dark small d-block" style="line-height: 1.2;">
                                        <?= htmlspecialchars($data['customer']['address'] ?? 'Chưa có địa chỉ') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-3"><i class="fa-regular fa-calendar text-primary"></i></div>
                                <div>
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Ngày tham gia</small>
                                    <span class="text-dark small"><?= date('d/m/Y', strtotime($data['customer']['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($data['current_ticket'])): ?>
                        <div class="card-footer bg-warning bg-opacity-10 border-top border-warning p-3">
                            <h6 class="text-warning-emphasis fw-bold small text-uppercase mb-2">
                                <i class="fa-solid fa-circle-info me-1"></i> Yêu cầu hiện tại
                            </h6>
                            <div class="bg-white p-2 rounded border border-warning-subtle small fst-italic text-secondary">
                                "<?= htmlspecialchars($data['current_ticket']['content']) ?>"
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-8">
                
                <?php if (isset($data['current_ticket']) && $data['current_ticket']): ?>
                    <div class="card mb-4 active-ticket-card position-relative">
                        <div class="loading-overlay" id="ticketLoading">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <div class="card-header bg-warning border-warning fw-bold text-dark d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-gavel me-2"></i>XỬ LÝ YÊU CẦU #<?= $data['current_ticket']['id'] ?></span>
                            <span class="badge bg-white text-dark"><?= $data['current_ticket']['status'] ?></span>
                        </div>
                        <div class="card-body">
                            <form id="mainTicketForm">
                                <input type="hidden" name="id" value="<?= $data['current_ticket']['id'] ?>">

                                <div class="alert alert-light border-warning border-start border-4 small mb-3">
                                    <i class="fa-solid fa-lightbulb text-warning me-1"></i> 
                                    Vui lòng cập nhật trạng thái sau khi đã liên hệ hoặc giải quyết vấn đề cho khách hàng.
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Trạng thái xử lý:</label>
                                        <select name="status" class="form-select border-primary">
                                            <option value="Processed" class="text-success fw-bold">✅ Hoàn thành (Đã xong)</option>
                                            <option value="Pending" class="text-warning fw-bold" selected>⏳ Đang xử lý (Giữ nguyên)</option>
                                            <option value="Cancelled" class="text-danger fw-bold">❌ Hủy bỏ (Không thể xử lý)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Ghi chú nội bộ:</label>
                                        <input type="text" name="note" class="form-control" placeholder="VD: Đã gọi khách, khách đồng ý...">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary fw-bold px-4">
                                            <i class="fa-solid fa-save me-1"></i> Cập nhật Ticket
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Thêm ghi chú tương tác mới</h6>
                    </div>
                    <div class="card-body bg-light">
                        <form action="/admin/customercare/store/<?= $data['customer']['id'] ?>" method="POST">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <select name="type" class="form-select form-select-sm">
                                        <option value="Tư vấn">📞 Gọi điện tư vấn</option>
                                        <option value="Khiếu nại">⚠️ Xử lý khiếu nại</option>
                                        <option value="Gặp mặt">🤝 Gặp trực tiếp</option>
                                        <option value="Khác">📝 Ghi chú khác</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Hoàn thành">✅ Thành công / Nghe máy</option>
                                        <option value="Đang xử lý">⏳ Cần gọi lại</option>
                                        <option value="Thất bại">❌ Thuê bao / Thất bại</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <textarea name="content" class="form-control mt-2" rows="2" placeholder="Nhập nội dung chi tiết cuộc gọi/ghi chú..." required></textarea>
                                </div>
                                
                                <?php if(isset($data['current_ticket'])): ?>
                                    <input type="hidden" name="ticket_id" value="<?= $data['current_ticket']['id'] ?>">
                                <?php endif; ?>

                                <div class="col-12 text-end mt-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Lưu ghi chú
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i>Lịch sử hoạt động</h6>
                        <span class="badge bg-light text-dark border">Total: <?= count($data['logs']) ?></span>
                    </div>
                    <div class="card-body" style="max-height: 800px; overflow-y: auto;">
                        <?php if (!empty($data['logs'])): ?>
                            <div class="timeline-container">
                                <?php foreach ($data['logs'] as $log): 
                                    // Xác định loại log: Ticket (của khách) hay Note (của nhân viên)
                                    // Giả sử logic: Nếu không có staff_id -> Khách hàng. Nếu có staff_id -> Nhân viên
                                    $isCustomer = empty($log['staff_id']); 
                                    $isTicket = !empty($log['status']) && ($log['type'] == 'Ticket' || isset($log['ticket_id'])); // Logic tùy chỉnh theo DB của bạn
                                    
                                    // Nếu log có status là Pending/Processed... thì khả năng cao là Ticket hoặc Interaction có trạng thái
                                ?>
                                    <div class="timeline-item pb-4">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle" style="font-size: 0.75rem;">
                                                <?= date('H:i d/m/Y', strtotime($log['created_at'])) ?>
                                            </span>
                                            
                                            <?php 
                                                // Badge trạng thái
                                                $badgeClass = match($log['status']) {
                                                    'Pending', 'Đang chờ' => 'bg-warning text-dark blink_me',
                                                    'Processed', 'Hoàn thành' => 'bg-success',
                                                    'Cancelled', 'Thất bại' => 'bg-danger',
                                                    default => 'bg-info text-dark'
                                                };
                                            ?>
                                            <?php if($log['status']): ?>
                                                <span class="badge <?= $badgeClass ?> rounded-pill" style="font-size: 0.7rem;"><?= $log['status'] ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="bubble <?= $isCustomer ? 'bubble-customer' : 'bubble-staff' ?> shadow-sm">
                                            <?php if($isCustomer): ?>
                                                <i class="fa-solid fa-ticket me-1"></i> <strong>Yêu cầu hỗ trợ:</strong>
                                            <?php else: ?>
                                                <i class="fa-solid fa-user-headset me-1"></i> <strong>Ghi chú / Tương tác:</strong>
                                            <?php endif; ?>
                                            
                                            <div class="mt-2" style="white-space: pre-line;"><?= htmlspecialchars($log['content']) ?></div>
                                            
                                            <?php if ($isCustomer && isset($log['id'])): ?>
                                                <div class="mt-2 pt-2 border-top border-primary border-opacity-10 text-end">
                                                    <button class="btn btn-link btn-sm text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#modalLog<?= $log['id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i> Cập nhật
                                                    </button>
                                                </div>
                                                
                                                <div class="modal fade" id="modalLog<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                        <div class="modal-content">
                                                            <form class="ajax-form">
                                                                <div class="modal-header bg-light py-2">
                                                                    <h6 class="modal-title fw-bold">Cập nhật #<?= $log['id'] ?></h6>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="id" value="<?= $log['id'] ?>">
                                                                    <label class="form-label small fw-bold">Trạng thái mới:</label>
                                                                    <select name="status" class="form-select form-select-sm mb-2">
                                                                        <option value="Processed">✅ Đã xong</option>
                                                                        <option value="Pending">⏳ Đang chờ</option>
                                                                        <option value="Cancelled">❌ Hủy bỏ</option>
                                                                    </select>
                                                                    <label class="form-label small fw-bold">Ghi chú:</label>
                                                                    <textarea name="note" class="form-control form-control-sm" rows="2"></textarea>
                                                                </div>
                                                                <div class="modal-footer py-1">
                                                                    <button type="submit" class="btn btn-primary btn-sm w-100">Lưu</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <img src="/public/assets/images/no-data.png" alt="" style="width: 60px; opacity: 0.3;">
                                <p class="mt-2 small">Chưa có lịch sử tương tác nào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editInfoModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/admin/customercare/update_info/<?= $data['customer']['id'] ?>" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Cập nhật hồ sơ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['customer']['phone'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ giao hàng</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($data['customer']['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script>
$(document).ready(function() {
    
    // Hàm xử lý AJAX cập nhật Ticket
    function updateTicket(form, isModal = false) {
        var btn = form.find('button[type="submit"]');
        var originalText = btn.html();
        var overlay = isModal ? null : $('#ticketLoading');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        if(overlay) overlay.css('display', 'flex');

        $.ajax({
            url: '/admin/customercare/updateTicket', // Router xử lý update
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('Cập nhật thành công!');
                    location.reload(); 
                } else {
                    alert('Lỗi: ' + res.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối server!');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
                if(overlay) overlay.hide();
            }
        });
    }

    // Submit form xử lý chính
    $('#mainTicketForm').on('submit', function(e) {
        e.preventDefault();
        updateTicket($(this));
    });

    // Submit form trong modal (Lịch sử)
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        updateTicket($(this), true);
    });

});
</script>