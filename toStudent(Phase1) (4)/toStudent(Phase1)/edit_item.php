<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$product = null;
$error = "";
$success = false;

if ($pid > 0) {
    $res = $conn->query("SELECT * FROM product WHERE pid = $pid");
    $product = $res->fetch_assoc();
    if (!$product) {
        $error = "No information found for this product";
    }
} else {
    $error = "Please specify the product number ?pid=123";
}

if (!file_exists("uploads")) {
    mkdir("uploads", 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product) {
    $pname = trim($_POST['pname']);
    $pdesc = trim($_POST['pdesc']);
    $pcost = floatval($_POST['pcost']);
    $newImage = $product['pimage'];

    if (isset($_FILES['pimage']) && $_FILES['pimage']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['pimage']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_') . '.' . $ext;
        $targetPath = 'uploads/' . $filename;
        if (move_uploaded_file($_FILES['pimage']['tmp_name'], $targetPath)) {
            $newImage = $filename;
        } else {
            $error = "Image upload failed";
        }
    }

    if (empty($error) && !empty($pname) && $pcost > 0) {
        $stmt = $conn->prepare("UPDATE product SET pname=?, pdesc=?, pcost=?, pimage=? WHERE pid=?");
        $stmt->bind_param("ssdsi", $pname, $pdesc, $pcost, $newImage, $pid);
        if ($stmt->execute()) {
            $success = true;
            $product = array_merge($product, [
                'pname' => $pname,
                'pdesc' => $pdesc,
                'pcost' => $pcost,
                'pimage' => $newImage
            ]);
        } else {
            $error = "Update failed：" . $conn->error;
        }
        $stmt->close();
    } elseif (empty($error)) {
        $error = "Please fill in the correct fields";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Edit product | Staff</title>
  <link rel="stylesheet" href="common.css">
  <style>
    body { font-family: 'Segoe UI'; padding: 40px; background: #f5f5f5; }
    .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 1rem; color: #333; }
    .input-group { margin-bottom: 1rem; }
    label { display: block; font-weight: bold; margin-bottom: 6px; }
    input, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
    textarea { resize: vertical; height: 100px; }
    .btn-group { margin-top: 20px; display: flex; gap: 1rem; }
    .btn-primary, .btn-secondary {
      padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;
    }
    .btn-primary { background: #2196f3; color: white; }
    .btn-secondary { background: #ddd; color: #333; }
    .message { margin-bottom: 1rem; padding: 12px; border-radius: 6px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .preview-img { margin-top: 10px; width: 120px; object-fit: cover; border-radius: 6px; border: 1px solid #ccc; }
  </style>
</head>
<body>
<div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
  <h2>Edit product information</h2>
  <?php if ($error): ?>
    <div class="message error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php elseif ($success): ?>
    <div class="message success">✅ Item updated successfully</div>
  <?php endif; ?>

  <?php if ($product): ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="input-group">
      <label for="pname">Product Name</label>
      <input type="text" id="pname" name="pname" value="<?= htmlspecialchars($product['pname']) ?>" required>
    </div>

    <div class="input-group">
      <label for="pdesc">Product Description</label>
      <textarea id="pdesc" name="pdesc"><?= htmlspecialchars($product['pdesc']) ?></textarea>
    </div>

    <div class="input-group">
      <label for="pcost">Cost Price (HK$)</label>
      <input type="number" id="pcost" name="pcost" step="0.01" value="<?= $product['pcost'] ?>" required>
    </div>

    <div class="input-group">
      <label for="pimage">Product images</label>
      <input type="file" id="pimage" name="pimage" accept="image/*" onchange="previewImage(event)">
      <?php if (!empty($product['pimage'])): ?>
        <img id="preview" class="preview-img" src="uploads/<?= $product['pimage'] ?>">
      <?php else: ?>
        <img id="preview" class="preview-img" style="display:none;">
      <?php endif ?>
    </div>

    <div class="btn-group">
      <button type="submit" class="btn-primary">💾 Storage changes</button>
      <a href="product_list.php" class="btn-secondary">Back to list</a>
    </div>
  </form>
  <?php endif ?>
</div>

<script>
function previewImage(e) {
  const file = e.target.files[0];
  const reader = new FileReader();
  reader.onload = function(evt) {
    const img = document.getElementById('preview');
    img.src = evt.target.result;
    img.style.display = 'block';
  };
  reader.readAsDataURL(file);
}
</script>
</body>
</html>