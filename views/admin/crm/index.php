<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chăm sóc Khách hàng - Camping Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { margin: 0; background-color: #f8f9fa; }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 280px;
            min-width: 280px;
            background-color: #343a40;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 300px;
            padding: 20px;
            min-height: 100vh;
        }
        /* Hiệu ứng nhấp nháy cho trạng thái Chờ xử lý */
        .blink_me { animation: blinker 1.5s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.5; } }
        
        /* Style cho Tabs */
        .nav-tabs .nav-link { color: #6c757d; font-weight: 500; }
        .nav-tabs .nav-link.active { color: #0d6efd; font-weight: bold; border-top: 3px solid #0d6efd; border-bottom-color: transparent; }
        
        /* Avatar nhân viên nhỏ */
        .avatar-xs { width: 24px; height: 24px; object-fit: cover; border-radius: 50%; }

        @media (max-width: 992px) {
            .sidebar {
                position: relative;
                height: auto;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
    <div class="sidebar">
        <?php $active = 'crm'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-headset me-2"></i> Trung tâm CSKH</h3>
                <p class="text-muted mb-0">Quản lý yêu cầu hỗ trợ và hồ sơ khách hàng</p>
            </div>
            </div>

        <ul class="nav nav-tabs mb-4 bg-white shadow-sm rounded-top px-3 pt-2" id="crmTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-3" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab">
                    <i class="fa-solid fa-ticket me-2"></i>Yêu cầu hỗ trợ 
                    <?php 
                        // Đếm số lượng ticket đang chờ xử lý
                        $pendingCount = 0;
                        if (!empty($data['tickets'])) {
                            foreach($data['tickets'] as $t) {
                                if($t['status'] == 'Pending') $pendingCount++;
                            }
                        }
                    ?>
                    <?php if($pendingCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab">
                    <i class="fa-solid fa-users me-2"></i>Danh sách khách hàng
                </button>
            </li>
        </ul>

        <div class="tab-content" id="crmTabsContent">
            
            <div class="tab-pane fade show active" id="tickets" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th class="ps-4">Mã Ticket</th>
                                        <th>Khách hàng</th>
                                        <th>Vấn đề / Nội dung</th>
                                        <th>Phụ trách</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['tickets'])): ?>
                                        <?php foreach ($data['tickets'] as $ticket): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-primary">
                                                    #<?= htmlspecialchars($ticket['ticket_id'] ?? $ticket['id']) ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="ms-0">
                                                            <h6 class="mb-0 text-dark"><?= htmlspecialchars($ticket['customer_name'] ?? 'Khách vãng lai') ?></h6>
                                                            <small class="text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars($ticket['customer_email']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 300px;">
                                                        <?= htmlspecialchars($ticket['content']) ?>
                                                    </div>
                                                    <span class="badge bg-light text-secondary border border-light">
                                                        <?= htmlspecialchars($ticket['interaction_type'] ?? 'Hỗ trợ') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($ticket['staff_name'])): ?>
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                                            <i class="fas fa-user-shield me-1"></i> <?= htmlspecialchars($ticket['staff_name']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted small fst-italic">-- Chưa phân công --</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $statusClass = match($ticket['status']) {
                                                            'Pending', 'Đang chờ' => 'bg-warning text-dark blink_me',
                                                            'Processing', 'Đang xử lý' => 'bg-primary',
                                                            'Completed', 'Processed', 'Hoàn thành' => 'bg-success',
                                                            'Cancelled', 'Hủy bỏ' => 'bg-secondary',
                                                            default => 'bg-light text-dark border'
                                                        };
                                                        
                                                        $statusLabel = match($ticket['status']) {
                                                            'Pending' => 'Chờ xử lý',
                                                            'Processing' => 'Đang xử lý',
                                                            'Completed', 'Processed' => 'Hoàn thành',
                                                            'Cancelled' => 'Đã hủy',
                                                            default => $ticket['status']
                                                        };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?> rounded-pill">
                                                        <?= $statusLabel ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= date('H:i d/m/Y', strtotime($ticket['created_at'])) ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="/admin/customercare/ticket_detail/<?= $ticket['id'] ?>" class="btn btn-outline-primary btn-sm" title="Xử lý yêu cầu">
                                                        <i class="fa-solid fa-wrench"></i> Xử lý
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i><br>
                                                <p class="text-muted mt-2">Hiện tại không có yêu cầu hỗ trợ nào.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="customers" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="mb-0 fw-bold">Danh sách hồ sơ khách hàng</h6>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" class="form-control" id="searchCustomer" placeholder="Tìm kiếm tên, email...">
                                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="customerTable">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Thông tin khách hàng</th>
                                        <th>Liên hệ</th>
                                        <th>Tương tác gần nhất</th>
                                        <th class="text-center">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(!empty($data['customers'])): ?>
                                        <?php foreach ($data['customers'] as $cus): ?>
                                            <tr>
                                                <td class="ps-4 text-muted">#<?= $cus['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-light text-primary d-flex justify-content-center align-items-center fw-bold me-3 border" style="width: 40px; height: 40px;">
                                                            <?= strtoupper(substr($cus['full_name'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark"><?= htmlspecialchars($cus['full_name']) ?></div>
                                                            <small class="text-muted">Thành viên</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-dark"><i class="far fa-envelope me-1 text-muted"></i> <?= htmlspecialchars($cus['email'] ?? 'N/A') ?></span>
                                                        <span class="text-muted small mt-1"><i class="fas fa-phone-alt me-1 text-muted"></i> <?= htmlspecialchars($cus['phone'] ?? '---') ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($cus['last_interaction']): ?>
                                                        <span class="badge bg-light text-primary border border-light">
                                                            <?= date('d/m/Y', strtotime($cus['last_interaction'])) ?>
                                                        </span>
                                                        <div class="small text-muted mt-1">
                                                            <?= date('H:i', strtotime($cus['last_interaction'])) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-secondary">Chưa tương tác</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="/admin/customercare/customer/<?= $cus['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem hồ sơ">
                                                        <i class="fas fa-user-circle me-1"></i> Hồ sơ
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-5">Chưa có dữ liệu khách hàng</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div> 
    </div>
</div>

<script>
    // Script tìm kiếm đơn giản cho bảng khách hàng (Client-side)
    document.getElementById('searchCustomer').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#customerTable tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>