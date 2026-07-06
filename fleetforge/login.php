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
  --accent2: #e05a30;
  --text: #e8eaf2;
  --text2: #9ba3bc;
  --muted: #565e78;
  --green: #2ecc8a;
  --red: #f05060;
  --blue: #4d8ef0;
  --purple: #9d6ef0;
  --font-d: 'Bebas Neue', sans-serif;
  --font-b: 'DM Sans', sans-serif;
  --font-m: 'DM Mono', monospace;
  --radius: 14px;
}
*{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;}
body{
  background:
    radial-gradient(ellipse 900px 500px at 50% 0%, rgba(240,192,64,0.07), transparent 60%),
    radial-gradient(ellipse 700px 500px at 85% 100%, rgba(77,142,240,0.05), transparent 60%),
    var(--bg);
  color:var(--text);font-family:var(--font-b);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  overflow-x:hidden;
}

/* ── Card ── */
.login-wrap{width:100%;max-width:420px;margin:20px;}

.login-card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:36px 32px 30px;
  box-shadow:0 20px 40px -20px rgba(0,0,0,.5);
  animation:cardIn .5s cubic-bezier(.2,.8,.2,1);
}
@keyframes cardIn{
  from{opacity:0;transform:translateY(14px);}
  to{opacity:1;transform:translateY(0);}
}

.brand-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:4px;}
.brand-icon{
  width:40px;height:40px;border-radius:10px;
  background:var(--surface2);border:1px solid var(--border2);
  display:flex;align-items:center;justify-content:center;font-size:19px;
}
.logo-mark{font-family:var(--font-d);font-size:34px;letter-spacing:3px;color:var(--text);line-height:1;}
.logo-mark span{color:var(--accent);}
.logo-sub{font-size:10px;letter-spacing:3.5px;color:var(--muted);text-transform:uppercase;text-align:center;margin-top:5px;margin-bottom:28px;}

.form-group{margin-bottom:17px;position:relative;}
.label-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:7px;}
label{display:block;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);font-weight:500;}
.forgot-link{font-size:11px;color:var(--text2);text-decoration:none;transition:color .15s;}
.forgot-link:hover{color:var(--accent);}

