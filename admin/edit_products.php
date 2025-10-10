<?php
require '../config.php';
require 'auth.admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header("Location: products.php");
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $imageName = $product['image'];

    if (!empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $allowed = ['image/jpeg', 'image/png'];

        if (in_array($file['type'], $allowed)) {
            if ($product['image']) {
                $oldPath = realpath(__DIR__ . '/../product_images/' . $product['image']);
                if ($oldPath && is_file($oldPath)) unlink($oldPath);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $imageName = 'product_' . time() . '.' . $ext;
            $path = __DIR__ . '/../product_images/' . $imageName;
            move_uploaded_file($file['tmp_name'], $path);
        }
    }

    $stmt = $conn->prepare("
        UPDATE products 
        SET product_name=?, description=?, price=?, stock=?, category_id=?, image=? 
        WHERE product_id=?
    ");
    $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName, $product_id]);
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แก้ไขสินค้า | StreetGenZ Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #1c1c1c, #2b2b2b);
    font-family: "Prompt", sans-serif;
    color: #fff;
    min-height: 100vh;
    padding-top: 40px;
}
.card-glass {
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 0 25px rgba(0,0,0,0.3);
}
h3 {
    font-weight: 700;
    color: #00eaff;
    text-shadow: 0 0 10px #6f00ffff;
}
label {
    font-weight: 500;
    color: #cfcfcf;
}
.form-control, .form-select {
    background: rgba(255,255,255,0.08);
    border: none;
    color: #fff;
    border-radius: 10px;
}
.form-control:focus, .form-select:focus {
    background: rgba(255,255,255,0.15);
    color: #fff;
    box-shadow: 0 0 0 2px #00eaff;
}
.btn-back {
    background: linear-gradient(45deg, #3a3a3a, #222);
    border: none;
    color: #ddd;
    border-radius: 10px;
    transition: all 0.3s;
}
.btn-back:hover {
    background: #444;
    color: #fff;
    transform: translateY(-2px);
}
.btn-save {
    background: linear-gradient(45deg, #00eaff, #784ba0);
    border: none;
    color: white;
    border-radius: 10px;
    transition: 0.3s;
    box-shadow: 0 0 12px rgba(0,234,255,0.4);
}
.btn-save:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 0 20px rgba(120,75,160,0.6);
}
.product-preview {
    border-radius: 15px;
    border: 2px solid rgba(255,255,255,0.15);
    object-fit: cover;
    width: 160px;
    height: 160px;
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
}
</style>
</head>
<body>
<div class="container">
    <h3 class="mb-4 text-center"><i class="bi bi-pencil-square me-2"></i>แก้ไขสินค้า</h3>

    <div class="card-glass p-5 mx-auto" style="max-width: 700px;">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รายละเอียดสินค้า</label>
                <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">ราคา (บาท)</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">จำนวน</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">หมวดหมู่</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $product['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 text-center">
                <label class="form-label d-block">รูปสินค้า (อัปโหลดใหม่เพื่อแทนที่)</label>
                <input type="file" name="product_image" class="form-control mb-3">
                <?php if ($product['image']): ?>
                    <img src="../product_images/<?= htmlspecialchars($product['image']) ?>" class="product-preview">
                <?php else: ?>
                    <div class="text-muted fst-italic">ไม่มีรูปภาพ</div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="products.php" class="btn-back px-4 py-2 me-2">
                    <i class="bi bi-arrow-left-circle me-1"></i> กลับ
                </a>
                <button type="submit" name="update_product" class="btn-save px-4 py-2">
                    <i class="bi bi-save2 me-1"></i> บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
