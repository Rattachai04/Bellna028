<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';
require 'auth.admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$user_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: user.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    $update = $conn->prepare("
        UPDATE users 
        SET full_name = ?, email = ?, username = ?, role = ? 
        WHERE user_id = ?
    ");
    $update->execute([$full_name, $email, $username, $role, $user_id]);

    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🛠️ แก้ไขข้อมูลสมาชิก | Street GenZ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f, #1a0033, #32005e, #000);
            background-size: 400% 400%;
            animation: bgShift 12s ease infinite;
            font-family: 'Kanit', sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes bgShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card {
            background: rgba(30, 30, 30, 0.9);
            border: 2px solid #ff00ff44;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(255, 0, 255, 0.3);
            padding: 30px;
            width: 100%;
            max-width: 650px;
        }

        .card h3 {
            text-align: center;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            text-shadow: 0 0 8px #ff00ff;
        }

        label {
            color: #ffb6ff;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background-color: #1a1a1a;
            color: #fff;
            border: 1px solid #ff00ff33;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #ff00ff;
            box-shadow: 0 0 10px #ff00ff55;
        }

        .btn-glow {
            background: linear-gradient(90deg, #ff00ff, #7700ff);
            border: none;
            color: white;
            font-weight: 600;
            box-shadow: 0 0 15px rgba(255, 0, 255, 0.5);
            transition: all 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 0 25px rgba(255, 0, 255, 0.9);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #444;
            color: #fff;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #666;
        }

        .neon-divider {
            height: 2px;
            background: linear-gradient(to right, #ff00ff, transparent);
            margin: 20px 0;
            border: none;
        }
    </style>
</head>
<body>

<div class="card">
    <h3><i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลสมาชิก</h3>
    <form method="post">
        <div class="mb-3">
            <label for="username">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" class="form-control"
                   value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="full_name">ชื่อ-นามสกุล</label>
            <input type="text" id="full_name" name="full_name" class="form-control"
                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email">อีเมล</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-4">
            <label for="role">สิทธิ์การใช้งาน</label>
            <select name="role" id="role" class="form-select">
                <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>สมาชิก</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>แอดมิน</option>
            </select>
        </div>

        <hr class="neon-divider">

        <div class="d-flex justify-content-between">
            <a href="user.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle"></i> กลับ
            </a>
            <button type="submit" class="btn btn-glow">
                <i class="bi bi-save2-fill"></i> บันทึกการแก้ไข
            </button>
        </div>
    </form>
</div>

</body>
</html>
