<?php
session_start();

// 已登入就直接跳到仪表盘
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 呼叫 Flask API 验证
    $ch = curl_init('http://flask-api:5000/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'username' => $username,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200) {
        $user = json_decode($result, true);
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ColdWatch Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .login-wrap { display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .login-box { background:#1e293b; padding:40px; border-radius:16px; width:360px; }
    .login-box h2 { color:#60a5fa; margin-bottom:24px; font-size:1.4rem; }
    .form-group { margin-bottom:16px; }
    .form-group label { display:block; color:#94a3b8; font-size:0.85rem; margin-bottom:6px; }
    .form-group input { width:100%; padding:10px 14px; background:#0f172a; border:1px solid #334155;
      border-radius:8px; color:#e2e8f0; font-size:0.95rem; box-sizing:border-box; }
    .form-group input:focus { outline:none; border-color:#3b82f6; }
    .btn-login { width:100%; padding:12px; background:#3b82f6; color:white; border:none;
      border-radius:8px; font-size:1rem; cursor:pointer; margin-top:8px; }
    .btn-login:hover { background:#2563eb; }
    .error { color:#f87171; font-size:0.85rem; margin-bottom:12px; }
  </style>
</head>
<body>
<header>
  <h1>❄️ ColdWatch</h1>
  <span>Cold Chain Temperature Monitoring System</span>
</header>

<div class="login-wrap">
  <div class="login-box">
    <h2>Sign In</h2>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn-login">Login</button>
    </form>
  </div>
</div>
</body>
</html>