<?php
// ── login.php ───────────────────────────────────────────────────
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $pdo = getDB();
        if (attemptLogin($username, $password, $pdo)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FleetForge Pro — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #080a0f;
  --surface: #0f1118;
  --surface2: #161921;
  --border: #232737;
  --border2: #2d3245;
  --accent: #f0c040;
  --text: #e8eaf2;
  --text2: #9ba3bc;
  --muted: #565e78;
  --red: #f05060;
  --font-d: 'Bebas Neue', sans-serif;
  --font-b: 'DM Sans', sans-serif;
  --font-m: 'DM Mono', monospace;
  --radius: 10px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  background:var(--bg);color:var(--text);font-family:var(--font-b);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
}
.login-card{
  width:100%;max-width:380px;background:var(--surface);
  border:1px solid var(--border);border-radius:var(--radius);
  padding:36px 32px;margin:20px;
}
.logo-mark{font-family:var(--font-d);font-size:34px;letter-spacing:3px;color:var(--accent);text-align:center;}
.logo-sub{font-size:10px;letter-spacing:3.5px;color:var(--muted);text-transform:uppercase;text-align:center;margin-top:4px;margin-bottom:28px;}
.form-group{margin-bottom:16px;}
label{display:block;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
input{
  width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;
  padding:11px 13px;color:var(--text);font-family:var(--font-b);font-size:14px;outline:none;
  transition:border-color .15s;
}
input:focus{border-color:var(--accent);}
.btn-login{
  width:100%;margin-top:8px;padding:11px;border:none;border-radius:8px;
  background:var(--accent);color:#080a0f;font-family:var(--font-b);font-weight:600;
  font-size:14px;letter-spacing:.3px;cursor:pointer;transition:all .15s;
}
.btn-login:hover{background:#f5d060;transform:translateY(-1px);}
.error-box{
  background:rgba(240,80,96,0.1);border:1px solid rgba(240,80,96,0.25);color:var(--red);
  border-radius:8px;padding:10px 13px;font-size:13px;margin-bottom:16px;
}
.hint{font-size:11px;color:var(--muted);text-align:center;margin-top:20px;font-family:var(--font-m);line-height:1.6;}
</style>
</head>
<body>
  <div class="login-card">
    <div class="logo-mark">FleetForge</div>
    <div class="logo-sub">Rental Management</div>

    <?php if ($error): ?>
      <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" autocomplete="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-login">Sign In</button>
    </form>

    <div class="hint">admin / admin123 (full access)<br>staff / staff123 (no delete access)</div>
  </div>
</body>
</html>
