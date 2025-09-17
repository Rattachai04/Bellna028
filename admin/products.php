<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
    }
    h2, h5 {
        font-weight: 600;
        color: #333;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    table thead {
        background: #0d6efd;
        color: #fff;
    }
    table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .btn {
        border-radius: 20px;
        padding: 6px 14px;
    }
    textarea, input, select {
        border-radius: 10px !important;
    }
</style>
</head>
<body class="container mt-4">

<h2 class="mb-4 text-center">จัดกำรสนิ คำ้</h2>
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ำผู้ดูแล</a>

<!-- ฟอรม์ เพมิ่ สนิคำ้ใหม่ -->
<div class="card p-4 mb-4">
    <h5 class="mb-3">เพมิ่ สนิคำ้ใหม่</h5>
    <form method="post" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="stock" class="form-control" placeholder="จำนวน" required>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select" required>
                <option value="">เลือกหมวดหมู่</option>
                <?php foreach ($ตัวแปรที่เก็บหมวดหมู่ as $cat): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['ชอื่ หมวดหม'ู่]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
        </div>
        <div class="col-12 text-end">
            <button type="submit" name="add_product" class="btn btn-primary">+ เพิ่มสินค้า</button>
        </div>
    </form>
</div>

<!-- แสดงรายการสินค้า -->
<div class="card p-4">
    <h5 class="mb-3">รายการสินค้า</h5>
    <table class="table table-bordered table-hover align-middle text-center">
        <thead>
            <tr>
                <th>ชื่อสินค้า</th>
                <th>หมวดหมู่</th>
                <th>ราคา</th>
                <th>คงเหลือ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ตัวแปรเก็บสนิ $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['ค่ำที่ต ้องกำรแสดง']) ?></td>
                <td><?= htmlspecialchars($p['ค่ำที่ต ้องกำรแสดง']) ?></td>
                <td><?= number_format($p['ค่ำที่ต ้องกำรแสดง'], 2) ?> บาท</td>
                <td><?= $p['ค่ำที่ต ้องกำรแสดง'] ?></td>
                <td>
                    <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('ยืนยันการลบสินค้านี้?')">ลบ</a>
                    <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning">แก้ไข</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
