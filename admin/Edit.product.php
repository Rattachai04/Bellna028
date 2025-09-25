<?php
require '../config.php';
require '../auth.admin.php';

if (!isset($_SESSION[' TODO ']) || $_SESSION['role'] !== ' TODO ') {
header("Location: ../login.php");
exit;
}

// ตรวจสอบวำ่ ไดส้ ง่ id สนิ คำ้มำหรอื ไม่
if (!isset($_GET['id'])) {
header("Location: products.php");
exit;
}
$product_id = $_GET['id'];
// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?"); //where ที่เราจ่ะส่ง
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if ($product) {
echo "<h3>ไมพ่ บขอ้ มลู สนิ คำ้</h3>";
exit;
}
// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// เมอื่ มกี ำรสง่ ฟอรม์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$name = trim($_POST['product_name']);
$description = trim($_POST['description']);
$price = (float)$_POST['price'];
$stock = (int)$_POST['stock'];
$category_id = (int)$_POST['category_id'];
// ค่ำรูปเดิมจำกฟอร์ม
$oldImage = $_POST['old_image'] ?? null;
$removeImage = isset($_POST['remove_image']); // true/false
if ($name && $price > 0) {
// เตรียมตัวแปรรูปที่จะบันทึก
$newImageName = $oldImage; // default: คงรูปเดิมไว้
// 1) ถ ้ำมีติ๊ก "ลบรูปเดิม" → ตั้งให้เป็น null
if ($removeImage) {
$newImageName = null;
}
// 2) ถ ้ำมีอัปโหลดไฟล์ใหม่ → ตรวจแลว้เซฟไฟลแ์ ละตัง้ชอื่ ใหมท่ ับคำ่
if (!empty($_FILES['product_image']['name'])) {
$file = $_FILES['product_image'];
// ตรวจชนิดไฟล์แบบง่ำย (แนะน ำ: ตรวจ MIME จริงด ้วย finfo)
$allowed = ['image/jpeg', 'image/png'];
if (in_array($file['type'], $allowed, true) && $file['error'] === UPLOAD_ERR_OK) {
// สรำ้งชอื่ ไฟลใ์หม่
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newImageName = 'product_' . time() . '.' . $ext;
$uploadDir = realpath(__DIR__ . '/../product_images');
$destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;
// ย้ำยไฟล์อัปโหลด
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
// ถ ้ำย้ำยไม่ได ้ อำจตั้ง flash message แลว้คงใชรู้ปเดมิ ไว ้
$newImageName = $oldImage;
}
}
}
// อัปเดต DB
$sql = "UPDATE products
SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
WHERE product_id = ?";
$args = [$name, $description, $price, $stock, $category_id, $newImageName, $product_id];
$stmt = $conn->prepare($sql);
$stmt->execute($args);
// ลบไฟล์เก่ำในดิสก์ ถ ้ำ:
// - มีรูปเดิม ($oldImage) และ
// - เกดิ กำรเปลยี่ นรปู (อัปโหลดใหมห่ รอื สั่งลบรปู เดมิ)
if (!empty($oldImage) && $oldImage !== $newImageName) {
$baseDir = realpath(__DIR__ . '/../product_images');
$filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $oldImage);
if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
@unlink($filePath);
}
}
header("Location: 68products.php");
exit;
}
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แกไชสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>แกไ้ขสนิ คำ้</h2>
    <a href="products.php" class="btn btn-secondary mb-3">← กลับไปยังรายการสินค้า</a>
    <form method="post" enctype="multipart/form-data"class="row g-3">
        <div class="col-md-6">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($product ['product_name'])?>" require>
        </div>
        <div class="col-md-3">
            <label class="form-label">ราคา</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product ['price']?>" required >
        </div>
        <div class="col-md-3">
            <label class="form-label">จำนวนในคลัง</label>
            <input type="number" name="stock" class="form-control" value="<?= TODO ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">หมวดหมู่</label>
            <select name="category_id" class="form-select" required>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['catgory_id'] ?>" <?= $product['catgory_id'] === $cat ['catgory_id'] ?'selected' : ''?>>
                    <?= htmlspecialchars(too) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">รำยละเอยีดสนิ คำ้</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product) ?></textarea>
        </div>

        <!--TODO ===div แสดงรูปเดิม + เก็บค่ำเก่ำ -->
        <div class="col-md-6">
        <label class="form-label d-block">รูปปัจจุบัน</label>
        <?php if (!empty($product['../product_images'])): ?>
        <img src="../product_images/<?= htmlspecialchars($product['image']) ?>"
        width="120" height="120" class="rounded mb-2">
        <?php else: ?>
        <span class="text-muted d-block mb-2">ไม่มีรูป</span>
        <?php endif; ?>
        <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
        </div>

        <!--TODO === อัปโหลดรูปใหม่ (ทำงเลือก) -->
        <div class="col-md-6">
        <label class="form-label">อัปโหลดรูปใหม่ (jpg, png)</label>
        <input type="file" name="product_image" class="form-control">
        <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image"
        value="1">
        <label class="form-check-label" for="remove_image">ลบรูปเดิม</label>
        </div>
        </div>



        <div class="col-12">
            <button type="submit" class="btn btn-primary">บันทึกกำรแก ้ไข</button>
        </div>
    </form>
</body>
</html>