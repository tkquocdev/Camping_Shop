<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Xuất kho - Camping Shop</title>
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
        <?php $active = 'stock_issue'; include ROOT_PATH . '/views/admin/layouts/sidebar.php'; ?>
    </div>
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Quản lý Xuất kho</h4>
            <a href="/admin/StockIssue/create" class="btn btn-primary fw-bold">
                <i class="fa-solid fa-plus me-2"></i> Tạo phiếu xuất
            </a>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <?php if($_GET['msg'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-check-circle me-1"></i> Xuất kho thành công!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            
            <?php elseif($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-trash-can me-1"></i> Đã xóa phiếu và hoàn trả tồn kho thành công!
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
                                <th>Người tạo phiếu</th>
                                <th>Tổng giá trị</th>
                                <th>Ngày xuất</th>
                                <th>Lý do / Ghi chú</th>
                                <th class="text-end pe-4">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($issues)): ?>
                                <?php foreach ($issues as $item): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $item['id'] ?></span></td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2 text-secondary">
                                                <i class="fa-regular fa-user"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold"><?= htmlspecialchars($item['user_name'] ?? 'System Admin') ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-danger fw-bold">
                                        <?= number_format($item['total_amount'], 0, ',', '.') ?> ₫
                                    </td>
                                    
                                    <td>
                                        <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                        <small class="text-muted d-block"><?= date('H:i', strtotime($item['created_at'])) ?></small>
                                    </td>
                                    
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($item['note'] ?? '-') ?>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <a href="/admin/StockIssue/print/<?= $item['id'] ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary me-2" 
                                           title="In Phiếu">
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        <a href="/admin/StockIssue/delete/<?= $item['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           title="Xóa phiếu"
                                           onclick="return confirm('⚠️ CẢNH BÁO QUAN TRỌNG:\n\nBạn có chắc chắn muốn xóa phiếu xuất #<?= $item['id'] ?>?\n\nHệ thống sẽ:\n1. Xóa phiếu này vĩnh viễn.\n2. HOÀN TRẢ lại số lượng hàng hóa vào kho.')">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-box-open fa-3x mb-3"></i><br>
                                        <p>Chưa có phiếu xuất kho nào.</p>
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