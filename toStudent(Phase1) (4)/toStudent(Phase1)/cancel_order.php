<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['orderId'])) {
    echo json_encode(['error' => 'Missing order number']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$orderId = intval($_POST['orderId']);

$username = $_SESSION['username'];
$res = $conn->query("SELECT cid FROM customer WHERE cname = '$username'");
$cid = $res->fetch_assoc()['cid'];

$check = $conn->query("SELECT * FROM orders WHERE oid = $orderId AND cid = $cid");
if ($check->num_rows === 0) {
    echo json_encode(['error' => 'This order cannot be found']);
    exit();
}

$conn->query("UPDATE orders SET ostatus = 4 WHERE oid = $orderId");
echo json_encode(['success' => true]);
?>