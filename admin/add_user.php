<?php
require '../config.php';
require 'auth.admin.php';

// ✅ ตรวจสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ เมื่อส่งฟอร์ม
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ ตรวจสอบข้อมูล
    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }

    // ✅ ตรวจสอบชื่อผู้ใช้ซ้ำ
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "ชื่อผู้ใช้นี้ถูกใช้แล้ว";
    }

    // ✅ บันทึกข้อมูล
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute([$username, $full_name, $email, $hash]);
        header("Location: user.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่ม Admin | Street GenZ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    background: #0d0d0d;
    font-family: 'Kanit', sans-serif;
    color: #fff;
}
.container {
    max-width: 600px;
    margin-top: 80px;
}
.card {
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #ff00ff55;
    box-shadow: 0 0 20px #ff00ff88;
}
.card-header {
    background: linear-gradient(90deg, #ff00ff, #6600cc);
    font-weight: 700;
    font-size: 1.4rem;
    color: #fff;
    text-shadow: 0 0 6px #fff;
}
.btn-primary {
    background: linear-gradient(90deg, #00ffcc, #00aaff);
    border: none;
    font-weight: bold;
    color: #000;
}
.btn-primary:hover {
    box-shadow: 0 0 15px #00ffff;
    transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <i class="bi bi-person-plus-fill"></i> เพิ่มผู้ดูแลระบบ (Admin)
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">อีเมล</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle-fill me-1"></i> เพิ่มผู้ดูแลระบบ
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
