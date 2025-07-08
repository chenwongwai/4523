<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");
$result = $conn->query("SELECT pid, pname, pcost, pdesc, pimage FROM product ORDER BY pid DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Product Overview | Staff</title>
  <link rel="stylesheet" href="common.css">
  <style>
    body { font-family: "Segoe UI"; background: #f2f2f2; padding: 40px; }
    .container { max-width: 1000px; margin: auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .page-title { font-size: 1.5rem; margin-bottom: 1.5rem; }
    .product-card {
      display: flex; align-items: center; justify-content: space-between;
      background: #fafafa; padding: 1rem; border-radius: 8px;
      margin-bottom: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .product-info { display: flex; gap: 1rem; align-items: center; flex: 1; }
    .product-img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #ccc; border-radius: 6px; }
    .btn { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-weight: bold; }
    .btn-edit { background: #2196f3; color: white; }
    .btn-new { background: #4CAF50; color: white; float: right; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <div style="margin-bottom: 1rem;">
    <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
  </div>
  <div class="container">
    <h2 class="page-title">📋 Product Overview
      <a href="insert_item.php" class="btn btn-new">➕ New Products</a>
    </h2>

    <?php while($row = $result->fetch_assoc()): ?>
      <div class="product-card">
        <div class="product-info">
          <?php if ($row['pimage']): ?>
            <img src="uploads/<?= $row['pimage'] ?>" class="product-img">
          <?php else: ?>
            <div class="product-img" style="background:#eee;display:flex;align-items:center;justify-content:center;color:#aaa">N/A</div>
          <?php endif ?>
          <div>
            <strong><?= htmlspecialchars($row['pname']) ?></strong><br>
            <small>HK$<?= number_format($row['pcost'], 2) ?></small><br>
            <small><?= nl2br(htmlspecialchars($row['pdesc'])) ?></small>
          </div>
        </div>
        <a href="edit_item.php?pid=<?= $row['pid'] ?>" class="btn btn-edit">✏️ edit</a>
      </div>
    <?php endwhile; ?>
  </div>
</body>
</html>