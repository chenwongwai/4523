<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "projectdb");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $stmt = $conn->prepare("DELETE FROM product WHERE pid = ?");
    $stmt->bind_param("i", $pid);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $stmt->error]);
    }
    exit();
}

$products = $conn->query("SELECT pid, pname, pcost FROM product ORDER BY pid DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Delete product management</title>
  <link rel="stylesheet" href="common.css">
  <style>
    body { 
      font-family: 'Segoe UI'; 
      padding: 40px; 
      background: #f7f9fb; 
    }
    .container { 
      max-width: 800px; 
      margin: auto; 
    }
    .search-section { 
      display: flex; 
      gap: 1rem; 
      margin-bottom: 2rem; 
    }
    .input-group input { 
      padding: 10px; 
      border-radius: 6px; 
      border: 1px solid #ccc; 
      width: 100%; 
    }
    .btn { 
      padding: 10px 20px; 
      border: none; 
      border-radius: 6px; 
      font-weight: bold; 
      cursor: pointer; 
    }
    .btn-primary { 
      background: #e74c3c; 
      color: white; 
    }
    .btn-secondary { 
      background: #eee; 
      color: #333; 
    }
    .results-list { 
      margin-top: 2rem; 
    }
    .item-card {
      background: white; border-radius: 8px;
      padding: 1rem; margin-bottom: 1rem;
      display: flex; justify-content: space-between; align-items: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: transform 0.2s;
    }
    .item-card:hover { transform: translateX(5px); border-left: 4px solid #e74c3c; }
    .toast {
      position: fixed; bottom: 20px; left: 50%;
      transform: translateX(-50%);
      padding: 12px 24px; border-radius: 20px;
      font-weight: 500; z-index: 999;
    }
    .toast.success { background: #4CAF50; color: white; }
    .toast.error { background: #e74c3c; color: white; }
  </style>
</head>
<body>
  <div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
  <h2>🗑️ Product deletion management</h2>

  <div class="search-section">
    <div class="input-group" style="flex:1">
      <input type="text" placeholder="Enter product name or number" id="searchInput" oninput="searchItems()">
    </div>
  </div>

  <div class="results-list" id="resultsList">
    <?php foreach ($products as $p): ?>
      <div class="item-card" data-id="<?= $p['pid'] ?>" data-name="<?= strtolower($p['pname']) ?>">
        <div>
          <strong>#<?= $p['pid'] ?> <?= htmlspecialchars($p['pname']) ?></strong><br>
          <small>cost：HK$<?= number_format($p['pcost'], 2) ?></small>
        </div>
        <button class="btn btn-primary" onclick="confirmDelete(this)">delete</button>
      </div>
    <?php endforeach ?>
  </div>
</div>

<script>
let selectedCard = null;

function confirmDelete(btn) {
  const card = btn.closest('.item-card');
  const pid = card.dataset.id;
  const pname = card.dataset.name;

  if (confirm(`are you onfirm to delete the product #${pid} ？`)) {
    const formData = new FormData();
    formData.append('pid', pid);

    fetch('delete_item.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        card.remove();
        showToast(`✅ Deleted products #${pid}`, "success");
      } else {
        showToast("❌ Unable to delete product：" + (data.error || "Unknown error"), "error");
      }
    });
  }
}

function searchItems() {
  const keyword = document.getElementById("searchInput").value.toLowerCase();
  document.querySelectorAll('.item-card').forEach(card => {
    const name = card.dataset.name + card.dataset.id;
    card.style.display = name.includes(keyword) ? "flex" : "none";
  });
}

function showToast(msg, type="success") {
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}
</script>
</body>
</html>