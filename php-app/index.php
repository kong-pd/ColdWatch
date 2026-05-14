<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
$isAdmin = $user['role'] === 'admin';
?>

<?php
// PHP 职责：只负责输出 HTML 结构
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ColdWatch Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <h1> ColdWatch</h1>
  <span>Cold Chain Temperature Monitoring System</span>
</header>
<div style="background:#1e293b;padding:10px 40px;display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;">
  <span style="color:#94a3b8;">Logged in as <strong style="color:#60a5fa;"><?= htmlspecialchars($user['username']) ?></strong>
  <span style="background:#1e3a5f;color:#93c5fd;padding:2px 10px;border-radius:99px;margin-left:8px;"><?= $user['role'] ?></span>
  </span>
  <a href="logout.php" style="color:#f87171;text-decoration:none;">Logout</a>
</div>

<div class="container">

  <div class="status-bar">
    <h2>Live Sensor Status</h2>
    <span style="font-size:0.8rem;color:#64748b">
      <span class="live-dot"></span>Live — updates every 5s
    </span>
  </div>
  <div class="cards" id="sensor-cards">
    <div class="card"><div class="meta">Loading...</div></div>
  </div>

  <h2>Recent Alerts</h2>
  <table>
    <thead>
      <tr><th>Time</th><th>Sensor</th><th>Temp</th><th>Type</th><th>Threshold</th></tr>
    </thead>
    <tbody id="alert-rows">
      <tr><td colspan="5" style="text-align:center;color:#64748b">Loading...</td></tr>
    </tbody>
  </table>

</div>

<script src="app.js"></script>
</body>
</html>