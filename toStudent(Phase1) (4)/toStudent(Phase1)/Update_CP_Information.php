<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$username = $_SESSION['username'];

$res = $conn->query("SELECT * FROM customer WHERE cname = '$username'");
$customer = $res->fetch_assoc();

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tel = $_POST['phone'];
    $addr = $_POST['address'];
    $company = $_POST['company'];
    $currentPw = $_POST['currentPassword'];
    $newPw = $_POST['newPassword'];
    $confirmPw = $_POST['confirmPassword'];

    $updates = ["ctel = '$tel'", "caddr = '$addr'", "company = '$company'"];

    if (!empty($newPw)) {
        if ($newPw !== $confirmPw) {
            $errors[] = "The new password and the confirmed password do not match";
        } elseif ($currentPw !== $customer['cpassword']) {
            $errors[] = "The current password is incorrect";
        } else {
            $updates[] = "cpassword = '$newPw'";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE customer SET " . implode(", ", $updates) . " WHERE cid = " . $customer['cid'];
        if ($conn->query($sql)) {
            $success = true;
            $res = $conn->query("SELECT * FROM customer WHERE cname = '$username'");
            $customer = $res->fetch_assoc();
        } else {
            $errors[] = "Update failed, please try again later";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-HK">
<head>
    <meta charset="UTF-8">
    <title>Update Profile | Customer Center</title>
    <link rel="stylesheet" href="common.css">
    <style>
        .form-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .input-group { margin-bottom: 1rem; }
        label { font-weight: bold; display: block; margin-bottom: 6px; }
        .btn-group { display: flex; gap: 1rem; margin-top: 2rem; }
        .toast {
            position: fixed; bottom: 20px; left: 50%;
            transform: translateX(-50%);
            background: #4CAF50; color: white;
            padding: 12px 24px; border-radius: 20px;
            z-index: 999; font-weight: bold;
        }
        .toast.error { background: #e74c3c; }
        .password-strength { height: 6px; background: #eee; border-radius: 4px; margin-top: 6px; }
        .strength-meter { height: 100%; width: 0; transition: 0.3s; }
    </style>
</head>
<body>
    <?php if ($success): ?>
        <div class="toast">✅ Data updated successfully</div>
    <?php elseif (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="toast error"><?= htmlspecialchars($e) ?></div>
        <?php endforeach ?>
    <?php endif ?>

    <div class="container">
        <h2 class="page-title">Update your personal information</h2>
        <form method="POST">
            <div class="form-section">
                <h3>Contact Information</h3>
                <div class="form-grid">
                    <div class="input-group">
                        <label for="phone">telephone number</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($customer['ctel']) ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="address">address</label>
                        <input type="text" id="address" name="address" value="<?= htmlspecialchars($customer['caddr']) ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="company">Company Name</label>
                        <input type="text" id="company" name="company" value="<?= htmlspecialchars($customer['company']) ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Change Password</h3>
                <div class="input-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" placeholder="Please enter your current password">
                </div>
                <div class="form-grid">
                    <div class="input-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="newPassword" placeholder="New Password" oninput="checkPasswordStrength(this.value)">
                        <div class="password-strength"><div class="strength-meter" id="strengthMeter"></div></div>
                    </div>
                    <div class="input-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter new password">
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">💾 Storage changes</button>
                <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function checkPasswordStrength(pw) {
            const meter = document.getElementById('strengthMeter');
            let score = 0;
            if (pw.length > 7) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[a-z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            let width = score * 20;
            let color = '#e74c3c';
            if (score >= 4) color = '#f1c40f';
            if (score >= 5) color = '#2ecc71';
            meter.style.width = width + '%';
            meter.style.background = color;
        }

        setTimeout(() => {
            document.querySelectorAll('.toast').forEach(t => t.remove());
        }, 3500);
    </script>
</body>
</html>