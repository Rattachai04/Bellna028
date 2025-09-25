<?php
session_start();
require 'config.php';
require 'auth.admin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ดึงคำสั่งซื้อทั้งหมด
$stmt = $pdo->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ฟังก์ชันดึงสินค้าในคำสั่งซื้อ
function getOrderItems($pdo, $order_id) {
    $stmt = $pdo->prepare("
        SELECT oi.quantity, oi.price, p.product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ฟังก์ชันดึงข้อมูลการจัดส่ง
function getShippingInfo($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT * FROM shipping WHERE order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// อัปเดตสถานะ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header("Location: orders.php?order_id=" . $_POST['order_id']);
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $pdo->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        header("Location: orders.php?order_id=" . $_POST['order_id']);
        exit;
    }
}

// เช็คว่ามี order ที่เลือกหรือไม่
$selected_order = null;
$shipping = null;
if (isset($_GET['order_id'])) {
    $stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
    $stmt->execute([$_GET['order_id']]);
    $selected_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selected_order) {
        $shipping = getShippingInfo($pdo, $selected_order['order_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<!-- กล่องที่ 1: รายการคำสั่งซื้อ -->
<div class="card mb-4">
    <div class="card-body">
        <h4>คำสั่งซื้อทั้งหมด</h4>
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>
        <div class="list-group">
            <?php foreach ($orders as $order): ?>
                <a href="orders.php?order_id=<?= $order['order_id'] ?>" 
                   class="list-group-item list-group-item-action <?= (isset($_GET['order_id']) && $_GET['order_id'] == $order['order_id']) ? 'active' : '' ?>">
                    คำสั่งซื้อ #<?= $order['order_id'] ?> | <?= htmlspecialchars($order['username']) ?> | <?= $order['order_date'] ?> 
                    | สถานะ: <span class="badge bg-info text-dark"><?= ucfirst($order['status']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- กล่องที่ 2: รายละเอียดคำสั่งซื้อ -->
<?php if ($selected_order): ?>
<div class="card">
    <div class="card-body">
        <h4>คำสั่งซื้อทั้งหมด</h4>
        <a href="orders.php" class="btn btn-secondary mb-3">← กลับหน้าคำสั่งซื้อทั้งหมด</a>

        <div class="accordion" id="orderDetailAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingDetail">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetail" aria-expanded="true" aria-controls="collapseDetail">
                        คำสั่งซื้อ #<?= $selected_order['order_id'] ?> | <?= htmlspecialchars($selected_order['username']) ?> | <?= $selected_order['order_date'] ?> | 
                        <span class="badge bg-info text-dark"><?= ucfirst($selected_order['status']) ?></span>
                    </button>
                </h2>
                <div id="collapseDetail" class="accordion-collapse collapse show" aria-labelledby="headingDetail" data-bs-parent="#orderDetailAccordion">
                    <div class="accordion-body">
                        
                        <!-- รายการสินค้า -->
                        <h5>รายการสินค้า</h5>
                        <ul class="list-group mb-3">
                            <?php foreach (getOrderItems($pdo, $selected_order['order_id']) as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                                    <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p><strong>ยอดรวม:</strong> <?= number_format($selected_order['total_amount'], 2) ?> บาท</p>

                        <!-- อัปเดตสถานะ -->
                        <form method="post" class="row g-2 mb-3">
                            <input type="hidden" name="order_id" value="<?= $selected_order['order_id'] ?>">
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <?php
                                    $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                    foreach ($statuses as $status) {
                                        $selected = ($selected_order['status'] === $status) ? 'selected' : '';
                                        echo "<option value=\"$status\" $selected>$status</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="update_status" class="btn btn-primary">อัปเดตสถานะ</button>
                            </div>
                        </form>

                        <!-- ข้อมูลการจัดส่ง -->
                        <?php if ($shipping): ?>
                            <h5>ข้อมูลจัดส่ง</h5>
                            <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                            <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                            <form method="post" class="row g-2">
                                <input type="hidden" name="order_id" value="<?= $selected_order['order_id'] ?>">
                                <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                <div class="col-md-4">
                                    <select name="shipping_status" class="form-select">
                                        <?php
                                        $s_statuses = ['not_shipped', 'shipped', 'delivered'];
                                        foreach ($s_statuses as $s) {
                                            $selected = ($shipping['shipping_status'] === $s) ? 'selected' : '';
                                            echo "<option value=\"$s\" $selected>$s</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="update_shipping" class="btn btn-success">อัปเดตการจัดส่ง</button>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
