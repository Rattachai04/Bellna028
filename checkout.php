<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// ดึงสินค้าในตะกร้า
$stmt = $conn->prepare("
    SELECT c.quantity, p.product_id, p.product_name, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// รวมราคา
$total = 0;
foreach ($items as $i) $total += $i['price'] * $i['quantity'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($address=='' || $city=='' || $postal=='' || $phone=='') {
        $errors[] = "กรุณากรอกข้อมูลให้ครบ";
    } elseif (empty($items)) {
        $errors[] = "ตะกร้าของคุณว่าง";
    } else {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $i) {
                $itemStmt->execute([$order_id, $i['product_id'], $i['quantity'], $i['price']]);
            }

            $ship = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $ship->execute([$order_id, $address, $city, $postal, $phone]);

            $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
            $conn->commit();

            $success = true;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Checkout - Neon Street</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body {
        background-color: #0a0a0f;
        font-family: "Poppins", sans-serif;
        color: #eaeaea;
        margin: 0; padding: 0;
    }
    .checkout-box {
        max-width: 700px;
        margin: 50px auto;
        background: #12121a;
        border-radius: 20px;
        box-shadow: 0 0 25px rgba(0,255,255,0.1);
        padding: 30px;
    }
    h1 {
        text-align: center;
        font-family: 'Orbitron', sans-serif;
        color: #00f0ff;
        margin-bottom: 20px;
    }
    .item { display: flex; justify-content: space-between; border-bottom: 1px solid #2b2b36; padding: 8px 0; }
    .item:last-child { border: none; }
    .total { text-align: right; margin-top: 10px; font-weight: bold; color: #ff2dd7; }

    label { display: block; margin-top: 12px; color: #aaa; font-size: 14px; }
    input {
        width: 100%; padding: 10px;
        border-radius: 10px; border: none;
        background: #1b1b25; color: #fff;
        margin-top: 5px;
    }
    input:focus { outline: 1px solid #00f0ff; }

    .btn {
        margin-top: 20px;
        width: 100%;
        padding: 12px;
        border: none; border-radius: 25px;
        background: linear-gradient(90deg,#ff2dd7,#00f0ff);
        color: #000; font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .btn:hover { transform: scale(1.03); }

    .alert {
        background: #2b2b36;
        color: #ff6fae;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
        text-align: center;
    }
    .success {
        color: #00ffbf;
    }
    a.back {
        display: inline-block;
        text-align: center;
        margin-top: 10px;
        color: #00f0ff;
        text-decoration: none;
    }
</style>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Poppins:wght@300;500&display=swap" rel="stylesheet">
</head>
<body>

<div class="checkout-box">
    <h1>🛒 Neon Checkout</h1>

    <?php if ($errors): ?>
        <div class="alert"><?= implode("<br>", array_map("htmlspecialchars", $errors)) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success">✅ สั่งซื้อสำเร็จ! ขอบคุณที่อุดหนุน 💖</div>
        <a href="orders.php" class="back">ดูคำสั่งซื้อของฉัน →</a>
    <?php else: ?>
        <!-- รายการสินค้า -->
        <?php if (empty($items)): ?>
            <p style="text-align:center;color:#888;">ตะกร้าว่างค่ะ 💕</p>
        <?php else: ?>
            <?php foreach ($items as $i): ?>
                <div class="item">
                    <span><?= htmlspecialchars($i['product_name']) ?> (×<?= $i['quantity'] ?>)</span>
                    <span><?= number_format($i['price'] * $i['quantity'], 2) ?> ฿</span>
                </div>
            <?php endforeach; ?>
            <div class="total">รวมทั้งหมด: <?= number_format($total, 2) ?> ฿</div>
        <?php endif; ?>

        <form method="post">
            <label>ที่อยู่จัดส่ง</label>
            <input type="text" name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            <label>จังหวัด</label>
            <input type="text" name="city" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            <label>รหัสไปรษณีย์</label>
            <input type="text" name="postal_code" required value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
            <label>เบอร์โทรศัพท์</label>
            <input type="text" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

            <button type="submit" class="btn" <?= empty($items) ? 'disabled' : '' ?>>ยืนยันการสั่งซื้อ</button>
        </form>
        <a href="cart.php" class="back">← กลับไปตะกร้า</a>
    <?php endif; ?>
</div>

</body>
</html>
