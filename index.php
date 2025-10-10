<?php 
session_start(); 
require_once 'config.php'; 

$isLoggedIn = isset($_SESSION['user_id']); 
$user_id = $_SESSION['user_id'] ?? 0; 

// ดึงสินค้า
$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Midnight Bloom | Street Style</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
  margin: 0;
  background: radial-gradient(circle at top left, #1a1a1a, #0c0c0c);
  font-family: "Poppins", "Segoe UI", sans-serif;
  color: #fff;
  overflow-x: hidden;
}

/* ---------- Sidebar ---------- */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 240px;
  height: 100vh;
  background: #111;
  border-right: 3px solid #8e3cff;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 1rem;
  z-index: 1000;
}
.sidebar img {
  width: 80px;
  height: 80px;
  margin-bottom: 1rem;
}
.sidebar h2 {
  color: #ff3c8d;
  font-weight: 800;
  font-size: 1.4rem;
  text-shadow: 0 0 10px #8e3cff;
  text-align: center;
  margin-bottom: 2rem;
}
.sidebar a {
  color: #eee;
  text-decoration: none;
  display: block;
  width: 100%;
  text-align: center;
  padding: .7rem 0;
  border-radius: 8px;
  transition: 0.2s;
}
.sidebar a:hover, .sidebar a.active {
  background: #8e3cff;
  color: #ffffffff;
}
.sidebar hr {
  border-color: #333;
  width: 100%;
  margin: 1.5rem 0;
}
.search-box {
  position: relative;
  width: 100%;
}
.search-box input {
  width: 100%;
  border-radius: 20px;
  border: none;
  padding: .4rem 2.2rem .4rem .8rem;
  background: #222;
  color: #fff;
}
.search-box button {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  background: #ff3c8d;
  border: none;
  color: #fff;
  border-radius: 50%;
  width: 28px;
  height: 28px;
}

/* ---------- Main Content ---------- */
.main {
  margin-left: 240px;
  padding: 2rem;
}

/* ---------- Hero Section ---------- */
.hero {
  text-align: center;
  padding: 3rem 1rem 1.5rem;
  background: linear-gradient(180deg, #181818 0%, #101010 100%);
  border-radius: 1rem;
  margin-bottom: 2rem;
  box-shadow: 0 0 20px rgba(142,60,255,0.4);
}
.hero h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #b94cff;
  text-shadow: 0 0 10px #ffffffff;
}
.hero p {
  color: #bbb;
  font-size: 1.1rem;
}

/* ---------- Product Grid ---------- */
.product-card {
  background: #181818;
  border-radius: 1rem;
  overflow: hidden;
  transition: all .3s ease;
  box-shadow: 0 3px 10px rgba(142,60,255,0.2);
}
.product-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 20px rgba(142,60,255,0.5);
}
.product-thumb {
  height: 230px;
  object-fit: cover;
  width: 100%;
}
.product-title {
  color: #fff;
  font-weight: 700;
  font-size: 1.1rem;
}
.price {
  color: #3cffea;
  font-weight: 700;
  font-size: 1.2rem;
}
.btn-buy {
  border: 1px solid #ff3c8d;
  color: #fff;
  border-radius: 25px;
  padding: .4rem 1.2rem;
  transition: .2s;
}
.btn-buy:hover {
  background: #ff3c8d;
}

/* ---------- Footer ---------- */
footer {
  text-align: center;
  color: #888;
  margin-top: 2rem;
  font-size: .9rem;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <img src="product_images/logo.png" alt="logo">
  <h2>Midnight Bloom</h2>
  
  <div class="search-box mb-3">
    <input type="text" placeholder="ค้นหา...">
    <button><i class="bi bi-search"></i></button>
  </div>

  <a href="index.php" class="active"><i class="bi bi-house-door"></i> หน้าหลัก</a>
  <a href="cart.php"><i class="bi bi-cart"></i> ตะกร้า</a>
  <a href="orders.php"><i class="bi bi-receipt"></i> คำสั่งซื้อ</a>
  <?php if ($isLoggedIn): ?>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
  <?php else: ?>
    <a href="login.php"><i class="bi bi-person"></i> เข้าสู่ระบบ</a>
  <?php endif; ?>
  
  <hr>
  <div class="text-muted small mt-auto">
    <p>© 2025 Midnight Bloom</p>
  </div>
</div>

<!-- Main Content -->
<div class="main">
  <section class="hero">
    <h1>Street Style Collection</h1>
    <p>แฟชั่นสายเท่ ความเป็นตัวเองของคุณ 💜</p>
  </section>

  <div class="row g-4">
    <?php foreach ($products as $p): 
      $img = !empty($p['image']) ? "product_images/" . rawurlencode($p['image']) : "product_images/no-image.jpg"; ?>
      <div class="col-12 col-sm-6 col-lg-3">
        <div class="product-card text-center">
          <img src="<?= htmlspecialchars($img) ?>" class="product-thumb" alt="สินค้า">
          <div class="p-3">
            <div class="product-title"><?= htmlspecialchars($p['product_name']) ?></div>
            <div class="text-muted small mb-1"><?= htmlspecialchars($p['category_name']) ?></div>
            <div class="price mb-3"><?= number_format($p['price'], 2) ?> ฿</div>
            <a href="product_detail.php?id=<?= $p['product_id'] ?>" class="btn-buy btn-sm">ดูสินค้า</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>
