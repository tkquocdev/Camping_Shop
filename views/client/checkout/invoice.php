<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn #<?= $order['id'] ?></title>
    <style>
        /* Cấu hình Font chữ để không lỗi tiếng Việt */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: normal;
            src: url(https://fonts.gstatic.com/s/dejavusans/v1/xpoc12paP9SSln1QLU1gR7O4HLCP.ttf) format('truetype');
        }
        
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #000; line-height: 1.4; }
        
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .company-info { float: right; text-align: right; }
        
        .title { text-align: center; font-size: 20px; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        
        .product-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .product-table th, .product-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .product-table th { background-color: #f0f0f0; font-weight: bold; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .total-section { width: 100%; text-align: right; }
        .footer { margin-top: 50px; text-align: center; font-style: italic; font-size: 10px; }
        
        /* Helper để clear float */
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div class="logo">CAMPING SHOP</div>
                    <div>Thới An Hội, Kế Sách, Sóc Trăng</div>
                    <div>Hotline: 0868.285.284</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <strong>MÃ ĐƠN: #<?= $order['id'] ?></strong><br>
                    Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?><br>
                    Trạng thái: <?= strtoupper($order['status']) ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">HÓA ĐƠN BÁN LẺ</div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>THÔNG TIN KHÁCH HÀNG:</strong><br>
                Họ tên: <?= htmlspecialchars($customerName ?? $_SESSION['user']['name'] ?? 'N/A') ?><br> Số điện thoại: <?= $order['phone'] ?><br>
                Địa chỉ: <?= $order['shipping_address'] ?>
            </td>
            <td width="50%">
                <strong>GHI CHÚ:</strong><br>
                <?= $order['note'] ?: 'Không có ghi chú' ?><br>
                <br>
                <strong>THANH TOÁN:</strong> <?= strtoupper($order['payment_method']) ?>
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th>Tên sản phẩm</th>
                <th width="15%" class="text-center">Số lượng</th>
                <th width="20%" class="text-right">Đơn giá</th>
                <th width="20%" class="text-right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $stt = 1;
            foreach($items as $item): 
            ?>
            <tr>
                <td class="text-center"><?= $stt++ ?></td>
                <td><?= $item['name'] ?></td>
                <td class="text-center"><?= $item['quantity'] ?></td>
                <td class="text-right"><?= number_format($item['price']) ?> đ</td>
                <td class="text-right"><?= number_format($item['price'] * $item['quantity']) ?> đ</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 40%; float: right; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px;">Tạm tính:</td>
                <td class="text-right" style="padding: 5px;"><strong><?= number_format($order['total_amount'] - $order['shipping_fee'] + $order['discount_amount']) ?> đ</strong></td>
            </tr>
            <tr>
                <td style="padding: 5px;">Phí vận chuyển:</td>
                <td class="text-right" style="padding: 5px;">+ <?= number_format($order['shipping_fee']) ?> đ</td>
            </tr>
            <?php if($order['discount_amount'] > 0): ?>
            <tr>
                <td style="padding: 5px;">Giảm giá:</td>
                <td class="text-right" style="padding: 5px;">- <?= number_format($order['discount_amount']) ?> đ</td>
            </tr>
            <?php endif; ?>
            <tr>
                <td style="padding: 10px; border-top: 2px solid #000; font-size: 14px;"><strong>TỔNG CỘNG:</strong></td>
                <td class="text-right" style="padding: 10px; border-top: 2px solid #000; font-size: 14px;"><strong><?= number_format($order['total_amount']) ?> đ</strong></td>
            </tr>
        </table>
    </div>

    <div class="clearfix"></div>

    <div class="footer">
        <p>Cảm ơn quý khách đã mua hàng tại Camping Shop!</p>
        <p>Hóa đơn này được tạo tự động từ hệ thống.</p>
    </div>

</body>
</html>
