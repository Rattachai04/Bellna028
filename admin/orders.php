<?php
require '../config.php';
require 'auth.admin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ดึงคำสั่งซื้อทั้งหมด
$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../function.php'; // ฟังก์ชัน getOrderItems() และ getShippingInfo()
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>คำสั่งซื้อทั้งหมด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&display=swap');

        body {
            background: linear-gradient(135deg, #0d0d0d, #1e1e1e);
            color: #f8f9fa;
            font-family: "Prompt", sans-serif;
            padding-bottom: 60px;
        }

        .container-main {
            max-width: 1100px;
            margin: 40px auto;
        }

        h2 {
            color: #00e0ff;
            font-weight: 600;
            text-shadow: 0 0 8px rgba(0, 224, 255, 0.8);
        }

        .order-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 25px rgba(0, 255, 200, 0.2);
        }

        .order-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .order-id {
            font-size: 1.2rem;
            font-weight: 600;
            color: #7df9ff;
        }

        .badge-status {
            font-size: 0.95rem;
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 600;
            color: #fff;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .status-pending {
            background: linear-gradient(45deg, #6c757d, #495057);
        }

        .status-processing {
            background: linear-gradient(45deg, #17a2b8, #0dcaf0);
        }

        .status-shipped {
            background: linear-gradient(45deg, #007bff, #00bfff);
        }

        .status-completed {
            background: linear-gradient(45deg, #28a745, #20c997);
        }

        .status-cancelled {
            background: linear-gradient(45deg, #dc3545, #ff4b5c);
        }

        .shipping-status {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .list-group-item {
            background: rgba(255, 255, 255, 0.08);
            border: none;
            color: #fff;
            border-radius: 10px;
            margin-bottom: 6px;
        }

        .total {
            font-weight: 700;
            color: #20c997;
        }

        footer {
            text-align: center;
            color: #aaa;
            font-size: 14px;
            margin-top: 40px;
        }

        .btn-back {
            background: linear-gradient(45deg, #00e0ff, #007bff);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 12px;
            padding: 8px 16px;
            box-shadow: 0 0 12px rgba(0, 224, 255, 0.4);
            transition: all 0.3s;
        }

        .btn-back:hover {
            transform: scale(1.05);
            box-shadow: 0 0 18px rgba(0, 224, 255, 0.7);
        }
    </style>
</head>

<body>

    <div class="container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-check me-2"></i> คำสั่งซื้อทั้งหมด</h2>
            <a href="index.php" class="btn-back"><i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล</a>
        </div>

        <?php foreach ($orders as $order): ?>
            <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
            <div class="order-card">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <span class="order-id"><i class="bi bi-box-seam me-1"></i> #<?= $order['order_id'] ?> - <?= htmlspecialchars($order['username']) ?></span>
                    <?php
                    $statusClass = [
                        'pending' => 'status-pending',
                        'processing' => 'status-processing',
                        'shipped' => 'status-shipped',
                        'completed' => 'status-completed',
                        'cancelled' => 'status-cancelled'
                    ];
                    $statusLabel = [
                        'pending' => '🕓 รอดำเนินการ',
                        'processing' => '⚙️ กำลังดำเนินการ',
                        'shipped' => '🚚 จัดส่งแล้ว',
                        'completed' => '✅ สำเร็จ',
                        'cancelled' => '❌ ยกเลิก'
                    ];
                    ?>
                    <span class="badge-status <?= $statusClass[$order['status']] ?>">
                        <?= $statusLabel[$order['status']] ?>
                    </span>
                </div>

                <p><strong>วันที่สั่งซื้อ:</strong> <?= $order['order_date'] ?></p>

                <h6 class="mt-3 mb-2 text-info"><i class="bi bi-cart-check me-1"></i> รายการสินค้า</h6>
                <ul class="list-group mb-3">
                    <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                            <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p class="text-end total">💰 ยอดรวม: <?= number_format($order['total_amount'], 2) ?> บาท</p>

                <?php if ($shipping): ?>
                    <div class="mt-3">
                        <h6 class="text-success"><i class="bi bi-truck me-1"></i> ข้อมูลจัดส่ง</h6>
                        <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>,
                            <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                        <?php
                        $shipLabel = [
                            'not_shipped' => '📦 ยังไม่จัดส่ง',
                            'shipped' => '🚚 กำลังจัดส่ง',
                            'delivered' => '✅ จัดส่งสำเร็จ'
                        ];
                        ?>
                        <p class="shipping-status"><strong>สถานะจัดส่ง:</strong>
                            <?= $shipLabel[$shipping['shipping_status']] ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
