<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");

$username = $_SESSION['username'];
$result = $conn->query("SELECT cid FROM customer WHERE cname = '$username'");
$row = $result->fetch_assoc();
$cid = $row['cid'];

$sql = "SELECT o.*, p.pname 
        FROM orders o 
        JOIN product p ON o.pid = p.pid 
        WHERE o.cid = $cid 
        ORDER BY o.odate DESC";
$orders = $conn->query($sql);
function statusClass($status) {
    return match($status) {
        1 => 'status-processing',
        2 => 'status-shipped',
        3 => 'status-completed',
        4 => 'status-cancelled',
        default => 'status-processing'
    };
}
function statusLabel($status) {
    return match($status) {
        1 => 'Processing',
        2 => 'Shipped',
        3 => 'Completed',
        4 => 'Cancelled',
        default => 'unknown'
    };
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Order History | Smile & Sunshine Toy Co.</title>
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

        .empty-orders {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(245,245,245,0.5);
            border-radius: var(--border-radius);
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-summary {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
            
            .order-actions .btn {
                width: 100%;
            }
        }
    </style>

</head>
<body>
    <nav class="navbar">
        <div class="nav-content">
            <div class="nav-left">
                <button class="nav-back-btn" onclick="history.back()">←</button>
                <div class="breadcrumb">
                    <span>Customer Center</span><span class="divider">/</span><span>My Orders</span>
                </div>
            </div>
            <div class="nav-right">
                <div class="user-avatar">C</div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="page-title">My Order records</h2>
        <div class="order-tabs">
            <div class="tab active" onclick="filterOrders('all')">All orders</div>
        </div>

        <div class="order-list" id="orderList">
        <?php if ($orders->num_rows > 0): ?>
            <?php while($o = $orders->fetch_assoc()): ?>
                <?php
                $orderId = $o['oid'];
                $statusClass = statusClass($o['ostatus']);
                $statusText = statusLabel($o['ostatus']);
                ?>
                <div class="order-card" data-status="<?= statusClass($o['ostatus']) ?>">
                    <div class="order-header">
                        <h3>Order #<?= $orderId ?></h3>
                        <span class="order-status <?= $statusClass ?>"><?= $statusText ?></span>
                    </div>
                    <div class="order-summary">
                        <div class="summary-item"><strong>Order date：</strong> <?= $o['odate'] ?></div>
                        <div class="summary-item"><strong>commodity：</strong> <?= $o['pname'] ?></div>
                        <div class="summary-item"><strong>quantity：</strong> <?= $o['oqty'] ?></div>
                        <div class="summary-item"><strong>lump sum：</strong> HK$ <?= number_format($o['ocost'], 2) ?></div>
                        <div class="summary-item"><strong>Estimated delivery：</strong> <?= $o['odeliverdate'] ?? '待確認' ?></div>
                    </div>
                    <div class="order-actions">
                        <button class="btn btn-primary" onclick="viewOrderDetails('<?= $orderId ?>')">check the details</button>
                        <?php if ($o['ostatus'] === '1'): ?>
                        <button class="btn btn-secondary" onclick="cancelOrder('<?= $orderId ?>')">Cancellation of order</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile ?>
        <?php else: ?>
            <div class="empty-orders" id="emptyState">
                <h3>There is no order record at the moment</h3>
                <p>You haven't placed any orders yet</p>
                <button class="btn btn-primary" onclick="location.href='Make_Order.php'">Shop Now</button>
            </div>
        <?php endif ?>
        </div>
    </div>

    <script>
        function filterOrders(status) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.currentTarget.classList.add('active');
            const cards = document.querySelectorAll('.order-card');
            let hasVisible = false;
            cards.forEach(card => {
                const s = card.dataset.status;
                if (status === 'all' || s === status) {
                    card.style.display = 'block';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });
            document.getElementById('emptyState').style.display = hasVisible ? 'none' : 'block';
        }
        function viewOrderDetails(orderId) {
            location.href = `order_detail.php?id=${orderId}`;
        }
        function cancelOrder(orderId) {
            if(confirm(`Are you confirm to cancel the order ${orderId} ？`)) {
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `orderId=${orderId}`
                    })
                    .then(res => res.json())
                    .then(data => {
                    if (data.success) {
                        const cardList = document.querySelectorAll('.order-card');
                        cardList.forEach(card => {
                        if (card.querySelector('h3').textContent.includes(`#${orderId}`)) {
                            const status = card.querySelector('.order-status');
                            status.textContent = 'Cancelled';
                            status.className = 'order-status status-cancelled';
                            const cancelBtn = card.querySelector('.btn-secondary');
                            if (cancelBtn) cancelBtn.remove();
                        }
                        });
                        showToast('✅ Order successfully cancelled', 'success');
                        } else {
                            showToast(data.error || 'Cancellation failed', 'error');
                    }
                });
            }
        }
    </script>
</body>
</html>