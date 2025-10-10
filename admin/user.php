<?php
require '../config.php';
require 'auth.admin.php';

// ✅ ตรวจสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    header("Location: user.php");
    exit;
}

// ✅ ดึงข้อมูลสมาชิก
$stmt = $conn->query("SELECT user_id, username, full_name, email, role FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>👾 Street GenZ Admin | จัดการสมาชิก</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ===== พื้นหลังนีออนเคลื่อนไหว ===== */
body {
    margin: 0;
    font-family: 'Kanit', sans-serif;
    color: #fff;
    overflow-x: hidden;
    background: linear-gradient(-45deg, #0f0f0f, #1a0033, #32005e, #000);
    background-size: 400% 400%;
    animation: neonMove 10s ease infinite;
}
@keyframes neonMove {
    0% {background-position: 0% 50%;}
    50% {background-position: 100% 50%;}
    100% {background-position: 0% 50%;}
}

/* ===== กล่องหลัก ===== */
.container {
    max-width: 1000px;
    margin-top: 80px;
    z-index: 2;
    position: relative;
}

.card {
    background: rgba(25, 25, 25, 0.85);
    border: 2px solid rgba(255, 0, 255, 0.4);
    border-radius: 18px;
    box-shadow: 0 0 25px rgba(255, 0, 255, 0.3);
    transition: 0.4s ease;
}
.card:hover {
    transform: scale(1.01);
    box-shadow: 0 0 40px rgba(255, 0, 255, 0.5);
}

/* ===== Header ===== */
.card-header {
    background: linear-gradient(90deg, #ff00c8, #8a2be2, #00d4ff);
    color: #fff;
    padding: 25px 30px;
    font-size: 1.5rem;
    font-weight: 700;
    text-shadow: 0 0 10px #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 18px 18px 0 0;
}

/* ===== ตาราง ===== */
.table {
    color: #eee;
    border-collapse: separate;
    border-spacing: 0 10px;
}
.table thead {
    background: rgba(255, 255, 255, 0.08);
    color: #ff9ff3;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.table tbody tr {
    background: rgba(50, 50, 50, 0.8);
    transition: 0.3s;
    border-radius: 10px;
}
.table tbody tr:hover {
    background: rgba(80, 0, 120, 0.8);
    transform: scale(1.02);
}
.table td, .table th {
    vertical-align: middle;
    padding: 14px;
}

/* ===== ปุ่ม Glow 3D ===== */
.btn {
    border-radius: 10px;
    font-weight: 600;
    text-transform: uppercase;
    transition: all 0.25s ease;
    box-shadow: 0 0 10px rgba(255,255,255,0.2);
}

.btn-success {
    background: linear-gradient(90deg, #00ff88, #00ccff);
    border: none;
    color: #000;
    text-shadow: 0 0 5px #fff;
}
.btn-success:hover {
    box-shadow: 0 0 25px #00ffcc;
    transform: translateY(-3px);
}

.btn-warning {
    background: linear-gradient(90deg, #facc15, #f97316);
    border: none;
    color: #000;
    text-shadow: 0 0 5px #fff;
}
.btn-warning:hover {
    box-shadow: 0 0 25px #ffb700;
    transform: translateY(-3px);
}

.btn-danger {
    background: linear-gradient(90deg, #ff0000, #b91c1c);
    border: none;
}
.btn-danger:hover {
    box-shadow: 0 0 25px #ff4d4d;
    transform: translateY(-3px);
}

.btn-secondary {
    background: linear-gradient(90deg, #6b7280, #374151);
    border: none;
}
.btn-secondary:hover {
    box-shadow: 0 0 20px #999;
    transform: translateY(-3px);
}

/* ===== Badge ===== */
.badge {
    font-size: 0.85rem;
    padding: 6px 10px;
    border-radius: 8px;
}
.badge.bg-primary {
    background: linear-gradient(90deg, #6f00ff, #ff00ff);
    color: #fff;
    text-shadow: 0 0 5px #fff;
}

/* ===== เอฟเฟกต์แสง Neon รอบจอ ===== */
.neon-ring {
    position: fixed;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,0,255,0.1) 0%, transparent 70%);
    top: -50%;
    left: -50%;
    animation: spin 30s linear infinite;
    z-index: 0;
    pointer-events: none;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== Footer ===== */
footer {
    text-align: center;
    margin-top: 50px;
    color: #ccc;
    font-size: 0.9rem;
}
footer span {
    color: #ff00ff;
    text-shadow: 0 0 10px #ff00ff;
}
</style>
</head>
<body>

<div class="neon-ring"></div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <span><i class="bi bi-people-fill me-2"></i> 👾 สำหรับแอดมินผู้จัดการระบบ </span>
            <div class="d-flex gap-2">
                <a href="add_user.php" class="btn btn-success">
                    <i class="bi bi-person-plus-fill"></i> ADMIN
                </a>
                <a href="../logout.php" class="btn btn-secondary" onclick="return confirm('ออกจากระบบ?')">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </a>
            </div>
        </div>

        <div class="card-body">
            <?php if (count($users) === 0): ?>
                <div class="alert alert-dark text-center border-0">🚫 ยังไม่มีสมาชิกในระบบ</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $user): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-bold text-info"><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($user['role']) ?></span></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="user.php?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบสมาชิกนี้หรือไม่?')">
                                       <i class="bi bi-trash3-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
