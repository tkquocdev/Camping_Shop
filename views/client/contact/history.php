<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/" class="text-dark">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="/contact" class="text-dark">Liên hệ</a></li>
            <li class="breadcrumb-item active">Lịch sử hỗ trợ</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['flash_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lịch sử yêu cầu</h5>
            <a href="/contact" class="btn btn-sm btn-light text-primary fw-bold">
                <i class="fa-solid fa-plus"></i> Tạo mới
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Ngày gửi</th>
                            <th>Lý do / Yêu cầu</th> <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#<?= $item['ticket_id'] ?></td>
                                    
                                    <td><?= date('d/m/Y', strtotime($item['last_update'])) ?></td>
                                    
                                    <td style="max-width: 300px;">
                                        <div class="text-truncate" title="<?= htmlspecialchars($item['original_content']) ?>">
                                            <?= htmlspecialchars($item['original_content']) ?>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php 
                                            // Xử lý màu sắc trạng thái
                                            $stt = $item['current_status'];
                                            $badgeClass = 'bg-secondary';
                                            $sttLabel = $stt;

                                            if ($stt == 'Pending') { 
                                                $badgeClass = 'bg-warning text-dark'; $sttLabel = 'Đang chờ'; 
                                            }
                                            elseif ($stt == 'Processed') { 
                                                $badgeClass = 'bg-primary'; $sttLabel = 'Đang xử lý'; 
                                            }
                                            elseif ($stt == 'Hoàn thành' || $stt == 'Completed') { 
                                                $badgeClass = 'bg-success'; $sttLabel = 'Hoàn thành'; 
                                            }
                                            elseif ($stt == 'Cancelled' || $stt == 'Đã hủy') { 
                                                $badgeClass = 'bg-danger'; $sttLabel = 'Đã hủy'; 
                                            }
                                        ?>
                                        <span class="badge rounded-pill <?= $badgeClass ?>"><?= $sttLabel ?></span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <form action="/contact/delete" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa lịch sử này?');">
                                            <input type="hidden" name="ticket_id" value="<?= $item['latest_id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fa-solid fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fa-2x mb-3"></i>
                                    <p>Chưa có lịch sử hỗ trợ nào.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>