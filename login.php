<?php 
// ✅ ทำให้ session ใช้งานได้ในทุกโฟลเดอร์
ini_set('session.cookie_path', '/');
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE (username = ? OR email = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] === 'admin'){
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ | Midnight Bloom Shop</title>

<!-- Bootstrap & Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
    background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
    font-family: 'Poppins', sans-serif;
    color: #fff;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-card {
    background: #121212;
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(30, 192, 246, 0.4);
    width: 100%;
    max-width: 420px;
    text-align: center;
}

.login-card h3 {
    margin-bottom: 30px;
    font-weight: 700;
    color: #b73cffff;
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
    border-color: #efff3cff;
    box-shadow: 0 0 8px #ffd53cff;
    background: #1e1e1e;
    color: #fff;
}

.btn-login {
    background: linear-gradient(135deg, #663cffff, #30fdfdff);
    border: none;
    border-radius: 25px;
    font-size: 1.1rem;
    padding: 10px 25px;
    width: 100%;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.btn-login:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 5px 15px rgba(255, 60, 110, 0.5);
}

.btn-register {
    display: block;
    text-align: center;
    margin-top: 12px;
    color: #59f2ff;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}

.btn-register:hover {
    color: #1ac4ff;
    text-decoration: underline;
}

.alert {
    max-width: 420px;
    margin: 20px auto;
    border-radius: 12px;
}

</style>
</head>

<body>

<?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
    <div class="alert alert-success text-center shadow-sm">✅ สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger text-center shadow-sm">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="login-card">
    <h3><i class="bi bi-shield-lock-fill"></i> เข้าสู่ระบบ</h3>
    <form method="post" class="row g-3">
        <div class="col-12">
            <label for="username_or_email" class="form-label"><i class="bi bi-person-fill"></i> ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
        </div>
        <div class="col-12">
            <label for="password" class="form-label"><i class="bi bi-key-fill"></i> รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-login"><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</button>
            <a href="register.php" class="btn-register"><i class="bi bi-pencil-square"></i> สมัครสมาชิก</a>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
