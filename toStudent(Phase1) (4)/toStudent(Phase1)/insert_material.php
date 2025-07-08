<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mname = trim($_POST['mname']);
    $mqty = intval($_POST['mqty']);
    $mrqty = intval($_POST['mrqty']);
    $munit = trim($_POST['munit']);
    $mreorderqty = intval($_POST['mreorderqty']);

    if (isset($_FILES['mimage']) && $_FILES['mimage']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['mimage']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('mat_') . '.' . $ext;
        $targetPath = 'uploads/' . $filename;
        if (move_uploaded_file($_FILES['mimage']['tmp_name'], $targetPath)) {
            $mimage = $filename;
        } else {
            $error = "Image upload failed";
        }
    }

    if (!empty($mname) && !empty($munit)) {
        $stmt = $conn->prepare("INSERT INTO material (mname, mqty, mrqty, munit, mreorderqty, mimage) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisis", $mname, $mqty, $mrqty, $munit, $mreorderqty, $mimage);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Add failure：" . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please fill in the raw material name and unit, please enter a valid value";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New raw materials | Staff</title>
    <link rel="stylesheet" href="common.css">
    <style>
        body { font-family: 'Segoe UI'; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 1rem; color: #333; }
        .input-group { margin-bottom: 1rem; }
        label { display: block; font-weight: bold; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        .btn-group { margin-top: 20px; display: flex; gap: 1rem; }
        .btn-primary, .btn-secondary {
            padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;
        }
        .btn-primary { background: #4CAF50; color: white; }
        .btn-secondary { background: #ddd; color: #333; }
        .btn-primary:hover { background: #45a049; }
        .message { margin-bottom: 1rem; padding: 12px; border-radius: 6px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
    <h2>Added raw material information</h2>

    <?php if ($success): ?>
        <div class="message success">✅ Raw materials added successfully！</div>
    <?php elseif (!empty($error)): ?>
        <div class="message error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label for="mname">Raw material name</label>
            <input type="text" id="mname" name="mname" required>
        </div>

        <div class="input-group">
            <label for="mqty">Initial inventory quantity</label>
            <input type="number" id="mqty" name="mqty" value="0" min="0">
        </div>

        <div class="input-group">
            <label for="mrqty">Reserved quantity</label>
            <input type="number" id="mrqty" name="mrqty" value="0" min="0">
        </div>

        <div class="input-group">
            <label for="munit">Unit (such as KG, piece, liter)</label>
            <input type="text" id="munit" name="munit" required>
        </div>

        <div class="input-group">
            <label for="mreorderqty">Replenishment warning line (when the inventory is lower than this quantity, a prompt will be displayed)</label>
            <input type="number" id="mreorderqty" name="mreorderqty" value="100" min="0">
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-primary">✅ New raw materials</button>
            <button type="reset" class="btn-secondary">Clear</button>
        </div>
        <label for="mimage">Raw material pictures</label>
        <input type="file" name="mimage" id="mimage" accept="image/*" onchange="previewImage(event)">
        <img id="preview" style="max-width:120px; margin-top:8px; display:none;">
    </form>
</div>
</body>
</html>