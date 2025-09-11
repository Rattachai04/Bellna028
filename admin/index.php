<?php
require '../config.php';
// ตรวจสอบสิทธิ์admin
require 'auth.admin.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(to right, #ca7efaff, #87ebfaff);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
    color: #000205ff;
    font-weight: 700;
    margin-bottom: 5px;
}

p.mb-4 {
    font-size: 1.1rem;
    color: #333;
}

.btn {
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #3a8dff);
    border: none;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
}

.btn-warning {
    background: linear-gradient(45deg, #ffc107, #ffca2c);
    border: none;
    color: #212529;
}

.btn-dark {
    background: linear-gradient(45deg, #343a40, #495057);
    border: none;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
    border: none;
}

.row {
    margin-top: 20px;
}

a.btn.w-100 {
    font-size: 1rem;
}

@media (max-width: 576px) {
    h2 {
        font-size: 1.5rem;
    }
    p.mb-4 {
        font-size: 1rem;
    }
    a.btn.w-100 {
        font-size: 0.95rem;
        padding: 10px;
    }
}
</style>
</head>

<body class="container mt-4">
    <h2>ระบบผู้ดูแลระบบ</h2>
    <p class="mb-4">ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?></p>
    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="products.php" class="btn btn-primary w-100">จัดการสินค้า</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="orders.php" class="btn btn-success w-100">จัดการคำสั่งซื้อ</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="user.php" class="btn btn-warning w-100">จัดการสมาชิก</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="categories.php" class="btn btn-dark w-100">จัดการหมวดหมู่</a>
        </div>
    </div>
    <a href="../logout.php" class="btn btn-secondary mt-3">ออกจากระบบ</a>
</body>

</html>