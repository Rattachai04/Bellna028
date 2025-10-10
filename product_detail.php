<?php
session_start(); 
require_once 'config.php'; 

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = ?
");
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
<title><?= htmlspecialchars($product['product_name']) ?> | Midnight Bloom</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    background: radial-gradient(circle at top left, #0a0a0a, #1a1a1a);
    font-family: 'Poppins', sans-serif;
    color: #fff;
    margin: 0;
    padding: 40px 0;
}

/* ---------- Container ---------- */
.container-detail {
    max-width: 1100px;
    margin: auto;
    background: linear-gradient(145deg, #141414, #1f1f1f);
    border-radius: 20px;
    box-shadow: 0 0 25px rgba(161, 60, 255, 0.3);
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
}

/* ---------- Product Image ---------- */
.product-img {
    flex: 1 1 45%;
    background: #0f0f0f;
    display: flex;
    align-items: center;
    justify-content: center;
}
.product-img img {
    width: 100%;
    height: auto;
    max-height: 550px;
    object-fit: cover;
    border-right: 2px solid #3cffea;
}

/* ---------- Product Info ---------- */
.product-info {
    flex: 1 1 55%;
    padding: 40px;
}
.product-info h1 {
    font-weight: 700;
    font-size: 2.2rem;
    color: #b94cff;
    text-shadow: 0 0 10px rgba(185, 76, 255, 0.6);
}
.category {
    font-size: 1rem;
    color: #3cffea;
    margin-bottom: 15px;
}
.price {
    font-size: 1.8rem;
    font-weight: 700;
    color: #3cffea;
    margin-top: 10px;
}
.description {
    font-size: 1rem;
    line-height: 1.7;
    color: #ccc;
    margin: 25px 0;
}
.stock {
    color: #aaa;
    margin-bottom: 20px;
}

/* ---------- Form ---------- */
.quantity-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
}
input[type=number] {
    width: 90px;
    border-radius: 10px;
    border: 1px solid #444;
    background: #1a1a1a;
    color: #fff;
    padding: 6px 10px;
    font-size: 1rem;
}
input[type=number]:focus {
    border-color: #b94cff;
    box-shadow: 0 0 10px rgba(185, 76, 255, 0.4);
    outline: none;
}

/* ---------- Buttons ---------- */
.btn-cart {
    background: linear-gradient(135deg, #b94cff, #3cffea);
    border: none;
    border-radius: 25px;
    padding: 12px 30px;
    font-weight: 600;
    color: #fff;
    transition: 0.3s;
}
.btn-cart:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(60,255,234,0.4);
}

.btn-back {
    display: inline-block;
    color: #ccc;
    text-decoration: none;
    margin-bottom: 30px;
    transition: 0.3s;
}
.btn-back:hover {
    color: #3cffea;
    transform: translateX(-5px);
}

/* ---------- Alert ---------- */
.alert-info {
    background: #252525;
    border: 1px solid #3cffea;
    color: #fff;
    border-radius: 12px;
    text-align: center;
    padding: 12px;
}

/* ---------- Responsive ---------- */
@media (max-width: 992px) {
    .container-detail {
        flex-direction: column;
    }
    .product-img img {
        border-right: none;
        border-bottom: 2px solid #3cffea;
    }
    .product-info {
        padding: 30px;
    }
}
</style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> กลับหน้ารายการสินค้า</a>

    <div class="container-detail">
        <div class="product-img">
            <img src="<?= htmlspecialchars(!empty($product['image']) ? 'product_images/' . $product['image'] : 'product_images/no-image.jpg') ?>" 
                 alt="<?= htmlspecialchars($product['product_name']) ?>" 
                 onerror="this.onerror=null;this.src='product_images/no-image.jpg';">
        </div>

        <div class="product-info">
            <h1><?= htmlspecialchars($product['product_name']) ?></h1>
            <div class="category"><i class="bi bi-tags-fill"></i> หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></div>
            <div class="price"><?= number_format($product['price'], 2) ?> ฿</div>
            <div class="stock"><i class="bi bi-box-seam"></i> คงเหลือ: <?= $product['stock'] ?> ชิ้น</div>

            <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <?php if ($isLoggedIn): ?>
                <form action="cart.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    <div class="quantity-box">
                        <label for="quantity" class="me-2">จำนวน:</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                    </div>
                    <button type="submit" class="btn-cart"><i class="bi bi-cart-plus-fill"></i> เพิ่มในตะกร้า</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-4">🔑 กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