.input-wrap{position:relative;}
.input-icon{
  position:absolute;left:13px;top:50%;transform:translateY(-50%);
  font-size:14px;opacity:.55;pointer-events:none;
}
input{
  width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:9px;
  padding:12px 13px 12px 36px;color:var(--text);font-family:var(--font-b);font-size:14px;outline:none;
  transition:border-color .15s, box-shadow .15s, background .15s;
}
input[name="password"]{padding-right:40px;}
input::placeholder{color:var(--muted);}
input:focus{border-color:var(--accent);background:#181c26;box-shadow:0 0 0 3px rgba(240,192,64,0.12);}

.toggle-pass{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:14px;opacity:.55;
  padding:2px;color:var(--text2);transition:opacity .15s;
}
.toggle-pass:hover{opacity:.9;}

.btn-login{
  width:100%;margin-top:10px;padding:12px;border:none;border-radius:9px;
  background:var(--accent);color:#080a0f;
  font-family:var(--font-b);font-weight:600;
  font-size:14px;letter-spacing:.3px;cursor:pointer;
  transition:background .15s, transform .15s, box-shadow .15s;
  display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-login:hover{background:#f5d060;transform:translateY(-1px);box-shadow:0 8px 20px -8px rgba(240,192,64,0.5);}
.btn-login:active{transform:translateY(0);}
.btn-login:disabled{opacity:.75;cursor:default;transform:none;}

.spinner{
  width:14px;height:14px;border-radius:50%;
  border:2px solid rgba(8,10,15,0.3);border-top-color:#080a0f;
  animation:spin .6s linear infinite;display:none;
}
@keyframes spin{to{transform:rotate(360deg);}}

.error-box{
  background:rgba(240,80,96,0.1);border:1px solid rgba(240,80,96,0.25);color:var(--red);
  border-radius:9px;padding:11px 13px;font-size:13px;margin-bottom:18px;
  display:flex;align-items:center;gap:8px;
  animation:shake .3s;
}
@keyframes shake{
  25%{transform:translateX(-4px);} 75%{transform:translateX(4px);}
}

.divider{display:flex;align-items:center;gap:10px;margin:24px 0 18px;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.divider span{font-size:10px;letter-spacing:1.5px;color:var(--muted);text-transform:uppercase;white-space:nowrap;}

.roles{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.role-card{
  background:var(--surface2);border:1px solid var(--border);border-radius:9px;
  padding:12px 12px 13px;text-align:center;cursor:pointer;
  transition:border-color .15s, transform .15s, box-shadow .15s;
}
.role-card:hover{transform:translateY(-2px);}
.role-card.is-admin:hover{border-color:rgba(240,192,64,0.4);box-shadow:0 8px 18px -10px rgba(240,192,64,0.35);}
.role-card.is-staff:hover{border-color:rgba(77,142,240,0.4);box-shadow:0 8px 18px -10px rgba(77,142,240,0.35);}
.role-card:active{transform:translateY(0);}

.role-card .role-tag{
  display:inline-block;font-size:9px;letter-spacing:1.5px;text-transform:uppercase;
  padding:2px 8px;border-radius:20px;font-weight:600;margin-bottom:8px;
}
.role-card.is-admin .role-tag{background:rgba(240,192,64,0.12);color:var(--accent);border:1px solid rgba(240,192,64,0.25);}
.role-card.is-staff .role-tag{background:rgba(77,142,240,0.12);color:var(--blue);border:1px solid rgba(77,142,240,0.25);}
.role-card .role-cred{font-family:var(--font-m);font-size:12.5px;color:var(--text);line-height:1.7;}
.role-card .role-desc{font-size:10px;color:var(--muted);margin-top:5px;}
.role-card .use-hint{font-size:9px;color:var(--muted);margin-top:6px;opacity:0;transition:opacity .15s;letter-spacing:.5px;}
.role-card:hover .use-hint{opacity:.8;}

.footer-note{text-align:center;margin-top:22px;font-size:11px;color:var(--muted);letter-spacing:.3px;}
</style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="brand-row">
        <div class="brand-icon">🚗</div>
      </div>
      <div class="logo-mark" style="text-align:center;">Fleet<span>Forge</span></div>
      <div class="logo-sub">Vehicle Rental Management</div>

      <?php if ($error): ?>
        <div class="error-box">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="loginForm">
        <div class="form-group">
          <label>Username</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" name="username" id="username" placeholder="Enter your username" autocomplete="username" required autofocus>
          </div>
        </div>
        <div class="form-group">
          <div class="label-row">
            <label>Password</label>
            <a href="#" class="forgot-link" onclick="return false;">Forgot password?</a>
          </div>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" name="password" id="password" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">👁</button>
          </div>
        </div>
        <button type="submit" class="btn-login" id="submitBtn">
          <span id="btnText">Sign In →</span>
          <span class="spinner" id="btnSpinner"></span>
        </button>
      </form>

      <div class="divider"><span>Demo Access</span></div>

      <div class="roles">
        <div class="role-card is-admin" onclick="fillDemo('admin','admin123')">
          <span class="role-tag">Admin</span>
          <div class="role-cred">admin<br>admin123</div>
          <div class="role-desc">Full access</div>
          <div class="use-hint">Click to use →</div>
        </div>
        <div class="role-card is-staff" onclick="fillDemo('staff','staff123')">
          <span class="role-tag">Staff</span>
          <div class="role-cred">staff<br>staff123</div>
          <div class="role-desc">No delete access</div>
          <div class="use-hint">Click to use →</div>
        </div>
      </div>
    </div>
    <div class="footer-note">FleetForge Pro · Rental Management System</div>
  </div>

<script>
function fillDemo(user, pass) {
  document.getElementById('username').value = user;
  document.getElementById('password').value = pass;
}

document.getElementById('togglePass').addEventListener('click', function() {
  const pw = document.getElementById('password');
  const isHidden = pw.type === 'password';
  pw.type = isHidden ? 'text' : 'password';
  this.textContent = isHidden ? '🙈' : '👁';
  this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
});

document.getElementById('loginForm').addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  document.getElementById('btnText').textContent = 'Signing in…';
  document.getElementById('btnSpinner').style.display = 'inline-block';
  btn.disabled = true;
});
</script>
</body>
</html>
