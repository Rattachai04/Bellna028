<?php
session_start();
require 'config.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการสั่งซื้อ | Neon Street</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&family=Orbitron:wght@600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #0a0a0f;
            color: #eaeaea;
            font-family: 'Poppins', sans-serif;
            padding: 40px 15px;
        }

        h2 {
            text-align: center;
            font-family: 'Orbitron', sans-serif;
            color: #00f0ff;
            text-shadow: 0 0 10px #00f0ff;
            margin-bottom: 40px;
        }

        .btn-main {
            background: linear-gradient(90deg, #ff2dd7, #00f0ff);
            border: none;
            color: #000;
            font-weight: 600;
            border-radius: 25px;
            padding: 8px 20px;
            transition: 0.3s;
        }
        .btn-main:hover {
            transform: scale(1.05);
            color: #000;
        }

        .order-card {
            background-color: #12121a;
            border-radius: 18px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid #1f1f2e;
        }

        .order-header {
            background: linear-gradient(90deg, #151520, #19192a);
            color: #00f0ff;
            padding: 15px 20px;
            font-weight: 500;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .order-header span {
            font-size: 0.9rem;
            color: #ccc;
        }

        .order-body {
            padding: 20px;
            background-color: #161625;
        }

        .order-items {
            background-color: #1c1c2e;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .order-items li {
            background: none;
            border: none;
            color: #ddd;
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #2b2b3d;
        }
        .order-items li:last-child {
            border-bottom: none;
        }

        .order-total {
            text-align: right;
            color: #00ffbf;
            font-weight: 600;
            margin-top: 10px;
        }

        .shipping-info {
            background-color: #1a1a27;
            border-left: 3px solid #ff2dd7;
            padding: 15px 20px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .shipping-info p {
            margin: 5px 0;
            font-size: 0.95rem;
            color: #ccc;
        }

        .shipping-info strong {
            color: #ff2dd7;
        }

        .alert {
            border-radius: 10px;
            font-weight: 500;
        }

        footer {
            text-align: center;
            color: #888;
            margin-top: 50px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="container" style="max-width: 900px;">
        <h2>🛍 ประวัติการสั่งซื้อ</h2>

        <div class="text-center mb-4">
            <a href="index.php" class="btn btn-main">← กลับหน้าหลัก</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">✅ ทำรายการสั่งซื้อเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <?php if (count($orders) === 0): ?>
            <div class="alert alert-warning text-center">ยังไม่มีการสั่งซื้อในระบบของคุณ 💬</div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>#<?= $order['order_id'] ?></strong> | <?= date('d/m/Y', strtotime($order['order_date'])) ?>
                        </div>
                        <div>
                            <span>สถานะ:</span> 
                            <strong style="color:#ff2dd7;"><?= ucfirst($order['status'] ?? 'N/A') ?></strong>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-items">
                            <ul class="list-unstyled mb-0">
                                <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                    <li>
                                        <span><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                                        <span><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="order-total">รวมทั้งหมด: <?= number_format($order['total_amount'], 2) ?> ฿</p>
                        </div>

                        <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                        <?php if ($shipping): ?>
                            <div class="shipping-info">
                                <p><strong>📍 ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= htmlspecialchars($shipping['postal_code']) ?></p>
                                <p><strong>📦 สถานะจัดส่ง:</strong> <?= ucfirst($shipping['status'] ?? 'รอดำเนินการ') ?></p>
                                <p><strong>📞 โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>
