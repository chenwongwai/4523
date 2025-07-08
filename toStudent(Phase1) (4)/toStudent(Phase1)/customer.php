<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer System</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --staff-accent: #2196F3;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            padding: 30px;
            position: relative;
        }

        .company-header {
            position: absolute;
            top: 30px;
            width: 100%;
            text-align: center;
            font-size: 28px;
            color: var(--text-dark);
            font-weight: 600;
            letter-spacing: 2px;
        }

        .menu-container {
            max-width: 800px;
            margin: 120px auto 50px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .menu-item {
            padding: 15px;
            margin: 10px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .menu-item:hover {
            transform: translateX(10px);
            border-color: var(--primary-color);
            background: #f8fff8;
        }

        .menu-item::after {
            content: "➔";
            position: absolute;
            right: 15px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .menu-item:hover::after {
            opacity: 1;
        }

        .top-bar {
            position: absolute;
            top: 30px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-bar span {
            font-size: 16px;
            color: var(--text-dark);
        }

        .logout-btn {
            padding: 6px 14px;
            border: 1px solid var(--primary-color);
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            color: var(--primary-color);
            font-weight: 500;
        }

        .logout-btn:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>

    <header class="company-header">
        <span style="color: var(--primary-color);">•</span> Smile & Sunshine Toy Co. Ltd. <span style="color: var(--primary-color);">•</span>
    </header>

    <div class="top-bar">
        <span>👋 Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
        <form action="logout.php" method="POST" style="margin: 0;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <main class="menu-container">
        <h2>Customer System</h2>
        <div class="menu-item" onclick="handleMenu(1)">1. Make the orders</div>
        <div class="menu-item" onclick="handleMenu(2)">2. View the order records</div>
        <div class="menu-item" onclick="handleMenu(3)">3. Update Customer profile’s information</div>
        <div class="menu-item" onclick="handleMenu(4)">4. Delete Order record</div>
    </main>

    <script>
        function handleMenu(option) {
            switch (option) {
                case 1:
                    window.location.href = "Make_Order.php";
                    break;
                case 2:
                    window.location.href = "View_order_record.php";
                    break;
                case 3:
                    window.location.href = "Update_CP_Information.php";
                    break;
                case 4:
                    window.location.href = "DOrder.php";
                    break;
                default:
                    console.log("Invalid option");
            }
        }
    </script>

</body>
</html>