<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhập kho - Camping Shop</title>
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
        <?php $active = 'stock'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Quản lý Nhập kho</h4>
            <a href="/admin/stock/create" class="btn btn-primary fw-bold">
                <i class="fa-solid fa-plus me-2"></i> Nhập hàng mới
            </a>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <?php if($_GET['msg'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check-circle me-1"></i> Nhập kho thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-trash-can me-1"></i> Đã xóa phiếu nhập và trừ lại tồn kho thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif($_GET['msg'] == 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Có lỗi xảy ra, vui lòng thử lại!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nhà cung cấp</th>
                                <th>Người nhập</th>
                                <th>Tổng tiền</th>
                                <th>Ngày nhập</th>
                                <th>Ghi chú</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['imports'])): ?>
                                <?php foreach ($data['imports'] as $item): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $item['id'] ?></span></td>
                                    
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle text-center me-2" style="width: 30px; height: 30px; line-height: 30px;">
                                                <i class="fa-regular fa-user text-muted small"></i>
                                            </div>
                                            <?= htmlspecialchars($item['user_name'] ?? 'Admin') ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-success fw-bold">
                                        <?= number_format($item['total_amount'], 0, ',', '.') ?> ₫
                                    </td>
                                    
                                    <td>
                                        <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                        <small class="text-muted d-block" style="font-size: 11px;">
                                            <?= date('H:i', strtotime($item['created_at'])) ?>
                                        </small>
                                    </td>
                                    
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($item['note'] ?? '-') ?>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <a href="/admin/stock/print/<?= $item['id'] ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary me-2" 
                                           title="In Phiếu Nhập">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                        
                                        <a href="/admin/stock/delete/<?= $item['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           title="Xóa Phiếu"
                                           onclick="return confirm('⚠️ CẢNH BÁO QUAN TRỌNG:\n\nBạn có chắc chắn muốn xóa Phiếu Nhập #<?= $item['id'] ?>?\n\nHệ thống sẽ:\n1. Xóa phiếu vĩnh viễn.\n2. TRỪ LẠI số lượng hàng hóa trong kho (Hoàn tác tồn kho).\n\nHành động này không thể phục hồi!')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary"></i><br>
                                        <span class="fs-5">Chưa có phiếu nhập hàng nào.</span><br>
                                        <a href="/admin/stock/create" class="btn btn-sm btn-link mt-2">Tạo phiếu mới ngay</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>