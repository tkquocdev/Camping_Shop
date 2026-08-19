<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phieu_Nhap_Kho_#<?= $import['id'] ?></title>
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

        /* Các style cũ giữ nguyên nhưng chỉnh lại một chút cho đẹp */
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
<body> <div class="no-print toolbar">
        <a href="/admin/stock" class="btn-print btn-back">&larr; Quay lại</a>
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
                <strong>SỐ: #<?= $import['id'] ?></strong><br>
                Ngày: <?= date('d/m/Y', strtotime($import['created_at'])) ?><br>
                NV: <?= htmlspecialchars($import['user_name'] ?? 'Admin') ?>
            </div>
        </div>

        <div class="title">PHIẾU NHẬP KHO</div>
        <div class="sub-title">(Liên 1: Lưu tại kho)</div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <strong>NHÀ CUNG CẤP:</strong><br>
                    <span class="fw-bold" style="font-size: 14px;"><?= htmlspecialchars($import['supplier_name']) ?></span><br>
                    SĐT: <?= htmlspecialchars($import['supplier_phone'] ?? '---') ?><br>
                    Đ/c: <?= htmlspecialchars($import['supplier_address'] ?? '---') ?>
                </td>
                <td width="40%">
                    <strong>GHI CHÚ:</strong><br>
                    <?= htmlspecialchars($import['note'] ?: 'Không có ghi chú') ?>
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
                if(!empty($items)):
                    foreach($items as $item): 
                        $grandTotal += $item['total'];
                ?>
                <tr>
                    <td class="text-center"><?= $stt++ ?></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td class="text-center">Cái</td>
                    <td class="text-center fw-bold"><?= $item['quantity'] ?></td>
                    <td class="text-right"><?= number_format($item['import_price'], 0, ',', '.') ?></td>
                    <td class="text-right fw-bold"><?= number_format($item['total'], 0, ',', '.') ?></td>
                </tr>
                <?php 
                    endforeach; 
                endif;
                ?>
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
                <td width="25%">Người giao hàng<br><small>(Ký, họ tên)</small></td>
                <td width="25%">Thủ kho<br><small>(Ký, họ tên)</small></td>
                <td width="25%">Giám đốc<br><small>(Ký, họ tên)</small></td>
            </tr>
        </table>

        <div class="footer">
            <p>Phiếu nhập kho này có giá trị lưu hành nội bộ.</p>
            <small>Ngày in: <?= date('d/m/Y H:i') ?></small>
        </div>

    </div> </body>
</html>
