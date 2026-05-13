<?php
$api = "http://flask-api:5000";

function fetch($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true) ?? [];
}

$readings = fetch("$api/readings");
$alerts   = fetch("$api/alerts");

// 取每个传感器最新一笔
$latest = [];
foreach ($readings as $r) {
    if (!isset($latest[$r['sensor_name']])) {
        $latest[$r['sensor_name']] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="10">
<title>ColdWatch Dashboard</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; }
  header { background: #1e3a5f; padding: 20px 40px; display: flex; align-items: center; gap: 12px; }
  header h1 { font-size: 1.5rem; color: #60a5fa; }
  header span { font-size: 0.85rem; color: #94a3b8; }
  .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
  h2 { font-size: 1rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }

  /* Sensor cards */
  .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 40px; }
  .card { background: #1e293b; border-radius: 12px; padding: 24px; border-left: 4px solid #3b82f6; }
  .card.alert-high { border-left-color: #ef4444; }
  .card.alert-low  { border-left-color: #3b82f6; }
  .card .temp { font-size: 2.5rem; font-weight: 700; margin: 8px 0; }
  .card .temp.high { color: #f87171; }
  .card .temp.low  { color: #60a5fa; }
  .card .temp.ok   { color: #34d399; }
  .card .meta { font-size: 0.8rem; color: #64748b; }
  .badge { display: inline-block; padding: 2px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; margin-top: 8px; }
  .badge.high { background: #7f1d1d; color: #fca5a5; }
  .badge.low  { background: #1e3a5f; color: #93c5fd; }
  .badge.ok   { background: #064e3b; color: #6ee7b7; }

  /* Alert table */
  table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; }
  th { background: #0f172a; padding: 12px 16px; text-align: left; font-size: 0.8rem; color: #64748b; text-transform: uppercase; }
  td { padding: 12px 16px; border-top: 1px solid #334155; font-size: 0.9rem; }
  tr:hover td { background: #263347; }
  .pill { padding: 2px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600; }
  .pill.HIGH { background: #7f1d1d; color: #fca5a5; }
  .pill.LOW  { background: #1e3a5f; color: #93c5fd; }
  .refresh { font-size: 0.75rem; color: #475569; text-align: right; margin-top: 8px; }
</style>
</head>
<body>

<header>
  <h1> ColdWatch</h1>
  <span>Cold Chain Temperature Monitoring System</span>
</header>

<div class="container">

  <h2>Live Sensor Status</h2>
  <div class="cards">
  <?php foreach ($latest as $name => $r):
    $temp = floatval($r['temperature']);
    $isHigh = $temp > 8 || ($r['location'] === 'Cold Storage Room' && $temp > 0);
    $isLow  = ($temp < 2 && $r['location'] !== 'Cold Storage Room') || ($r['location'] === 'Cold Storage Room' && $temp < -5);
    $status = $isHigh ? 'high' : ($isLow ? 'low' : 'ok');
    $label = $status === 'high' ? 'HIGH ALERT' : ($status === 'low' ? 'LOW ALERT' : 'NORMAL');
  ?>
    <div class="card alert-<?= $status ?>">
      <div style="font-weight:600"><?= htmlspecialchars($name) ?></div>
      <div class="meta"><?= htmlspecialchars($r['location']) ?></div>
      <div class="temp <?= $status ?>"><?= $temp ?>°C</div>
      <span class="badge <?= $status ?>"><?= $label ?></span>
      <div class="meta" style="margin-top:8px">Updated: <?= $r['recorded_at'] ?></div>
    </div>
  <?php endforeach; ?>
  </div>

  <h2>Recent Alerts</h2>
  <table>
    <thead>
      <tr><th>Time</th><th>Sensor</th><th>Temp</th><th>Type</th><th>Threshold</th></tr>
    </thead>
    <tbody>
    <?php if (empty($alerts)): ?>
      <tr><td colspan="5" style="text-align:center;color:#64748b">No alerts yet</td></tr>
    <?php else: ?>
      <?php foreach ($alerts as $a): ?>
      <tr>
        <td><?= $a['created_at'] ?></td>
        <td><?= htmlspecialchars($a['sensor_name']) ?></td>
        <td><?= $a['temperature'] ?>°C</td>
        <td><span class="pill <?= $a['breach_type'] ?>"><?= $a['breach_type'] ?></span></td>
        <td><?= $a['threshold_value'] ?>°C</td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
  <div class="refresh">Auto-refresh every 10 seconds</div>

</div>
</body>
</html>