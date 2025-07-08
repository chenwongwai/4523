<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "projectdb");
$result = $conn->query("SELECT mid, mname, mqty, mrqty, munit, mreorderqty, mimage FROM material ORDER BY mid DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Raw material inventory summary</title>
  <link rel="stylesheet" href="common.css">
  <style>
    body { font-family: 'Segoe UI'; padding: 40px; background: #f5f5f5; }
    .container { max-width: 1000px; margin: auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    h2 { margin-bottom: 1.5rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
    th { background: #f2f2f2; }
    .low-stock { background: #fff3cd; color: #856404; }
    .search-bar { margin-bottom: 1rem; display: flex; gap: 1rem; }
    input[type="search"] { padding: 8px; width: 250px; border-radius: 6px; border: 1px solid #ccc; }
  </style>
</head>
<body>
<div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
  <h2>📦 Raw material inventory summary</h2>
  <h2 style="display:flex;justify-content:space-between;align-items:center;">
  <a href="insert_material.php" style="background:#4CAF50; color:white; padding:6px 14px; border-radius:6px; text-decoration:none;">➕ Add material</a>
  </h2>

  <div class="search-bar">
    <input type="search" id="searchInput" placeholder="Search for raw material name or unit" oninput="filterTable()">
  </div>

  <table>
    <thead>
      <tr>
        <th>picture</th>
        <th>Raw material number</th>
        <th>name</th>
        <th>unit</th>
        <th>in stock</th>
        <th>Reserved</th>
        <th>Restocking alert</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="materialTable">
    <?php while($m = $result->fetch_assoc()): 
      $low = $m['mqty'] < $m['mreorderqty'];
      $imgPath = $m['mimage'] ? "uploads/{$m['mimage']}" : "https://via.placeholder.com/80x80?text=No+Image";
    ?>
      <tr class="<?= $low ? 'low-stock' : '' ?>">
        <td><img src="<?= $imgPath ?>" style="width:60px;height:60px;object-fit:cover;border-radius:6px">
        <td>#<?= $m['mid'] ?></td>
        <td><?= htmlspecialchars($m['mname']) ?></td>
        <td><?= htmlspecialchars($m['munit']) ?></td>
        <td><?= $m['mqty'] ?></td>
        <td><?= $m['mrqty'] ?></td>
        <td><?= $m['mreorderqty'] ?></td>
        <td><a href="edit_material.php?mid=<?= $m['mid'] ?>" class="btn btn-primary">✏️ edit</a></td>
      </tr>
    <?php endwhile ?>
    </tbody>
  </table>
</div>

<script>
function filterTable() {
  const keyword = document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll("#materialTable tr").forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(keyword) ? "" : "none";
  });
}
</script>
</body>
</html>