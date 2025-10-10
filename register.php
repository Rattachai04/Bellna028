<?php
require_once 'config.php';

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirmpassword)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif ($password !== $confirmpassword) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
        }
    }

    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, full_name, email, password, role) 
                VALUES (?, ?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);
        header("Location: login.php?register=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>สมัครสมาชิก | Midnight Bloom Shop</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
    font-family: 'Poppins', sans-serif;
    color: #fff;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.register-container {
    background: #121212;
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(60, 180, 255, 0.4);
    width: 100%;
    max-width: 500px;
    text-align: center;
}

.register-container h2 {
    margin-bottom: 30px;
    font-weight: 700;
    color: #8a3cffff;
    font-size: 2rem;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
}

.form-label {
    font-weight: 500;
    color: #ccc;
}

.form-control {
    border-radius: 12px;
    padding: 12px;
    background: #1e1e1e;
    border: 1px solid #333;
    color: #fff;
}

.form-control:focus {
    border-color: #a43cffff;
    box-shadow: 0 0 8px #3cbbffff;
    background: #1e1e1e;
    color: #fff;
}

.btn-register {
    background: linear-gradient(135deg, #3cb4ffff, #6385ffff);
    border: none;
    border-radius: 25px;
    font-size: 1.1rem;
    padding: 10px 25px;
    width: 100%;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.btn-register:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 5px 15px rgba(60, 255, 255, 0.5);
}

.btn-login-link {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: #59f2ff;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}

.btn-login-link:hover {
    color: #1ac4ff;
    text-decoration: underline;
}

.alert {
    border-radius: 12px;
    max-width: 500px;
    margin: 20px auto;
}
</style>
</head>
<body>

<?php if (!empty($error)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($error as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="register-container">
    <h2><i class="bi bi-person-plus-fill"></i> สมัครสมาชิก</h2>
    <form method="post" class="row g-3">
        <div class="col-12">
            <label for="username" class="form-label"><i class="bi bi-person-fill"></i> ชื่อผู้ใช้</label>
            <input type="text" name="username" id="username" class="form-control"
                placeholder="กรุณากรอกชื่อผู้ใช้"
                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
        </div>

        <div class="col-12">
            <label for="fullname" class="form-label"><i class="bi bi-card-text"></i> ชื่อ-สกุล</label>
            <input type="text" name="fullname" id="fullname" class="form-control"
                placeholder="กรุณากรอกชื่อ-สกุล"
                value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
        </div>

        <div class="col-12">
            <label for="email" class="form-label"><i class="bi bi-envelope-fill"></i> อีเมล</label>
            <input type="email" name="email" id="email" class="form-control"
                placeholder="กรุณากรอกอีเมล"
                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
        </div>

        <div class="col-12">
            <label for="password" class="form-label"><i class="bi bi-key-fill"></i> รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control"
                placeholder="กรุณากรอกรหัสผ่าน" required>
        </div>

        <div class="col-12">
            <label for="confirmpassword" class="form-label"><i class="bi bi-shield-lock-fill"></i> ยืนยันรหัสผ่าน</label>
            <input type="password" name="confirmpassword" id="confirmpassword" class="form-control"
                placeholder="กรุณายืนยันรหัสผ่าน" required>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-register"><i class="bi bi-check-circle-fill"></i> สมัครสมาชิก</button>
            <a href="login.php" class="btn-login-link"><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
