
<!DOCTYPE html>
<?php
    session_start();
        require '../config.php'; // เชื่อมต่อฐานข้อมูล
        require_once 'auth.admin.php';
    // ลบสมำชกิ
        if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // ป้องกันลบตัวเอง
        if ($user_id != $_SESSION['user_id']) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
    $stmt->execute([$user_id]);
}
    header("Location: user.php");
    exit;
    }
    // ดึงข้อมูลสมาชิก
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดกำรสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    background: linear-gradient(to right, #b46bf8ff, #8dfef7ff);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

h2 {
    font-weight: 700;
    color: #0d6efd;
}

.card {
    border-radius: 20px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.table-responsive {
    margin-top: 15px;
}

table {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

thead {
    background: linear-gradient(45deg, #0d6efd, #3a8dff);
    color: #fff;
    font-weight: 600;
}

th, td {
    vertical-align: middle;
    font-size: 0.95rem;
}

.btn {
    border-radius: 25px;
    padding: 6px 14px;
    transition: transform 0.2s, box-shadow 0.2s;
    font-size: 0.85rem;
}

.btn-sm {
    padding: 5px 12px;
}

.btn-warning {
    background: linear-gradient(45deg, #ffc107, #ffca2c);
    color: #212529;
    border: none;
}

.btn-danger {
    background: linear-gradient(45deg, #dc3545, #e4606d);
    border: none;
    color: #fff;
}

.btn-secondary {
    background: #6c757d;
    color: #fff;
    border: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.alert-warning {
    font-size: 1rem;
    border-radius: 15px;
    padding: 15px;
}

@media (max-width: 576px) {
    table, thead, tbody, th, td, tr {
        font-size: 0.85rem;
    }
    .btn {
        font-size: 0.75rem;
        padding: 4px 10px;
    }
}
</style>

</head>
<body class="container mt-4">
    <h2 class="mb-3">📋 จัดการสมาชิก</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>
<?php if (count($users) === 0): ?>
    <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
<?php else: ?>
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered text-center align-middle">
        <thead>
        <tr>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-นามสกุล</th>
            <th>อีเมล</th>
            <th>วันที่สมัคร</th>
            <th>จัดการ</th>
        </tr>
        </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">✏️ แก้ไข</a>
        <a href="users.php?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger"
    onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')">🗑️ ลบ</a>
        </td>
    </tr>
    <?php endforeach; ?>
        </tbody>
            </table>
    </div>
<?php endif; ?>
</body>
</html>
