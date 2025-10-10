<?php
require '../config.php';
require 'auth.admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสิทธิ์แอดมิน
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// ป้องกัน CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// --- การทำงานหลัก ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            throw new Exception('CSRF token ไม่ถูกต้อง');
        }

        if (isset($_POST['add_category'])) {
            $name = trim($_POST['category_name']);
            if ($name === '') throw new Exception('กรุณากรอกชื่อหมวดหมู่');
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (:n)");
            $stmt->execute([':n' => $name]);
            $_SESSION['success'] = 'เพิ่มหมวดหมู่สำเร็จ ✅';
        }

        if (isset($_POST['update_category'])) {
            $id = (int)$_POST['category_id'];
            $new = trim($_POST['new_name']);
            if ($new === '') throw new Exception('กรุณากรอกชื่อใหม่');
            $stmt = $conn->prepare("UPDATE categories SET category_name = :n WHERE category_id = :id");
            $stmt->execute([':n' => $new, ':id' => $id]);
            $_SESSION['success'] = 'อัปเดตหมวดหมู่เรียบร้อย ✏️';
        }

        if (isset($_POST['delete_category'])) {
            $id = (int)$_POST['category_id'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'ไม่สามารถลบได้: มีสินค้าผูกกับหมวดหมู่นี้';
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = :id");
                $stmt->execute([':id' => $id]);
                $_SESSION['success'] = 'ลบหมวดหมู่เรียบร้อย 🗑️';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: categories.php");
    exit;
}

$stmt = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>👕 จัดการหมวดหมู่เสื้อผ้า | Street GenZ Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            margin: 0;
            background: linear-gradient(-45deg, #0f0f0f, #1a0033, #32005e, #000);
            background-size: 400% 400%;
            animation: bgAnimate 15s ease infinite;
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
        }

        @keyframes bgAnimate {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .container-box {
            max-width: 1000px;
            margin: auto;
            background: rgba(30, 30, 30, 0.9);
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.2);
            padding: 30px;
        }

        .page-header h2 {
            font-weight: 700;
            color: #fff;
            text-shadow: 0 0 5px #ff00ff;
        }

        .btn-glow {
            background: linear-gradient(90deg, #ff00ff, #8a2be2);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
            transition: all 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.8);
            transform: translateY(-2px);
        }

        .table {
            color: #eee;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background-color: #ff00ff;
            color: #fff;
            text-transform: uppercase;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            background-color: #222;
            color: #fff;
            border: 1px solid #555;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #ff00ff;
            box-shadow: 0 0 10px #ff00ff88;
        }

        .alert {
            border-radius: 10px;
            font-weight: 500;
        }

        .alert-danger {
            background: #ff4d4d;
            color: #fff;
        }

        .alert-success {
            background: #00cc88;
            color: #000;
        }

        a.back-btn {
            color: #ccc;
            text-decoration: none;
        }

        a.back-btn:hover {
            color: #ff00ff;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            font-size: 0.9rem;
            color: #ccc;
        }
    </style>
</head>
<body>

<div class="container-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags-fill"></i> จัดการหมวดหมู่สินค้า</h2>
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left-circle"></i> กลับ</a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_SESSION['error']); ?>
        </div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
    <?php unset($_SESSION['success']); endif; ?>

    <!-- ฟอร์มเพิ่มหมวดหมู่ -->
    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="col-md-8">
            <input type="text" name="category_name" class="form-control" placeholder="เพิ่มชื่อหมวดหมู่ใหม่..." required>
        </div>
        <div class="col-md-4 d-grid">
            <button type="submit" name="add_category" class="btn btn-glow">
                <i class="bi bi-plus-circle-fill"></i> เพิ่มหมวดหมู่
            </button>
        </div>
    </form>

    <!-- ตารางหมวดหมู่ -->
    <div class="table-responsive">
        <table class="table text-center align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อหมวดหมู่</th>
                    <th>แก้ไข</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-muted">ไม่มีหมวดหมู่ในระบบ</td>
                </tr>
            <?php else: foreach ($categories as $i => $cat): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td>
                        <form method="post" class="d-flex justify-content-center gap-2">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                            <input type="text" name="new_name" value="<?= htmlspecialchars($cat['category_name']) ?>" class="form-control" required>
                            <button type="submit" name="update_category" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></button>
                        </form>
                    </td>
                    <td>
                        <form method="post" onsubmit="return confirm('ต้องการลบหมวดหมู่นี้หรือไม่?');">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm"><i class="bi bi-trash3-fill"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    ⚡ Street GenZ Admin Panel © <?= date('Y') ?>
</footer>

</body>
</html>
