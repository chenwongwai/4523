<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "No order number provided";
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$orderId = intval($_GET['id']);
$username = $_SESSION['username'];

$res = $conn->query("SELECT cid FROM customer WHERE cname = '$username'");
$cid = $res->fetch_assoc()['cid'];

$sql = "SELECT o.*, p.pname, c.caddr, c.ctel 
        FROM orders o 
        JOIN product p ON o.pid = p.pid 
        JOIN customer c ON o.cid = c.cid 
        WHERE o.oid = $orderId AND o.cid = $cid";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    echo "The order does not exist or does not belong to you";
    exit();
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order details #<?= $order['oid'] ?></title>
    <link rel="stylesheet" href="common.css">
    <style>
        .detail-box { max-width: 600px; margin: 80px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: var(--box-shadow); }
        .detail-box h2 { margin-bottom: 20px; }
        .detail-item { margin: 12px 0; }
        .label { font-weight: 600; display: inline-block; width: 120px; color: #555; }
    </style>
</head>
<body>
    <div class="detail-box">
        <h2>Order details</h2>
        <div class="detail-item"><span class="label">Order Number：</span> #<?= $order['oid'] ?></div>
        <div class="detail-item"><span class="label">Product Name：</span> <?= $order['pname'] ?></div>
        <div class="detail-item"><span class="label">Order date：</span> <?= $order['odate'] ?></div>
        <div class="detail-item"><span class="label">quantity：</span> <?= $order['oqty'] ?> pieces</div>
        <div class="detail-item"><span class="label">lump sum：</span> HK$ <?= number_format($order['ocost'], 2) ?></div>
        <div class="detail-item"><span class="label">Estimated delivery：</span> <?= $order['odeliverdate'] ?? 'To be confirmed' ?></div>
        <div class="detail-item"><span class="label">Shipping address：</span> <?= $order['caddr'] ?></div>
        <div class="detail-item"><span class="label">Contact Number：</span> <?= $order['ctel'] ?></div>
    </div>
</body>
</html>