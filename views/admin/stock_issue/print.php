<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phieu_Xuat_Kho_#<?= $issue['id'] ?? '---' ?></title>
    <style>
        /* Font chữ */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url(https://fonts.gstatic.com/s/dejavusans/v1/xpoc12paP9SSln1QLU1gR7O4HLCP.ttf) format('truetype');
        }
        
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 13px; 
            color: #000; 
            line-height: 1.5; 
            margin: 0; 
            background-color: #525659; /* Màu nền xám giống trình duyệt xem PDF */
            padding: 20px;
        }

        /* Giả lập tờ giấy A4 */
        .a4-paper {
            background: white;
            width: 210mm; /* Khổ A4 */
            min-height: 297mm; /* Khổ A4 */
            margin: 0 auto; /* Canh giữa màn hình */
            padding: 20mm; /* Lề giấy */
            box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Tạo bóng đổ cho giống giấy thật */
            position: relative;
            box-sizing: border-box;
        }
        
        /* Ẩn nút khi in */
        @media print {
            body { 
                background: none; 
                padding: 0; 
            }
            .no-print { display: none; }
            .a4-paper {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                min-height: auto;
            }
            /* Ép trang in khổ A4 */
            @page {
                size: A4;
                margin: 20mm;
            }
        }

        /* Nút bấm */
        .toolbar {
            width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            gap: 10px;
        }
        .btn-print {
            padding: 10px 20px; background: #0d6efd; color: white; border: none; cursor: pointer; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px;
        }
        .btn-back {
            background: #6c757d;
        }
        .btn-print:hover { opacity: 0.9; color: white; }

        /* Các style định dạng trang */
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #333; }
        .company-info { float: right; text-align: right; font-size: 12px; }
        
        .title { text-align: center; font-size: 22px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; margin-top: 10px;}
        .sub-title { text-align: center; font-style: italic; margin-bottom: 30px; font-size: 12px; }
        
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .product-table th, .product-table td { border: 1px solid #444; padding: 8px; text-align: left; font-size: 12px; }
        .product-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; text-align: center; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .total-section { width: 100%; text-align: right; margin-bottom: 40px; }
        
        .signature-table { width: 100%; text-align: center; margin-top: 50px; }
        .signature-table td { padding-bottom: 80px; font-weight: bold; font-size: 12px;}
        
        .footer { margin-top: 20px; text-align: center; font-style: italic; font-size: 11px; border-top: 1px solid #ddd; padding-top: 10px;}
        
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body> 
    <div class="no-print toolbar">
        <a href="/admin/StockIssue" class="btn-print btn-back">&larr; Quay lại</a>
        <button onclick="window.print()" class="btn-print">🖨️ In Phiếu</button>
    </div>

    <div class="a4-paper">

        <div class="header clearfix">
            <div style="float: left;">
                <div class="logo">Camping Shop</div>
                <div>Thới An Hội, Kế Sách, Sóc Trăng</div>
                <div>Hotline: 0868.285.284</div>
            </div>
            <div class="company-info">
                <strong>SỐ: #<?= $issue['id'] ?? '---' ?></strong><br>
                Ngày: <?= isset($issue['created_at']) ? date('d/m/Y', strtotime($issue['created_at'])) : date('d/m/Y') ?><br>
                NV: <?= htmlspecialchars($issue['user_name'] ?? 'Admin') ?>
            </div>
        </div>

        <div class="title">PHIẾU XUẤT KHO</div>
        <div class="sub-title">(Liên 1: Lưu tại kho / Giao khách hàng)</div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <strong>ĐƠN VỊ XUẤT:</strong><br>
                    <span class="fw-bold" style="font-size: 14px;">KHO CAMPING SHOP</span><br>
                    SĐT: 0868.285.284<br>
                    Đ/c: Thới An Hội, Kế Sách, Sóc Trăng
                </td>
                <td width="40%">
                    <strong>LÝ DO XUẤT / GHI CHÚ:</strong><br>
                    <?= htmlspecialchars($issue['note'] ?: 'Xuất bán lẻ / Xuất nội bộ') ?>
                </td>
            </tr>
        </table>

        <table class="product-table">
            <thead>
                <tr>
                    <th width="5%">STT</th>
                    <th>Tên hàng hóa / Quy cách</th>
                    <th width="10%">ĐVT</th>
                    <th width="10%">SL</th>
                    <th width="15%">Đơn giá</th>
                    <th width="20%">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                $grandTotal = 0;
                // Kiểm tra biến $details thay vì $items
                if(!empty($details)):
                    foreach($details as $item): 
                        // Tính thành tiền (SL * Giá)
                        $lineTotal = $item['quantity'] * $item['price'];
                        $grandTotal += $lineTotal;
                ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td><?= htmlspecialchars($item['name'] ?? $item['product_name'] ?? '-') ?></td>
                    <td class="text-center">Cái</td>
                    <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                    <td class="text-right"><?= number_format($item['price'], 0, ',', '.') ?></td>
                    <td class="text-right fw-bold"><?= number_format($lineTotal, 0, ',', '.') ?></td>
                </tr>
                <?php 
                    endforeach; 
                else:
                ?>
                <tr>
                    <td colspan="6" class="text-center">Không có dữ liệu hàng hóa</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="total-section">
            <table style="width: 50%; float: right; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border-top: 2px solid #000; font-size: 13px;"><strong>TỔNG CỘNG:</strong></td>
                    <td class="text-right" style="padding: 10px; border-top: 2px solid #000; font-size: 16px; color: #000;">
                        <strong><?= number_format($grandTotal, 0, ',', '.') ?> VNĐ</strong>
                    </td>
                </tr>
            </table>
        </div>

        <div class="clearfix"></div>

        <table class="signature-table">
            <tr>
                <td width="25%">Người lập phiếu<br><small>(Ký, họ tên)</small></td>
                <td width="25%">Người nhận hàng<br><small>(Ký, họ tên)</small></td>
                <td width="25%">Thủ kho<br><small>(Ký, họ tên)</small></td>
                <td width="25%">Giám đốc<br><small>(Ký, họ tên)</small></td>
            </tr>
        </table>

        <div class="footer">
            <p>Phiếu xuất kho này có giá trị lưu hành nội bộ.</p>
            <small>Ngày in: <?= date('d/m/Y H:i') ?></small>
        </div>

    </div> 
</body>
</html>