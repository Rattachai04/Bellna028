<?php
    session_start(); // เริ่ม session
require_once 'config.php'; // เชื่อมต่อฐานข้อมูล
if (!isset($_GET['id'])) { // ตรวจสอบว่ามีการส่ง ID ของสินค้าเข้ามาหรือไม่
header("Location: index.php");
exit;
}
$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
echo "<h3>ไม่พบสินค้าที่คุณต้องการ</h3>";
exit;
}
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายละเอียดสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(to right, #e0f7fa, #f8f9fa);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card {
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    background: #d980ffff;
    padding: 20px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}

.card-title {
    font-weight: 700;
    color: #0d6efd;
    font-size: 2rem;
}

.product-info p {
    font-size: 1.15rem;
    margin-bottom: 10px;
}

.btn {
    border-radius: 30px;
    padding: 10px 25px;
    transition: background 0.3s, transform 0.2s;
}

.btn:hover {
    transform: scale(1.05);
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
}

h6.text-muted {
    font-size: 1rem;
    font-style: italic;
}

input#quantity {
    border-radius: 12px;
    padding: 5px 10px;
    font-size: 1rem;
}

.alert-info {
    font-size: 1.1rem;
    border-radius: 15px;
    background: #d1ecf1;
    color: #0c5460;
}

@media (max-width: 576px) {
    .card-title {
        font-size: 1.5rem;
    }
    input#quantity {
        width: 60px !important;
    }
}
</style>
</head>
<body class="container mt-4">
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ารายการสินค้า</a>
<div class="card">
    <div class="card-body">
        <h3 class="card-title mb-3"><?= htmlspecialchars($product['product_name']) ?></h3>
        <h6 class="text-muted mb-3">📂 หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>
        <p class="card-text mt-3"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        
        <div class="product-info mt-3">
            <p><strong>💰 ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>
            <p><strong>📦 คงเหลือ:</strong> <?= $product['stock'] ?> ชิ้น</p>
        </div>

        <?php if ($isLoggedIn): ?>
        <form action="cart.php" method="post" class="mt-3">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <label for="quantity" class="form-label">จำนวน:</label>
            <input type="number" name="quantity" id="quantity" class="form-control w-25 d-inline-block"
                   value="1" min="1" max="<?= $product['stock'] ?>" required>
            <button type="submit" class="btn btn-success ms-2">🛒 เพิ่มในตะกร้า</button>
        </form>
        <?php else: ?>
        <div class="alert alert-info mt-3 text-center">🔑 กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
