<?php
require '../config.php';
require 'auth.admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name        = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $imageName   = null;

    if ($name && $price > 0) {
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], __DIR__ . '/../product_images/' . $imageName);
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        header("Location: products.php");
        exit;
    }
}

// ✅ ลบสินค้า
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $img = $conn->prepare("SELECT image FROM products WHERE product_id=?");
    $img->execute([$id]);
    $image = $img->fetchColumn();

    $conn->prepare("DELETE FROM products WHERE product_id=?")->execute([$id]);
    if ($image && file_exists(__DIR__ . '/../product_images/' . $image)) {
        unlink(__DIR__ . '/../product_images/' . $image);
    }
    header("Location: products.php");
    exit;
}

// ✅ ดึงข้อมูล
$products = $conn->query("
    SELECT p.*, c.category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
")->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสินค้า | StreetGenZ Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: #222121ff;
    font-family: "Prompt", sans-serif;
    color: #fff;
}
.header-title {
    font-weight: 700;
    color: #f8f8f8;
}
.card-glass {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
}
.btn-genz {
    background: linear-gradient(45deg, #3cffc8ff, #784ba0, #2b86c5);
    border: none;
    color: white;
    border-radius: 10px;
    transition: 0.3s;
}
.btn-genz:hover {
    opacity: 0.9;
    transform: scale(1.05);
}
.btn-del {
    background: linear-gradient(45deg, #ff5858, #f857a6);
    border: none;
    color: white;
    border-radius: 10px;
}
.table-card {
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 10px;
}
.product-img {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.1);
}
.category-tag {
    background: #2b86c5;
    color: #fff;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.85rem;
}
.btn-edit {
    background: linear-gradient(45deg, #00c6ff, #0072ff);
    border: none;
    color: #fff;
    border-radius: 10px;
    padding: 6px 12px;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(0, 114, 255, 0.3);
}
.btn-edit:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 0 14px rgba(0, 204, 255, 0.6);
    opacity: 0.95;
}

.btn-del {
    background: linear-gradient(45deg, #ff416c, #ff4b2b);
    border: none;
    color: #fff;
    border-radius: 10px;
    padding: 6px 12px;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(255, 75, 43, 0.3);
}
.btn-del:hover {
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 0 14px rgba(255, 75, 43, 0.6);
    opacity: 0.95;
}

</style>
</head>
<body class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="header-title">
        <i class="bi bi-lightning-charge-fill me-2" style="color:#ff3cac;"></i>StreetGenZ Store | Admin
    </h2>
    <a href="index.php" class="btn btn-outline-light">
        <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
    </a>
</div>

<!-- ฟอร์มเพิ่มสินค้า -->
<div class="card-glass p-4 mb-4">
    <h4 class="mb-3"><i class="bi bi-plus-circle me-2 text-warning"></i>เพิ่มสินค้าใหม่</h4>
    <form method="post" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="product_name" class="form-control bg-dark text-light" placeholder="ชื่อสินค้า" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control bg-dark text-light" placeholder="ราคา" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="stock" class="form-control bg-dark text-light" placeholder="จำนวน" required>
        </div>
        <div class="col-md-4">
            <select name="category_id" class="form-select bg-dark text-light" required>
                <option value="">เลือกหมวดหมู่</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <textarea name="description" class="form-control bg-dark text-light" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
        </div>
        <div class="col-md-6">
            <input type="file" name="product_image" class="form-control bg-dark text-light">
        </div>
        <div class="col-12 text-end">
            <button type="submit" name="add_product" class="btn-genz px-4 py-2">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
            </button>
        </div>
    </form>
</div>

<!-- รายการสินค้า -->
<h4 class="mb-3"><i class="bi bi-shop-window me-2 text-info"></i>รายการสินค้า</h4>
<div class="row g-4">
    <?php if ($products): ?>
        <?php foreach ($products as $p): ?>
            <div class="col-md-4">
                <div class="table-card text-center p-3 h-100">
                    <?php if ($p['image']): ?>
                        <img src="../product_images/<?= htmlspecialchars($p['image']) ?>" class="product-img mb-3">
                    <?php else: ?>
                        <div class="product-img d-flex align-items-center justify-content-center bg-dark text-muted">No Image</div>
                    <?php endif; ?>
                    <h5 class="fw-bold"><?= htmlspecialchars($p['product_name']) ?></h5>
                    <div class="category-tag mb-2"><?= htmlspecialchars($p['category_name']) ?></div>
                    <p class="mb-1 text-secondary">฿<?= number_format($p['price'],2) ?> | คงเหลือ: <?= $p['stock'] ?></p>
                    <div class="mt-3 d-flex justify-content-center gap-2">
                    <a href="edit_products.php?id=<?= $p['product_id'] ?>" class="btn-edit btn-sm">
                    <i class="bi bi-pencil-square me-1"></i> แก้ไข
                    </a>
                    <a href="products.php?delete=<?= $p['product_id'] ?>" 
                    onclick="return confirm('ยืนยันการลบสินค้านี้?')" 
                    class="btn-del btn-sm">
                    <i class="bi bi-trash3-fill me-1"></i> ลบ
    </a>
</div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted text-center">ยังไม่มีสินค้า</p>
    <?php endif; ?>
</div>

</body>
</html>
