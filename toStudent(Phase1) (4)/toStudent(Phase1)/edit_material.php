<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "projectdb");

$mid = intval($_GET['mid'] ?? 0);
$success = false;
$error = "";
$deleted = false;

if ($mid > 0) {
    $stmt = $conn->prepare("SELECT * FROM material WHERE mid = ?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $result = $stmt->get_result();
    $material = $result->fetch_assoc();
    $stmt->close();
} else {
    $error = "Invalid material number";
}

if (!$material && !$deleted) {
    $error = "The raw material information cannot be found";
} elseif (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM material WHERE mid = ?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mname = trim($_POST['mname']);
    $mqty = intval($_POST['mqty']);
    $mrqty = intval($_POST['mrqty']);
    $munit = trim($_POST['munit']);
    $mreorderqty = intval($_POST['mreorderqty']);
    $mimage = $material['mimage'] ?? "";

    if (isset($_FILES['mimage']) && $_FILES['mimage']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        $ext = pathinfo($_FILES['mimage']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('mat_') . '.' . $ext;
        $targetPath = 'uploads/' . $filename;

        if (move_uploaded_file($_FILES['mimage']['tmp_name'], $targetPath)) {
            if (!empty($mimage) && file_exists('uploads/' . $mimage)) {
                unlink('uploads/' . $mimage); 
            }
            $mimage = $filename;
        } else {
            $error = "Image upload failed";
        }
    }

    if (!empty($mname) && !empty($munit)) {
        $stmt = $conn->prepare("UPDATE material SET mname=?, mqty=?, mrqty=?, munit=?, mreorderqty=?, mimage=? WHERE mid=?");
        $stmt->bind_param("siisisi", $mname, $mqty, $mrqty, $munit, $mreorderqty, $mimage, $mid);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Update failed：" . $conn->error;
        }
        $stmt->close();
    } else {
        $error = "Please enter the complete field (raw material name and unit cannot be left blank)";
    }
}
?>
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Editing materials | Staff</title>
  <link rel="stylesheet" href="common.css">
  <style>
    body { font-family: 'Segoe UI'; padding: 40px; background: #f5f5f5; }
    .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 1rem; }
    .input-group { margin-bottom: 1rem; }
    label { font-weight: bold; margin-bottom: 4px; display: block; }
    input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
    .btn-group { display: flex; gap: 1rem; margin-top: 20px; flex-wrap: wrap; }
    .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .btn-primary { background: #2196f3; color: white; }
    .btn-delete { background: #e74c3c; color: white; }
    .btn-secondary { background: #ddd; color: #333; text-decoration: none; }
    .message { padding: 12px; border-radius: 6px; margin-bottom: 1rem; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
<div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
  <h2>Editing materials</h2>
    <?php if (!empty($material['mimage'])): ?>
        <img src="uploads/<?= htmlspecialchars($material['mimage']) ?>" style="max-width:120px; margin-bottom:8px; border-radius:6px;">
    <?php endif ?>
  <?php if ($error): ?>
    <div class="message error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php elseif ($deleted): ?>
    <div class="message success">🗑️ Raw material deleted</div>
    <a href="material_list.php" class="btn btn-secondary">Return to the raw materials list</a>
    <?php exit(); ?>
<?php elseif ($success): ?>
    <div class="message success">✅ Raw material update successful</div>
  <?php endif ?>

  <?php if (!empty($material)): ?>
  <form method="POST" enctype="multipart/form-data"
>
    <div class="input-group">
      <label>Raw material name</label>
      <input type="text" name="mname" value="<?= htmlspecialchars($material['mname']) ?>" required>
    </div>
    <div class="input-group">
      <label>Current stock quantity</label>
      <input type="number" name="mqty" value="<?= $material['mqty'] ?>" min="0">
    </div>
    <div class="input-group">
      <label>Reserved quantity</label>
      <input type="number" name="mrqty" value="<?= $material['mrqty'] ?>" min="0">
    </div>
    <div class="input-group">
      <label>unit</label>
      <input type="text" name="munit" value="<?= htmlspecialchars($material['munit']) ?>" required>
    </div>
    <div class="input-group">
      <label>Restocking threshold</label>
      <input type="number" name="mreorderqty" value="<?= $material['mreorderqty'] ?>" min="0">
    </div>

    <label for="mimage">Raw material pictures</label>
    <input type="file" name="mimage" id="mimage" accept="image/*" onchange="previewImage(event)">
    <img id="preview" style="max-width:120px; margin-top:8px; display:none;">

    <div class="btn-group">
      <button type="submit" class="btn btn-primary">💾 Storage changes</button>
      <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this ingredient?')">🗑️ delete</button>
      <a href="material_list.php" class="btn btn-secondary">Return to the raw materials list</a>
    </div>
  </form>
  <?php endif ?>
</div>
</body>
</html>