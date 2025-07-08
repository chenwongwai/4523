<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Staff System</title>
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
    </style>
</head>
<body>

    <header class="company-header">
        <span style="color: var(--primary-color);">•</span> Smile & Sunshine Toy Co. Ltd. <span style="color: var(--primary-color);">•</span>
    </header>

    <div class="top-bar">
        <span>👩‍💼 Welcome, <strong><?php echo htmlspecialchars($username); ?></strong></span>
        <form method="POST" action="logout.php" style="margin: 0;">
            <button class="logout-btn" type="submit">Logout</button>
        </form>
    </div>

    <main class="menu-container">
        <h2>Staff System</h2>
        <div class="menu-item" onclick="handleMenu(1)">1. Insert items’ information</div>
        <div class="menu-item" onclick="handleMenu(2)">2. Insert materials’ information</div>
        <div class="menu-item" onclick="handleMenu(3)">3. Update order records and related material record</div>
        <div class="menu-item" onclick="handleMenu(4)">4. Generate report</div>
        <div class="menu-item" onclick="handleMenu(5)">5. Delete Item</div>
    </main>

    <script>
        function handleMenu(option) {
            switch (option) {
                case 1:
                    window.location.href = "product_list.php";
                    break;
                case 2:
                    window.location.href = "material_list.php";
                    break;
                case 3:
                    window.location.href = "update_records.php";
                    break;
                case 4:
                    window.location.href = "generate_report.php";
                    break;
                case 5:
                    window.location.href = "delete_item.php";
                    break;
                default:
                    console.log("Invalid option");
            }
        }
    </script>
</body>
</html>