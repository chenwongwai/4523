<?php
session_start();
if (isset($_SESSION['username'])) {
    $role = $_SESSION['role'];
    header("Location: " . ($role === 'customer' ? 'customer.php' : 'staff.php'));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Smile & Sunshine Toy Co. Ltd. - Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .company-header {
            position: absolute;
            top: 30px;
            width: 100%;
            text-align: center;
            font-size: 28px;
            color: #2c3e50;
            font-weight: 600;
            letter-spacing: 2px;
        }

        .login-box {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            width: 360px;
            margin-top: 60px;
        }

        .user-type {
            margin: 20px 0;
            display: flex;
            gap: 25px;
            justify-content: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .user-type label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: #555;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-size: 14px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-group input:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        button[type="submit"]:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="company-header">
        <span style="color: #4CAF50;">•</span> Smile & Sunshine Toy Co. Ltd. <span style="color: #4CAF50;">•</span>
    </div>

    <div class="login-box">
        <h2 style="text-align: center; margin-bottom: 5px;">Welcome</h2>
        <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 25px;">Please select user type and log in</p>

        <form method="POST" action="login.php">
            <div class="user-type">
                <label><input type="radio" name="userType" value="customer" checked> Customer</label>
                <label><input type="radio" name="userType" value="staff"> Staff</label>
            </div>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit">Login</button>
        </form>
    </div>

</body>
</html>