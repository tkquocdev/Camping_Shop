<?php
// Fragment HTML (không bao gồm <html>/<head>/<body>)
// Dùng bởi MailHelper để render cả email và PDF.
// Được truyền vào:
// - $order: array
// - $items: array
// - $customerName: string
?>

<div style="font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 20px; line-height: 1.4;">
    <div style="margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h1 style="font-size: 24px; font-weight: bold; margin: 0; color: #000;">CAMPING SHOP</h1>
                    <p style="margin: 5px 0; font-size: 11px;">Thới An Hội, Kế Sách, Sóc Trăng</p>
                    <p style="margin: 5px 0; font-size: 11px;">Hotline: 0868.285.284</p>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <h2 style="font-size: 18px; font-weight: bold; margin: 0; color: #000;">HÓA ĐƠN BÁN LẺ</h2>
                    <p style="margin: 5px 0; font-size: 12px; font-weight: bold;">Mã đơn: #<?= htmlspecialchars($order['id']) ?></p>
                    <p style="margin: 5px 0; font-size: 11px;">Ngày: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    <p style="margin: 5px 0; font-size: 11px;">Trạng thái: <?= strtoupper(htmlspecialchars($order['status'] ?? 'pending')) ?></p>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; margin-bottom: 20px; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px;">THÔNG TIN KHÁCH HÀNG</h3>
                <p style="margin: 5px 0;"><strong>Họ tên:</strong> <?= htmlspecialchars($customerName) ?></p>
                <p style="margin: 5px 0;"><strong>SĐT:</strong> <?= htmlspecialchars($order['phone'] ?? '') ?></p>
                <p style="margin: 5px 0;"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['shipping_address'] ?? '') ?></p>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 5px;">THÔNG TIN ĐƠN HÀNG</h3>
                <p style="margin: 5px 0;"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note'] ?? 'Không có') ?></p>
                <p style="margin: 5px 0;"><strong>Thanh toán:</strong> <?= strtoupper(htmlspecialchars($order['payment_method'] ?? 'cod')) ?></p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 5%;">STT</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: left; font-weight: bold;">Tên sản phẩm</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; width: 15%;">SL</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; width: 20%;">Đơn giá</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; width: 20%;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 1; foreach ($items as $item): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><?= $stt++ ?></td>
                    <td style="border: 1px solid #000; padding: 8px;"><?= htmlspecialchars($item['name']) ?></td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;"><?= (int) $item['quantity'] ?></td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: right;"><?= number_format($item['price']) ?> đ</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: right;"><?= number_format($item['price'] * $item['quantity']) ?> đ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="width: 40%; margin-left: auto; margin-top: 20px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr>
                <td style="padding: 5px; font-weight: bold;">Tạm tính:</td>
                <td style="padding: 5px; text-align: right; font-weight: bold;"><?= number_format($order['total_amount'] - $order['shipping_fee'] + $order['discount_amount']) ?> đ</td>
            </tr>
            <tr>
                <td style="padding: 5px;">Phí vận chuyển:</td>
                <td style="padding: 5px; text-align: right;">+ <?= number_format($order['shipping_fee']) ?> đ</td>
            </tr>
            <?php if (!empty($order['discount_amount'])): ?>
            <tr>
                <td style="padding: 5px;">Giảm giá:</td>
                <td style="padding: 5px; text-align: right; color: red;">- <?= number_format($order['discount_amount']) ?> đ</td>
            </tr>
            <?php endif; ?>
            <tr style="border-top: 2px solid #000;">
                <td style="padding: 8px; font-weight: bold; font-size: 13px;">TỔNG CỘNG:</td>
                <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 13px;"><?= number_format($order['total_amount']) ?> đ</td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div style="margin-top: 30px; text-align: center; font-size: 11px; border-top: 1px solid #000; padding-top: 10px;">
        <p style="margin: 5px 0;"><strong>Cảm ơn quý khách đã mua hàng tại Camping Shop!</strong></p>
        <p style="margin: 5px 0;">Hóa đơn này được tạo tự động từ hệ thống.</p>
        <p style="margin: 5px 0;">Thời gian in: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</div>
