<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "projectdb");

function fetch_orders($keyword) {
    global $conn;
    $keyword = "%$keyword%";
    $sql = "SELECT o.oid, o.odate, o.oqty, o.ocost, o.ostatus, o.pid,
                   c.cname AS customer_name,
                   p.pname AS product_name
            FROM orders o
            JOIN customer c ON o.cid = c.cid
            JOIN product p ON o.pid = p.pid
            WHERE o.oid LIKE ? OR c.cname LIKE ?
            ORDER BY o.odate DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function update_order_and_material($oid) {
    global $conn;
    $q = $conn->prepare("SELECT pid, oqty FROM orders WHERE oid=? AND ostatus<>3");
    $q->bind_param("i", $oid);
    $q->execute();
    $res = $q->get_result();
    if (!$row = $res->fetch_assoc()) return "The order does not exist or has been completed";

    $pid = $row['pid'];
    $oqty = $row['oqty'];

    $mat = $conn->prepare("SELECT mid, pmqty FROM prodmat WHERE pid=?");
    $mat->bind_param("i", $pid);
    $mat->execute();
    $materials = $mat->get_result()->fetch_all(MYSQLI_ASSOC);

    $conn->begin_transaction();
    try {
        foreach ($materials as $m) {
            $used = $oqty * $m['pmqty'];
            $conn->query("UPDATE material SET mqty = mqty - $used WHERE mid = {$m['mid']}");
        }
        $conn->query("UPDATE orders SET ostatus = 3 WHERE oid = $oid");
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return "Update failed：" . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_oid'])) {
    $result = update_order_and_material(intval($_POST['update_oid']));
    if ($result === true) {
        $toast = "✅ The order has been updated and the raw materials have been deducted";
    } else {
        $toast = "❌ $result";
    }
}

$orders = [];
if (!empty($_GET['q'])) {
    $orders = fetch_orders($_GET['q']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Orders + Materials</title>
    <link rel="stylesheet" href="common.css">
    <style>
        body { font-family: 'Segoe UI'; background: #f0f2f5; padding: 40px; }
        .container { max-width: 900px; margin: auto; }
        .input-group { padding: 10px; border: 1px solid #ccc; border-radius: 6px; width: 100%; }
        .btn { padding: 10px 16px; border-radius: 6px; cursor: pointer; }
        .btn-primary { background: #4CAF50; color: white; border: none; }
        .btn-secondary { background: #eee; border: none; }
        .btn-group { display: flex; gap: 1rem; margin-top: 1rem; }
        .record-card {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .toast {
            position: fixed; bottom: 30px; left: 50%;
            transform: translateX(-50%);
            background: #4CAF50; color: white;
            padding: 12px 24px; border-radius: 20px;
            font-weight: bold; z-index: 999;
        }
        .toast.error { background: #e74c3c; }
    </style>
</head>
<body>
    <div style="margin-bottom: 1rem;">
        <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
    </div>
<div class="container">
    <h2>📦 Order update and material deduction</h2>
    <form method="GET" style="display:flex; gap:1rem; margin-bottom:2rem">
        <input type="text" name="q" class="input-group" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Enter order number or customer name">
        <button class="btn btn-primary">search</button>
    </form>

    <?php foreach ($orders as $o): ?>
        <div class="record-card">
            <h3>#<?= $o['oid'] ?> - <?= htmlspecialchars($o['customer_name']) ?></h3>
            <p>🧸 commodity：<?= htmlspecialchars($o['product_name']) ?></p>
            <p>📦 quantity：<?= $o['oqty'] ?>　💰 Amount：HK$<?= $o['ocost'] ?></p>
            <p>🕐 state：<?= $o['ostatus'] == 3 ? "<span style='color:green;font-weight:bold'>✅ Completed</span>" : "Processing" ?></p>
            <?php if ($o['ostatus'] != 3): ?>
                <form method="POST">
                    <input type="hidden" name="update_oid" value="<?= $o['oid'] ?>">
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="return confirm('Confirm shipment？')">Shipping</button>
                    </div>
                </form>
            <?php endif ?>
        </div>
    <?php endforeach ?>

    <?php if (isset($toast)): ?>
        <div class="toast"><?= htmlspecialchars($toast) ?></div>
        <script>setTimeout(() => document.querySelector('.toast').remove(), 3000);</script>
    <?php endif ?>
</div>
</body>
</html>