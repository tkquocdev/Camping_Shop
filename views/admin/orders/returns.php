<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu cầu Hoàn/Trả hàng - Camping Shop</title>
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
<body>
    <div class="sidebar">
        <?php $active = 'returns'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Quản lý Yêu cầu Hoàn/Trả hàng</h4>
        </div>

    <?php if(isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['flash_message']; unset($_SESSION['flash_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th class="ps-4">ID Yêu cầu</th>
                            <th>Khách hàng</th>
                            <th>Ngày gửi</th>
                            <th>Liên hệ</th>
                            <th>Nội dung</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($requests)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 text-secondary"></i><br>
                                    <span class="fw-bold">Không có yêu cầu nào đang chờ xử lý.</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#<?= $request['id'] ?></td>
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial rounded-circle bg-label-primary me-2">
                                            <i class="fa-solid fa-user text-secondary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($request['name'] ?? $request['customer_name'] ?? 'Khách vãng lai') ?></span>
                                            <?php if (!empty($request['email'] ?? $request['customer_email'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($request['email'] ?? $request['customer_email']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td><?= date('d/m/Y H:i', strtotime($request['created_at'])) ?></td>
                                
                                <td>
                                    <?php if (!empty($request['phone'])): ?>
                                        <i class="fa-solid fa-phone text-success me-1"></i><?= htmlspecialchars($request['phone']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <div class="text-muted small" style="max-width: 400px; word-wrap: break-word; white-space: normal;">
                                        <?= htmlspecialchars($request['content']) ?>
                                    </div>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <a href="/admin/customercare/ticket_detail/<?= $request['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary me-1" 
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <button class="btn btn-sm btn-outline-success me-1" 
                                            onclick="updateStatus(<?= $request['id'] ?>, 'Resolved')"
                                            title="Đã xử lý">
                                        <i class="fa-solid fa-check"></i> Xử lý
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="updateStatus(<?= $request['id'] ?>, 'Rejected')"
                                            title="Từ chối">
                                        <i class="fa-solid fa-xmark"></i> Từ chối
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(id, status) {
            const note = prompt(status === 'Resolved' ? 'Nhập ghi chú xử lý (tùy chọn):' : 'Lý do từ chối:');
            if (note === null) return; // User cancelled

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/orders/updateReturnStatus/${id}`;

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);

            const noteInput = document.createElement('input');
            noteInput.type = 'hidden';
            noteInput.name = 'note';
            noteInput.value = note;
            form.appendChild(noteInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>