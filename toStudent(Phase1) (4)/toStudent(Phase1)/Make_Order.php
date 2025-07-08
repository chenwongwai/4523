<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'customer') {
    header("Location: index.php");
    exit();
}
$username = $_SESSION['username'];
$conn = new mysqli("localhost", "root", "", "projectdb");
$products = $conn->query("SELECT * FROM product");
?>
<!DOCTYPE html>
<html lang="zh-HK">
<head>
    <meta charset="UTF-8">
    <title>Create an order</title>
    <link rel="stylesheet" href="common.css">
    <style>
        body { font-family: 'Segoe UI'; padding: 40px; background: #f7f7f7; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(250px,1fr)); gap: 1.5rem; }
        .card { background:white; padding:1rem; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
        .card img { width:100%; height:160px; object-fit:cover; border-radius:6px; }
        .qty { display:flex; align-items:center; margin-top:0.5rem; }
        .qty input { width:40px; text-align:center; margin:0 8px; }
        .toast { position:fixed; bottom:20px; left:50%; transform:translateX(-50%); background:#4CAF50; color:#fff; padding:10px 20px; border-radius:20px; display:none; }
    </style>
</head>
<body>
<h2>👋 Hello，<?= htmlspecialchars($username) ?></h2>

<div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="grid">
<?php while ($p = $products->fetch_assoc()): ?>
    <div class="card" data-id="<?= $p['pid'] ?>">
        <img src="<?= $p['pimage'] ? 'uploads/' . $p['pimage'] : 'https://via.placeholder.com/200?text=No+Image' ?>" alt="<?= $p['pname'] ?>">
        <h3><?= htmlspecialchars($p['pname']) ?></h3>
        <p><?= htmlspecialchars($p['pdesc']) ?></p>
        <strong>HK$ <?= number_format($p['pcost'], 2) ?></strong>
        <div class="qty">
            <button onclick="adjustQty(this,-1)">－</button>
            <input type="number" value="0" min="0">
            <button onclick="adjustQty(this,1)">＋</button>
        </div>
        <button onclick="addToCart(this)">add to the cart</button>
    </div>
<?php endwhile ?>
</div>

<button onclick="submitOrder()" style="margin-top:2rem">🧾 Checkout</button>
<div class="toast" id="toast">Success Message</div>

<script>
let cart = [];

function adjustQty(btn, n) {
  const input = btn.parentElement.querySelector("input");
  input.value = Math.max(0, parseInt(input.value) + n);
}

function addToCart(btn) {
  const card = btn.closest('.card');
  const qty = parseInt(card.querySelector('input').value);
  if (qty <= 0) return show('Please select quantity');
  const id = card.dataset.id;
  const name = card.querySelector('h3').textContent;
  const price = parseFloat(card.querySelector('strong').textContent.replace('HK$', ''));
  const img = card.querySelector('img').src;
  const index = cart.findIndex(i => i.id === id);
  if (index >= 0) cart[index].quantity += qty;
  else cart.push({ id, name, price, quantity: qty, image: img });
  card.querySelector('input').value = 0;
  show('✅ Added to cart');
}

function submitOrder() {
  if (cart.length === 0) return show('Shopping cart is empty');
  fetch("create_order.php", {
    method: "POST",
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ items: cart })
  }).then(res => res.json()).then(data => {
    if (data.success) {
      show('✅ Order has been sent');
      cart = [];
    } else {
      show('❌ ' + (data.error || 'An error occurred'));
    }
  });
}

function show(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 2500);
}
</script>
</body>
</html>