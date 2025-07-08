<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$username = $_SESSION['username'];

$res = $conn->query("SELECT cid FROM customer WHERE cname = '$username'");
$cid = $res->fetch_assoc()['cid'];

$sql = "SELECT o.*, p.pname 
        FROM orders o 
        JOIN product p ON o.pid = p.pid 
        WHERE o.cid = $cid 
        ORDER BY o.odate DESC";
$result = $conn->query($sql);

function statusClass($status) {
    return match($status) {
        1 => 'status-processing',
        2 => 'status-shipped',
        3 => 'status-completed',
        4 => 'status-cancelled',
        default => 'status-processing'
    };
}
function statusText($status) {
    return match($status) {
        1 => 'Processing',
        2 => 'Shipped',
        3 => 'Completed',
        4 => 'Cancelled',
        default => 'Unkown'
    };
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Order | Customer Center</title>
    <link rel="stylesheet" href="common.css">
    <style>
        .order-tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            position: relative;
            font-weight: 500;
        }

        .tab.active {
            color: var(--primary-color);
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-color);
        }

        .order-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin: 1rem 0;
            box-shadow: var(--box-shadow);
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .status-processing {
            background: #fff3cd;
            color: #856404;
        }

        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .order-summary {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .summary-item {
            background: #f8f9fa;
            padding: 0.8rem;
            border-radius: 5px;
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-left">
                <button class="nav-back-btn" onclick="history.back()">←</button>
                <div class="breadcrumb">
                    <span>Customer Centre</span><span class="divider">/</span><span>Delete order</span>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-avatar">C</div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="page-title">My Order History</h2>
        <div class="customer-order-list" id="orderList">
        <?php if ($result->num_rows > 0): ?>
            <?php while($o = $result->fetch_assoc()): 
                $statusClass = statusClass($o['ostatus']);
                $statusText = statusText($o['ostatus']);
                $isCancellable = $o['ostatus'] == 1;
            ?>
                <div class="order-card <?= $isCancellable ? 'cancellable' : '' ?>" data-id="<?= $o['oid'] ?>">
                    <div class="order-header">
                        <h3>Order #<?= $o['oid'] ?></h3>
                        <span class="order-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                    <div class="order-details">
                        <div class="detail-item"><strong>Order date：</strong> <?= $o['odate'] ?></div>
                        <div class="detail-item"><strong>Product Name：</strong> <?= $o['pname'] ?></div>
                        <div class="detail-item"><strong>quantity：</strong> <?= $o['oqty'] ?> 件</div>
                        <div class="detail-item"><strong>lump sum：</strong> HK$ <?= number_format($o['ocost'], 2) ?></div>
                        <div class="detail-item"><strong>Estimated delivery：</strong> <?= $o['odeliverdate'] ?? 'To be confirmed' ?></div>
                    </div>
                    <?php if ($isCancellable): ?>
                    <div class="btn-group" style="margin-top:1rem">
                        <button class="btn btn-cancel" onclick="confirmCancel(<?= $o['oid'] ?>)">Cancellation of order</button>
                    </div>
                    <?php endif ?>
                </div>
            <?php endwhile ?>
        <?php else: ?>
            <div class="empty-orders">
                <h3>No orders at the moment</h3>
                <p>You haven't placed any orders yet</p>
                <button class="btn btn-primary" onclick="location.href='Make_Order.php'">Order now</button>
            </div>
        <?php endif ?>
        </div>
    </div>

    <script>
        function confirmCancel(orderId) {
            if(confirm(`Confirm to cancel the order #${orderId} ？`)) {
                fetch('cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `orderId=${orderId}`
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        showToast('✅ Order successfully cancelled', 'success');
                        location.reload();
                    } else {
                        showToast(data.error || 'Cancellation failed', 'error');
                    }
                });
            }
        }

        function showToast(msg, type) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>

    <style>
        .toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 20px;
            font-weight: 500;
            color: white;
            z-index: 999;
        }
        .toast.success { background: #4CAF50; }
        .toast.error { background: #e74c3c; }
    </style>
</body>
</html>