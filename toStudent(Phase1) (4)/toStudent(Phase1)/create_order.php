<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['error' => '未授權']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

if (empty($items)) {
    echo json_encode(['error' => 'Shopping cart is empty']);
    exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT cid FROM customer WHERE cname = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$cid = $res->fetch_assoc()['cid'] ?? 0;
$stmt->close();

if ($cid <= 0) {
    echo json_encode(['error' => 'No customers found']);
    exit();
}

$total = 0;
foreach ($items as $item) {
    $pid = intval($item['id']);
    $qty = intval($item['quantity']);
    $res = $conn->query("SELECT pcost FROM product WHERE pid = $pid");
    if ($row = $res->fetch_assoc()) {
        $total += $row['pcost'] * $qty;
    }
}

$stmt = $conn->prepare("INSERT INTO orders (cid, pid, odate, oqty, ocost, ostatus) VALUES (?, ?, NOW(), ?, ?, 1)");

foreach ($items as $item) {
    $pid = intval($item['id']);
    $qty = intval($item['quantity']);
    $cost = 0;
    $res = $conn->query("SELECT pcost FROM product WHERE pid = $pid");
    if ($row = $res->fetch_assoc()) {
        $cost = $row['pcost'] * $qty;
    }
    $stmt->bind_param("iiid", $cid, $pid, $qty, $cost);
    $stmt->execute();
}

$stmt->close();

echo json_encode(['success' => true, 'message' => 'Order placed (not paid)']);
?>