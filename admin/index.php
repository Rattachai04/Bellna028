<?php
require '../config.php';
require 'auth.admin.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ | Midnight Bloom Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            background: linear-gradient(180deg, #1a1a1a 0%, #333333 100%);
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }

        .admin-container {
            max-width: 1100px;
            margin: 50px auto;
            background: #121212;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(4, 115, 250, 0.4);
            padding: 50px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .admin-header h2 {
            font-weight: 700;
            font-size: 2.2rem;
            color: #b149fcff;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
        }

        .admin-header p {
            color: #ccc;
            font-size: 1.1rem;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .admin-card {
            padding: 40px 20px;
            border-radius: 20px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 180px;
        }

        .admin-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .admin-card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 10px 20px rgba(236, 252, 11, 0.5);
        }

        .btn-products {
            background: linear-gradient(135deg, #7c83fd, #96baff);
            color: #fff;
        }

        .btn-orders {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: #fff;
        }

        .btn-users {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
            color: #fff;
        }

        .btn-categories {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: #fff;
        }

        .logout-btn {
            display: block;
            margin: 50px auto 0;
            text-align: center;
        }

        .logout-btn .btn {
            background: #42c8fdff;
            border: none;
            border-radius: 25px;
            font-size: 1.2rem;
            padding: 10px 30px;
            transition: 0.3s;
        }

        .logout-btn .btn:hover {
            background: #ff6380;
            transform: translateY(-3px);
        }
        @media (max-width: 768px) {
            .admin-container {
                padding: 30px 20px;
            }
            .admin-card {
                min-height: 160px;
                font-size: 1.1rem;
            }
            .admin-card i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <h2>🛍️ แผงควบคุมผู้ดูแลระบบ</h2>
            <p>ยินดีต้อนรับ, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        </div>

        <div class="admin-menu">
            <a href="products.php" class="admin-card btn-products">
                <i class="bi bi-bag-fill"></i>
                จัดการสินค้า
            </a>
            <a href="orders.php" class="admin-card btn-orders">
                <i class="bi bi-box-seam"></i>
                จัดการคำสั่งซื้อ
            </a>
            <a href="user.php" class="admin-card btn-users">
                <i class="bi bi-people-fill"></i>
                จัดการสมาชิก
            </a>
            <a href="categories.php" class="admin-card btn-categories">
                <i class="bi bi-folder-fill"></i>
                จัดการหมวดหมู่
            </a>
        </div>

        <div class="logout-btn">
            <a href="../logout.php" class="btn btn-lg"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
        </div>
    </div>
</body>

</html>
