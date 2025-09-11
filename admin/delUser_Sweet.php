<?php
    require '../config.php';
    require 'auth.admin.php'; // ตรวจสอบสทิธิ์admin

// ตรวจสอบกำรสง่ ขอ้ มลู จำกฟอรม์
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u_id'])) {
    $user_id = $_POST['u_id'];

// SQL ลบผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
    $stmt->execute([$user_id]);

// สง่ ผลลัพธก์ ลับไปยังหนำ้ 68users.php
    header("Location: user.php");
    exit;
    }
?>
