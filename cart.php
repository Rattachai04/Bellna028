<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_name, products.price
                        FROM cart
                        JOIN products ON cart.product_id = products.product_id
                        WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>🛍️ ตะกร้าสินค้า - Midnight Bloom Shop</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    margin:0;
    padding:0;
    background: linear-gradient(135deg, #1a1a1a, #2c2c2c);
    color:#fff;
}

.header {
    padding: 20px;
    text-align: center;
    background: linear-gradient(135deg, #c037ffff, #63b6ffff);
    color: #fff;
    font-size: 2rem;
    font-weight: 700;
    text-shadow: 1px 1px 3px #000;
}

.container-full {
    width: 100%;
    padding: 30px 50px;
    box-sizing: border-box;
}

.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    color: #fff;
    background: #6682ffff;
    padding: 10px 25px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}
.back-btn:hover {
    background: #3cff8aff;
    transform: scale(1.05);
}

.table-container {
    overflow-x: auto;
    width: 100%;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 15px;
    overflow: hidden;
    background: #2a2a2a;
    color: #fff;
}

thead {
    background: linear-gradient(135deg, #c037ffff, #63b6ffff);
    color: #fff;
}

tbody tr:nth-child(even) {
    background: #333;
}
tbody tr:hover {
    background: #444;
}

td, th {
    text-align: center;
    padding: 15px;
    vertical-align: middle;
}

.btn-remove {
    background: #ff4f7b;
    color: #000000ff;
    border:none;
    border-radius: 20px;
    padding: 8px 18px;
    font-weight:600;
    transition:0.3s;
}
.btn-remove:hover {
    background: #ff1f4b;
    transform: scale(1.05);
}

.btn-checkout {
    background: linear-gradient(135deg, #37ffc6ff, #e0ff63ff);
    color: #000000ff;
    border: none;
    border-radius: 25px;
    padding: 12px 35px;
    font-weight: 700;
    font-size: 1.1rem;
    margin-top: 20px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: 0.3s;
}

.btn-checkout:hover {
    background-color: #333; /* สีดำอ่อนขึ้นเล็กน้อยเมื่อ Hover */
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 5px 15px rgba(255,255,255,0.2);
}


.alert {
    background: #444;
    color: #ff66a3;
    border-radius: 15px;
    padding: 20px;
    text-align:center;
    font-weight:600;
}
</style>
</head>
<body>

<div class="header">🛒 ตะกร้าสินค้า</div>

<div class="container-full">
    <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i> กลับไปเลือกสินค้า</a>

    <?php if(count($items)===0): ?>
        <div class="alert">ยังไม่มีสินค้าในตะกร้า 💕</div>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>รวม</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'],2) ?> ฿</td>
                        <td><?= number_format($item['price']*$item['quantity'],2) ?> ฿</td>
                        <td>
                            <a href="cart.php?remove=<?= $item['cart_id'] ?>" class="btn-remove"><i class="bi bi-trash-fill"></i> ลบ</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>ยอดรวมทั้งหมด:</strong></td>
                    <td colspan="2"><strong><?= number_format($total,2) ?> ฿</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="text-center">
        <a href="checkout.php" class="btn-checkout"><i class="bi bi-bag-check-fill"></i> ดำเนินการสั่งซื้อ</a>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
