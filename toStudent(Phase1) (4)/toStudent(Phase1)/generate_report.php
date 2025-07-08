<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staff') {
    header("Location: index.php");
    exit();
}
$conn = new mysqli("localhost", "root", "", "projectdb");
$reportData = null;
$start = "";
$end = "";
$type = "";

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['type'])) {
    $type = $_GET['type'];
    $start = $_GET['start'] ?? "";
    $end = $_GET['end'] ?? "";

    $sql = "SELECT COUNT(*) AS total_orders,
                   SUM(ocost) AS total_sales,
                   AVG(ocost) AS avg_order
            FROM orders
            WHERE ostatus = 3";

    if (!empty($start) && !empty($end)) {
        $sql .= " AND odate BETWEEN '$start' AND '$end'";
    }

    $res = $conn->query($sql);
    $reportData = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Generate Report</title>
  <link rel="stylesheet" href="common.css">
  <style>
    :root { --primary-color: #4CAF50; --border-color: #ccc; --border-radius: 8px; }
    body { font-family: "Segoe UI"; background: #f8f9fa; padding: 40px; }
    .container { max-width: 1000px; margin: auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .page-title { font-size: 1.6rem; margin-bottom: 2rem; color: #333; }
    .report-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
    .report-card { padding: 1rem; border: 2px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer; transition: 0.3s; }
    .report-card:hover, .report-card.active { border-color: var(--primary-color); background: #f8fff8; }
    .date-picker { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem; }
    .input-group label { font-weight: bold; display: block; margin-bottom: 6px; }
    input[type="date"] { padding: 8px; width: 200px; border-radius: 6px; border: 1px solid #ccc; }
    .btn-group { margin-top: 1.5rem; display: flex; gap: 1rem; }
    .btn { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; }
    .btn-primary { background: var(--primary-color); color: white; }
    .preview-area { border: 2px solid var(--border-color); border-radius: var(--border-radius); padding: 1.5rem; margin-top: 2rem; min-height: 200px; background: #fafafa; }
  </style>
</head>
<body>
  <div style="margin-bottom: 1rem;">
  <button onclick="history.back()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #ccc; cursor: pointer;">← return</button>
</div>
<div class="container">
  <h2 class="page-title">📊 Operation report generator</h2>

  <form method="GET">
    <div class="report-options">
      <label class="report-card <?= $type=='daily' ? 'active' : '' ?>"><input type="radio" name="type" value="daily" <?= $type=='daily' ? 'checked' : '' ?>> Daily Report</label>
      <label class="report-card <?= $type=='weekly' ? 'active' : '' ?>"><input type="radio" name="type" value="weekly" <?= $type=='weekly' ? 'checked' : '' ?>> Weekly Report</label>
      <label class="report-card <?= $type=='monthly' ? 'active' : '' ?>"><input type="radio" name="type" value="monthly" <?= $type=='monthly' ? 'checked' : '' ?>> Monthly Report</label>
    </div>

    <div class="date-picker">
      <div class="input-group">
        <label>start date</label>
        <input type="date" name="start" value="<?= $start ?>">
      </div>
      <div class="input-group">
        <label>End Date</label>
        <input type="date" name="end" value="<?= $end ?>">
      </div>
    </div>

    <div class="btn-group">
      <button type="submit" class="btn btn-primary">Generate Report</button>
    </div>
  </form>

  <div class="preview-area">
    <?php if ($reportData): ?>
      <h3>📅 Report Results（<?= htmlspecialchars($type) ?>）</h3>
      <?php if (!empty($start) && !empty($end)): ?>
        <p>Statistical interval：<?= htmlspecialchars($start) ?> ~ <?= htmlspecialchars($end) ?></p>
      <?php endif ?>
      <ul>
        <li>🧾 Total Orders：<?= $reportData['total_orders'] ?></li>
        <li>💰 Total sales amount：HK$<?= number_format($reportData['total_sales'], 2) ?></li>
        <li>📈 Average order value：HK$<?= number_format($reportData['avg_order'], 2) ?></li>
      </ul>
    <?php else: ?>
      <p>Please select the report type and submit。</p>
    <?php endif ?>
  </div>
</div>
</body>
</html>