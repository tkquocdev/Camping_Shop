<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary mb-0">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch sử yêu cầu hỗ trợ
        </h4>
        <a href="/contact" class="btn btn-primary shadow-sm">
            <i class="fa-solid fa-pen-to-square me-2"></i>Gửi yêu cầu mới
        </a>
    </div>
    <div class="card shadow border-0 rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="py-3 ps-4">Ticket ID</th>
                            <th>Ngày cập nhật</th>
                            <th>Vấn đề / Yêu cầu</th>
                            <th class="text-center">Trạng thái</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $item): ?>
                                <?php 
                                $stt = $item['current_status'] ?? 'Pending';
                                
                                // MAPPING TRẠNG THÁI (Giữ nguyên logic bạn đã sửa)
                                $statusMap = [
                                    'Pending'   => ['label' => 'Đang chờ',   'class' => 'bg-warning text-dark'],
                                    'Processed' => ['label' => 'Hoàn thành', 'class' => 'bg-success'], 
                                    'Completed' => ['label' => 'Hoàn thành', 'class' => 'bg-success'],
                                    'Hoàn thành'=> ['label' => 'Hoàn thành', 'class' => 'bg-success'],
                                    'Cancelled' => ['label' => 'Đã hủy',     'class' => 'bg-danger'],
                                    'Đã hủy'    => ['label' => 'Đã hủy',     'class' => 'bg-danger'],
                                ];

                                $statusInfo = $statusMap[$stt] ?? ['label' => $stt, 'class' => 'bg-secondary'];
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#<?= $item['ticket_id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($item['last_update'])) ?></td>
                                    
                                    <td style="max-width: 350px;">
                                        <div class="d-flex align-items-center">
                                            <div class="text-truncate fw-medium" title="<?= htmlspecialchars($item['original_content']) ?>">
                                                <?= htmlspecialchars($item['original_content']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge rounded-pill <?= $statusInfo['class'] ?> px-3 py-2">
                                            <?= $statusInfo['label'] ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-info me-2" data-bs-toggle="modal" data-bs-target="#ticketModal<?= $item['ticket_id'] ?>">
                                            <i class="fa-solid fa-eye"></i> Xem
                                        </button>

                                        <form action="/contact/delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn muốn xóa lịch sử này?');">
                                            <input type="hidden" name="ticket_id" value="<?= $item['latest_id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="ticketModal<?= $item['ticket_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title text-primary">
                                                    Chi tiết Ticket #<?= $item['ticket_id'] ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body bg-light">
                                                <div class="timeline p-2">
                                                    <?php 
                                                        $details = $model->getTicketDetail($item['ticket_id']); 
                                                    ?>
                                                    <?php if(!empty($details)): foreach($details as $log): ?>
                                                        <div class="card mb-3 border-0 shadow-sm">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between text-muted small mb-2">
                                                                    <span><i class="fa-regular fa-clock me-1"></i><?= date('H:i d/m/Y', strtotime($log['created_at'])) ?></span>
                                                                    <span class="fw-bold text-uppercase"><?= $log['interaction_type'] ?? 'Hệ thống' ?></span>
                                                                </div>
                                                                <p class="mb-1 text-dark"><?= nl2br(htmlspecialchars($log['content'])) ?></p>
                                                                <?php if($log['status']): ?>
                                                                    <div class="mt-2 pt-2 border-top">
                                                                        <span class="badge bg-light text-dark border">Trạng thái: <?= $log['status'] ?></span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; endif; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <p class="text-muted">Chưa có dữ liệu lịch sử.</p>
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