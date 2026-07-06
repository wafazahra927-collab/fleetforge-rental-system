<?php
require_once __DIR__.'/includes/auth.php';
requireLogin();
$me = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FleetForge Pro — Vehicle Rental Management</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #080a0f;
  --surface: #0f1118;
  --surface2: #161921;
  --surface3: #1d212e;
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
  --orange: #f07040;
  --teal: #30c0b0;
  --font-d: 'Bebas Neue', sans-serif;
  --font-b: 'DM Sans', sans-serif;
  --font-m: 'DM Mono', monospace;
  --sidebar: 252px;
  --radius: 10px;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);min-height:100vh;overflow-x:hidden;}

/* ── SIDEBAR ── */
.sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sidebar);background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;}
.logo{padding:26px 22px 18px;border-bottom:1px solid var(--border);}
.logo-mark{font-family:var(--font-d);font-size:30px;letter-spacing:3px;color:var(--accent);line-height:1;}
.logo-sub{font-size:9px;letter-spacing:3.5px;color:var(--muted);text-transform:uppercase;margin-top:3px;}
.logo-badge{display:inline-block;background:rgba(240,192,64,0.12);border:1px solid rgba(240,192,64,0.25);color:var(--accent);font-size:9px;letter-spacing:1.5px;padding:2px 7px;border-radius:20px;margin-top:6px;font-family:var(--font-m);}
.nav{flex:1;padding:14px 10px;display:flex;flex-direction:column;gap:2px;overflow-y:auto;}
.nav-section{font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:var(--muted);padding:12px 12px 6px;margin-top:4px;}
.nav-item{display:flex;align-items:center;gap:11px;padding:9px 13px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:var(--text2);transition:all 0.15s;border:1px solid transparent;position:relative;}
.nav-item:hover{color:var(--text);background:var(--surface2);}
.nav-item.active{color:var(--accent);background:rgba(240,192,64,0.07);border-color:rgba(240,192,64,0.14);}
.nav-icon{font-size:16px;width:18px;text-align:center;flex-shrink:0;}
.nav-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-family:var(--font-m);padding:1px 6px;border-radius:10px;font-weight:600;}
.sidebar-footer{padding:14px;border-top:1px solid var(--border);font-size:11px;color:var(--muted);}
.s-dot{display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--green);margin-right:6px;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.35;}}

/* ── MAIN ── */
.main{margin-left:var(--sidebar);min-height:100vh;}
.topbar{position:sticky;top:0;background:rgba(8,10,15,0.9);backdrop-filter:blur(14px);border-bottom:1px solid var(--border);padding:13px 28px;display:flex;align-items:center;justify-content:space-between;z-index:50;}
.page-title{font-family:var(--font-d);font-size:24px;letter-spacing:2px;color:var(--text);}
.topbar-right{display:flex;align-items:center;gap:10px;}
.search-box{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:8px 13px;color:var(--text);font-family:var(--font-b);font-size:13px;width:210px;outline:none;transition:border-color .15s;}
.search-box:focus{border-color:var(--accent);}
.search-box::placeholder{color:var(--muted);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-family:var(--font-b);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;letter-spacing:.2px;}
.btn-primary{background:var(--accent);color:#080a0f;}
.btn-primary:hover{background:#f5d060;transform:translateY(-1px);}
.btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border);}
.btn-ghost:hover{color:var(--text);border-color:var(--border2);}
.btn-danger{background:rgba(240,80,96,0.1);color:var(--red);border:1px solid rgba(240,80,96,0.2);}
.btn-danger:hover{background:rgba(240,80,96,0.2);}
.btn-teal{background:rgba(48,192,176,0.1);color:var(--teal);border:1px solid rgba(48,192,176,0.2);}
.btn-teal:hover{background:rgba(48,192,176,0.2);}
.btn-purple{background:rgba(157,110,240,0.1);color:var(--purple);border:1px solid rgba(157,110,240,0.2);}
.btn-purple:hover{background:rgba(157,110,240,0.2);}
.btn-sm{padding:5px 11px;font-size:12px;}
.content{padding:28px;}
.page{display:none;}
.page.active{display:block;animation:fadeIn .2s ease;}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}

/* ── STATS ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:26px;}
.stats-grid-3{grid-template-columns:repeat(3,1fr);}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;position:relative;overflow:hidden;transition:transform .2s;}
.stat-card:hover{transform:translateY(-2px);}
.stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.stat-card.c-yellow::after{background:var(--accent);}
.stat-card.c-green::after{background:var(--green);}
.stat-card.c-red::after{background:var(--red);}
.stat-card.c-blue::after{background:var(--blue);}
.stat-card.c-purple::after{background:var(--purple);}
.stat-card.c-teal::after{background:var(--teal);}
.stat-label{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;}
.stat-value{font-family:var(--font-d);font-size:38px;letter-spacing:1px;line-height:1;}
.stat-card.c-yellow .stat-value{color:var(--accent);}
.stat-card.c-green .stat-value{color:var(--green);}
.stat-card.c-red .stat-value{color:var(--red);}
.stat-card.c-blue .stat-value{color:var(--blue);}
.stat-card.c-purple .stat-value{color:var(--purple);}
.stat-card.c-teal .stat-value{color:var(--teal);}
.stat-sub{font-size:11px;color:var(--muted);margin-top:5px;}

/* ── SECTION ── */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.section-title{font-family:var(--font-d);font-size:18px;letter-spacing:1.5px;color:var(--text);}

/* ── TABLE ── */
.table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:26px;}
.table-scroll{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{padding:12px 16px;text-align:left;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--border);background:var(--surface2);font-weight:500;}
tbody td{padding:13px 16px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr{transition:background .1s;}
tbody tr:hover{background:rgba(255,255,255,0.015);}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;}
.badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0;}
.b-available{background:rgba(46,204,138,0.1);color:var(--green);border:1px solid rgba(46,204,138,0.2);}
.b-available::before{background:var(--green);}
.b-rented{background:rgba(240,192,64,0.1);color:var(--accent);border:1px solid rgba(240,192,64,0.2);}
.b-rented::before{background:var(--accent);}
.b-maintenance{background:rgba(240,80,96,0.1);color:var(--red);border:1px solid rgba(240,80,96,0.2);}
.b-maintenance::before{background:var(--red);}
.b-active{background:rgba(46,204,138,0.1);color:var(--green);border:1px solid rgba(46,204,138,0.2);}
.b-active::before{background:var(--green);}
.b-returned{background:rgba(77,142,240,0.1);color:var(--blue);border:1px solid rgba(77,142,240,0.2);}
.b-returned::before{background:var(--blue);}
.b-overdue{background:rgba(240,80,96,0.1);color:var(--red);border:1px solid rgba(240,80,96,0.2);animation:blink 1.5s infinite;}
.b-overdue::before{background:var(--red);}
@keyframes blink{0%,100%{opacity:1;}50%{opacity:.5;}}
.b-basic{background:rgba(77,142,240,0.1);color:var(--blue);border:1px solid rgba(77,142,240,0.2);}
.b-basic::before{background:var(--blue);}
.b-standard{background:rgba(46,204,138,0.1);color:var(--green);border:1px solid rgba(46,204,138,0.2);}
.b-standard::before{background:var(--green);}
.b-premium{background:rgba(157,110,240,0.1);color:var(--purple);border:1px solid rgba(157,110,240,0.2);}
.b-premium::before{background:var(--purple);}
.b-scheduled{background:rgba(48,192,176,0.1);color:var(--teal);border:1px solid rgba(48,192,176,0.2);}
.b-scheduled::before{background:var(--teal);}
.b-inprogress{background:rgba(240,192,64,0.1);color:var(--accent);border:1px solid rgba(240,192,64,0.2);}
.b-inprogress::before{background:var(--accent);}
.b-done{background:rgba(77,142,240,0.1);color:var(--blue);border:1px solid rgba(77,142,240,0.2);}
.b-done::before{background:var(--blue);}
.b-overdue-m{background:rgba(240,80,96,0.1);color:var(--red);border:1px solid rgba(240,80,96,0.2);}
.b-overdue-m::before{background:var(--red);}

/* ── VEHICLE THUMB ── */
.vthumb{display:flex;align-items:center;gap:11px;}
.vthumb-icon{width:34px;height:34px;background:var(--surface2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:17px;border:1px solid var(--border);flex-shrink:0;}
.vthumb-name{font-weight:500;font-size:13px;}
.vthumb-plate{font-family:var(--font-m);font-size:10px;color:var(--muted);margin-top:1px;}

/* ── CUSTOMER AVATAR ── */
.cinfo{display:flex;align-items:center;gap:11px;}
.cavatar{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#080a0f;flex-shrink:0;}
.cname{font-weight:500;font-size:13px;}
.cemail{font-size:11px;color:var(--muted);margin-top:1px;}

.actions{display:flex;gap:5px;flex-wrap:wrap;}
.mono{font-family:var(--font-m);font-size:12px;}

/* ── FILTERS ── */
.filters{display:flex;gap:7px;margin-bottom:14px;flex-wrap:wrap;}
.filter-btn{padding:5px 13px;border-radius:20px;font-size:12px;font-weight:500;cursor:pointer;border:1px solid var(--border);background:var(--surface);color:var(--muted);transition:all .15s;}
.filter-btn:hover{color:var(--text);border-color:var(--border2);}
.filter-btn.active{background:var(--accent);color:#080a0f;border-color:var(--accent);}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(5px);z-index:200;display:none;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:var(--surface);border:1px solid var(--border2);border-radius:14px;width:560px;max-width:95vw;max-height:92vh;overflow-y:auto;animation:modalIn .2s ease;}
@keyframes modalIn{from{opacity:0;transform:translateY(14px) scale(.97);}to{opacity:1;transform:translateY(0) scale(1);}}
.modal-header{padding:22px 26px 0;display:flex;align-items:center;justify-content:space-between;}
.modal-title{font-family:var(--font-d);font-size:22px;letter-spacing:1.5px;}
.modal-close{width:28px;height:28px;background:var(--surface2);border:1px solid var(--border);border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;color:var(--muted);transition:all .15s;}
.modal-close:hover{color:var(--text);}
.modal-body{padding:22px 26px 26px;}

/* ── FORM ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.full{grid-column:1/-1;}
label{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted);font-weight:500;}
input,select,textarea{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:9px 13px;color:var(--text);font-family:var(--font-b);font-size:13px;outline:none;transition:border-color .15s;width:100%;}
input:focus,select:focus,textarea:focus{border-color:var(--accent);}
select option{background:var(--surface2);}
textarea{resize:vertical;min-height:70px;}
.form-actions{display:flex;gap:9px;justify-content:flex-end;margin-top:20px;padding-top:18px;border-top:1px solid var(--border);}

/* ── CHART ── */
.chart-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;margin-bottom:26px;}
.chart-bars{display:flex;align-items:flex-end;gap:7px;height:130px;margin-top:18px;}
.bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;height:100%;justify-content:flex-end;}
.bar{width:100%;border-radius:4px 4px 0 0;opacity:.75;transition:opacity .15s;min-height:3px;position:relative;}
.bar:hover{opacity:1;}
.bar-label{font-size:10px;color:var(--muted);font-family:var(--font-m);}

/* ── INSURANCE CARD ── */
.ins-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:26px;}
.ins-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;position:relative;overflow:hidden;transition:transform .2s,border-color .2s;}
.ins-card:hover{transform:translateY(-2px);}
.ins-card.plan-basic{border-top:3px solid var(--blue);}
.ins-card.plan-standard{border-top:3px solid var(--green);}
.ins-card.plan-premium{border-top:3px solid var(--purple);}
.ins-plan-name{font-family:var(--font-d);font-size:22px;letter-spacing:1.5px;margin-bottom:4px;}
.ins-plan-basic .ins-plan-name{color:var(--blue);}
.ins-plan-standard .ins-plan-name{color:var(--green);}
.ins-plan-premium .ins-plan-name{color:var(--purple);}
.ins-price{font-family:var(--font-m);font-size:20px;margin-bottom:10px;}
.ins-features{display:flex;flex-direction:column;gap:5px;margin-top:12px;}
.ins-feat{font-size:12px;color:var(--text2);display:flex;align-items:center;gap:7px;}
.ins-feat span{color:var(--green);font-size:14px;}
.ins-feat.no span{color:var(--muted);}

/* ── MAINTENANCE ── */
.maint-progress{height:6px;background:var(--surface2);border-radius:3px;overflow:hidden;margin-top:4px;}
.maint-bar{height:100%;border-radius:3px;transition:width .6s;}

/* ── EMPTY ── */
.empty-state{padding:44px;text-align:center;color:var(--muted);}
.empty-icon{font-size:36px;margin-bottom:10px;}
.empty-text{font-size:13px;}

/* ── TOAST ── */
.toast{position:fixed;bottom:22px;right:22px;background:var(--surface);border:1px solid var(--border2);border-radius:9px;padding:13px 18px;font-size:13px;z-index:999;display:none;align-items:center;gap:10px;box-shadow:0 8px 30px rgba(0,0,0,.45);animation:toastIn .2s ease;}
.toast.show{display:flex;}
.toast.success{border-left:3px solid var(--green);}
.toast.error{border-left:3px solid var(--red);}
.toast.warn{border-left:3px solid var(--accent);}
@keyframes toastIn{from{opacity:0;transform:translateX(18px);}to{opacity:1;transform:translateX(0);}}

/* ── CONFIRM DIALOG ── */
.confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);z-index:300;display:none;align-items:center;justify-content:center;}
.confirm-overlay.open{display:flex;}
.confirm-box{background:var(--surface);border:1px solid var(--border2);border-radius:12px;padding:28px;width:360px;max-width:94vw;animation:modalIn .2s ease;text-align:center;}
.confirm-icon{font-size:32px;margin-bottom:12px;}
.confirm-title{font-family:var(--font-d);font-size:20px;letter-spacing:1px;margin-bottom:8px;}
.confirm-msg{font-size:13px;color:var(--text2);margin-bottom:22px;line-height:1.6;}
.confirm-actions{display:flex;gap:10px;justify-content:center;}

/* ── REVENUE ── */
.rev-row{display:flex;align-items:center;gap:14px;}
.rev-label{width:140px;font-size:12px;color:var(--muted);font-family:var(--font-m);flex-shrink:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rev-track{flex:1;background:var(--surface2);border-radius:3px;height:10px;overflow:hidden;}
.rev-fill{height:100%;border-radius:3px;transition:width .6s;}
.rev-val{font-family:var(--font-m);font-size:12px;color:var(--text);width:68px;text-align:right;}

/* ── RESPONSIVE HELPERS ── */
@media(max-width:900px){
  .sidebar{width:60px;}
  .logo-mark,.logo-sub,.logo-badge,.nav-label,.nav-badge,.sidebar-footer{display:none;}
  .main{margin-left:60px;}
  .stats-grid{grid-template-columns:1fr 1fr;}
  .ins-cards{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="logo">
    <div class="logo-mark">FleetForge</div>
    <div class="logo-sub">Rental Management</div>
    <div class="logo-badge">PRO</div>
  </div>
  <div class="nav">
    <div class="nav-section">Overview</div>
    <div class="nav-item active" onclick="navigate('dashboard',this)" data-page="dashboard">
      <span class="nav-icon">◈</span><span class="nav-label">Dashboard</span>
    </div>

    <div class="nav-section">Fleet</div>
    <div class="nav-item" onclick="navigate('vehicles',this)" data-page="vehicles">
      <span class="nav-icon">🚗</span><span class="nav-label">Vehicles</span>
    </div>
    <div class="nav-item" onclick="navigate('maintenance',this)" data-page="maintenance">
      <span class="nav-icon">🔧</span><span class="nav-label">Maintenance</span>
      <span class="nav-badge" id="maint-badge">0</span>
    </div>

    <div class="nav-section">Business</div>
    <div class="nav-item" onclick="navigate('rentals',this)" data-page="rentals">
      <span class="nav-icon">📋</span><span class="nav-label">Rentals</span>
      <span class="nav-badge" id="overdue-badge" style="display:none">!</span>
    </div>
    <div class="nav-item" onclick="navigate('insurance',this)" data-page="insurance">
      <span class="nav-icon">🛡️</span><span class="nav-label">Insurance</span>
    </div>
    <div class="nav-item" onclick="navigate('customers',this)" data-page="customers">
      <span class="nav-icon">👤</span><span class="nav-label">Customers</span>
    </div>
    <div class="nav-item" onclick="navigate('revenue',this)" data-page="revenue">
      <span class="nav-icon">💰</span><span class="nav-label">Revenue</span>
    </div>
  </div>
  <div class="sidebar-footer"><span class="s-dot"></span>System Online</div>
</nav>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="page-title" id="topbar-title">DASHBOARD</div>
    <div class="topbar-right">
      <input class="search-box" type="text" placeholder="🔍  Search..." id="global-search" oninput="handleSearch(this.value)">
      <button class="btn btn-primary" onclick="openAddModal()">＋ Add New</button>
      <span style="font-size:12px;color:var(--text2);margin-left:6px;">
        <?= htmlspecialchars($me['full_name']) ?>
        <span class="badge <?= $me['role']==='admin' ? 'b-premium' : 'b-basic' ?>" style="margin-left:6px;"><?= htmlspecialchars($me['role']) ?></span>
      </span>
      <a href="logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
  </div>

  <div class="content">

    <!-- ── DASHBOARD ── -->
    <div id="page-dashboard" class="page active">
      <div class="stats-grid">
        <div class="stat-card c-yellow"><div class="stat-label">Total Fleet</div><div class="stat-value" id="s-total">0</div><div class="stat-sub">Registered vehicles</div></div>
        <div class="stat-card c-green"><div class="stat-label">Available</div><div class="stat-value" id="s-avail">0</div><div class="stat-sub">Ready to rent</div></div>
        <div class="stat-card c-red"><div class="stat-label">On Rent</div><div class="stat-value" id="s-rented">0</div><div class="stat-sub">Currently active</div></div>
        <div class="stat-card c-blue"><div class="stat-label">Revenue</div><div class="stat-value" id="s-rev">$0</div><div class="stat-sub">Total earned</div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:26px;">
        <div class="stat-card c-purple"><div class="stat-label">Active Insurance</div><div class="stat-value" id="s-ins">0</div><div class="stat-sub">Coverage active</div></div>
        <div class="stat-card c-teal"><div class="stat-label">Maintenance Due</div><div class="stat-value" id="s-maint">0</div><div class="stat-sub">Scheduled / overdue</div></div>
      </div>

      <div class="chart-card">
        <div class="section-header"><div class="section-title">MONTHLY REVENUE</div></div>
        <div class="chart-bars" id="dash-chart"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div>
          <div class="section-header"><div class="section-title">RECENT RENTALS</div></div>
          <div class="table-wrap table-scroll">
            <table><thead><tr><th>Vehicle</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody id="recent-rentals-body"></tbody></table>
          </div>
        </div>
        <div>
          <div class="section-header"><div class="section-title">UPCOMING MAINTENANCE</div></div>
          <div class="table-wrap table-scroll">
            <table><thead><tr><th>Vehicle</th><th>Type</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody id="upcoming-maint-body"></tbody></table>
          </div>
        </div>
      </div>
    </div>

    <!-- ── VEHICLES ── -->
    <div id="page-vehicles" class="page">
      <div class="filters" id="vehicle-filters">
        <button class="filter-btn active" onclick="filterVehicles('all',this)">All</button>
        <button class="filter-btn" onclick="filterVehicles('available',this)">Available</button>
        <button class="filter-btn" onclick="filterVehicles('rented',this)">Rented</button>
        <button class="filter-btn" onclick="filterVehicles('maintenance',this)">Maintenance</button>
      </div>
      <div class="table-wrap table-scroll">
        <table><thead><tr><th>Vehicle</th><th>Type</th><th>Year</th><th>Rate/Day</th><th>Mileage</th><th>Insurance</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="vehicles-body"></tbody></table>
      </div>
    </div>

    <!-- ── MAINTENANCE ── -->
    <div id="page-maintenance" class="page">
      <div class="filters" id="maint-filters">
        <button class="filter-btn active" onclick="filterMaint('all',this)">All</button>
        <button class="filter-btn" onclick="filterMaint('scheduled',this)">Scheduled</button>
        <button class="filter-btn" onclick="filterMaint('inprogress',this)">In Progress</button>
        <button class="filter-btn" onclick="filterMaint('done',this)">Done</button>
        <button class="filter-btn" onclick="filterMaint('overdue',this)">Overdue</button>
      </div>
      <div class="table-wrap table-scroll">
        <table><thead><tr><th>Vehicle</th><th>Type</th><th>Description</th><th>Scheduled</th><th>Cost (est.)</th><th>Progress</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="maint-body"></tbody></table>
      </div>
    </div>

    <!-- ── RENTALS ── -->
    <div id="page-rentals" class="page">
      <div class="filters" id="rental-filters">
        <button class="filter-btn active" onclick="filterRentals('all',this)">All</button>
        <button class="filter-btn" onclick="filterRentals('active',this)">Active</button>
        <button class="filter-btn" onclick="filterRentals('returned',this)">Returned</button>
        <button class="filter-btn" onclick="filterRentals('overdue',this)">Overdue</button>
      </div>
      <div class="table-wrap table-scroll">
        <table><thead><tr><th>ID</th><th>Vehicle</th><th>Customer</th><th>Duration</th><th>Insurance</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="rentals-body"></tbody></table>
      </div>
    </div>

    <!-- ── INSURANCE ── -->
    <div id="page-insurance" class="page">
      <div class="ins-cards">
        <div class="ins-card plan-basic">
          <div class="ins-plan-name" style="color:var(--blue)">BASIC</div>
          <div class="ins-price"><span style="font-size:13px;color:var(--muted)">+</span> $8 <span style="font-size:12px;color:var(--muted)">/day</span></div>
          <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">Essential coverage</div>
          <div class="ins-features">
            <div class="ins-feat"><span>✓</span> Third-party liability</div>
            <div class="ins-feat"><span>✓</span> Theft protection</div>
            <div class="ins-feat no"><span style="color:var(--muted)">✗</span> Collision damage waiver</div>
            <div class="ins-feat no"><span style="color:var(--muted)">✗</span> Roadside assistance</div>
            <div class="ins-feat no"><span style="color:var(--muted)">✗</span> Personal accident cover</div>
          </div>
        </div>
        <div class="ins-card plan-standard">
          <div class="ins-plan-name" style="color:var(--green)">STANDARD</div>
          <div class="ins-price"><span style="font-size:13px;color:var(--muted)">+</span> $18 <span style="font-size:12px;color:var(--muted)">/day</span></div>
          <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">Most popular plan</div>
          <div class="ins-features">
            <div class="ins-feat"><span>✓</span> Third-party liability</div>
            <div class="ins-feat"><span>✓</span> Theft protection</div>
            <div class="ins-feat"><span>✓</span> Collision damage waiver</div>
            <div class="ins-feat"><span>✓</span> Roadside assistance</div>
            <div class="ins-feat no"><span style="color:var(--muted)">✗</span> Personal accident cover</div>
          </div>
        </div>
        <div class="ins-card plan-premium">
          <div class="ins-plan-name" style="color:var(--purple)">PREMIUM</div>
          <div class="ins-price"><span style="font-size:13px;color:var(--muted)">+</span> $32 <span style="font-size:12px;color:var(--muted)">/day</span></div>
          <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">Full protection</div>
          <div class="ins-features">
            <div class="ins-feat"><span>✓</span> Third-party liability</div>
            <div class="ins-feat"><span>✓</span> Theft protection</div>
            <div class="ins-feat"><span>✓</span> Collision damage waiver</div>
            <div class="ins-feat"><span>✓</span> Roadside assistance</div>
            <div class="ins-feat"><span>✓</span> Personal accident cover</div>
          </div>
        </div>
      </div>

      <div class="section-header"><div class="section-title">ACTIVE INSURANCE POLICIES</div><button class="btn btn-primary btn-sm" onclick="showInsModal(null)">＋ Add Policy</button></div>
      <div class="table-wrap table-scroll">
        <table><thead><tr><th>Policy #</th><th>Vehicle</th><th>Customer</th><th>Plan</th><th>Start</th><th>Expiry</th><th>Daily Cost</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody id="ins-body"></tbody></table>
      </div>
    </div>

    <!-- ── CUSTOMERS ── -->
    <div id="page-customers" class="page">
      <div class="table-wrap table-scroll">
        <table><thead><tr><th>Customer</th><th>Phone</th><th>License</th><th>Rentals</th><th>Total Spent</th><th>Actions</th></tr></thead>
        <tbody id="customers-body"></tbody></table>
      </div>
    </div>

    <!-- ── REVENUE ── -->
    <div id="page-revenue" class="page">
      <div class="stats-grid stats-grid-3">
        <div class="stat-card c-green"><div class="stat-label">Total Revenue</div><div class="stat-value" id="rev-total">$0</div><div class="stat-sub">All time</div></div>
        <div class="stat-card c-yellow"><div class="stat-label">This Month</div><div class="stat-value" id="rev-month">$0</div><div class="stat-sub">Current month</div></div>
        <div class="stat-card c-blue"><div class="stat-label">Avg / Rental</div><div class="stat-value" id="rev-avg">$0</div><div class="stat-sub">Per transaction</div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="chart-card"><div class="section-title" style="margin-bottom:14px;">REVENUE BY VEHICLE</div><div id="rev-breakdown" style="display:flex;flex-direction:column;gap:10px;"></div></div>
        <div class="chart-card"><div class="section-title" style="margin-bottom:14px;">INSURANCE REVENUE</div><div id="rev-insurance" style="display:flex;flex-direction:column;gap:10px;"></div></div>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModalBg(event)">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-title">ADD</div>
      <div class="modal-close" onclick="closeModal()">✕</div>
    </div>
    <div class="modal-body" id="modal-body"></div>
  </div>
</div>

<!-- CONFIRM DIALOG -->
<div class="confirm-overlay" id="confirm-overlay">
  <div class="confirm-box">
    <div class="confirm-icon" id="confirm-icon">⚠️</div>
    <div class="confirm-title" id="confirm-title">Are you sure?</div>
    <div class="confirm-msg" id="confirm-msg">This action cannot be undone.</div>
    <div class="confirm-actions">
      <button class="btn btn-ghost" onclick="closeConfirm()">Cancel</button>
      <button class="btn btn-danger" id="confirm-ok" onclick="">Confirm</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// =============================================================
//  FleetForge Pro — PHP/MySQL Backend Edition
//  All state is fetched from the WAMP server via fetch() calls
// =============================================================

const IS_ADMIN = <?= $me['role'] === 'admin' ? 'true' : 'false' ?>; // controls delete-button visibility

const API = {
  vehicles:    'api/vehicles.php',
  customers:   'api/customers.php',
  rentals:     'api/rentals.php',
  maintenance: 'api/maintenance.php',
  insurance:   'api/insurance.php',
  dashboard:   'api/dashboard.php',
};

const COLORS    = ['#f0c040','#2ecc8a','#4d8ef0','#e05a30','#9d6ef0','#f07040','#30c0b0'];
const INS_RATES = { basic: 8, standard: 18, premium: 32 };

let editTarget = null;
let state = {
  searchQuery: '',
  currentFilter: { vehicles:'all', rentals:'all', maint:'all' },
  // Caches populated by API calls
  vehicles: [], customers: [], rentals: [],
  maintenance: [], insurancePolicies: [],
};

// ── API HELPERS ───────────────────────────────────────────────
async function apiFetch(url, options = {}) {
  try {
    const res = await fetch(url, {
      headers: { 'Content-Type': 'application/json' },
      ...options,
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'API error');
    return json.data;
  } catch (e) {
    showToast(e.message, 'error');
    throw e;
  }
}

const apiGet    = (url) => apiFetch(url);
const apiPost   = (url, body) => apiFetch(url, { method:'POST', body: JSON.stringify(body) });
const apiPut    = (url, body) => apiFetch(url, { method:'PUT',  body: JSON.stringify(body) });
const apiDelete = (url)       => apiFetch(url, { method:'DELETE' });

// ── HELPERS ───────────────────────────────────────────────────
const fmt   = n  => '$'+Number(n).toLocaleString();
const today = () => new Date().toISOString().split('T')[0];
const getV  = id => state.vehicles.find(v => v.vehicle_id === id);
const getC  = id => state.customers.find(c => c.customer_id === id);

function vThumb(v) {
  if (!v) return '<span style="color:var(--muted);font-size:12px">—</span>';
  return `<div class="vthumb">
    <div class="vthumb-icon">${v.icon}</div>
    <div>
      <div class="vthumb-name">${v.name}</div>
      <div class="vthumb-plate">${v.plate}</div>
    </div>
  </div>`;
}

function vThumbFromRental(r) {
  return `<div class="vthumb">
    <div class="vthumb-icon">${r.icon || '🚗'}</div>
    <div>
      <div class="vthumb-name">${r.vehicle_name}</div>
      <div class="vthumb-plate">${r.plate}</div>
    </div>
  </div>`;
}

function emptyRow(cols, msg, icon) {
  return `<tr><td colspan="${cols}"><div class="empty-state"><div class="empty-icon">${icon}</div><div class="empty-text">${msg}</div></div></td></tr>`;
}

// ── NAVIGATION ────────────────────────────────────────────────
function navigate(page, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-'+page).classList.add('active');
  if (el) el.closest('.nav-item').classList.add('active');
  document.getElementById('topbar-title').textContent = page.toUpperCase();
  document.getElementById('global-search').value = '';
  state.searchQuery = '';
  renderPage(page);
}

function renderPage(p) {
  if (p === 'dashboard')   loadDashboard();
  if (p === 'vehicles')    loadVehicles();
  if (p === 'maintenance') loadMaintenance();
  if (p === 'rentals')     loadRentals();
  if (p === 'insurance')   loadInsurance();
  if (p === 'customers')   loadCustomers();
  if (p === 'revenue')     loadRevenue();
}

function handleSearch(val) {
  state.searchQuery = val.toLowerCase();
  const p = document.querySelector('.page.active').id.replace('page-', '');
  renderPage(p);
}

// ── DASHBOARD ─────────────────────────────────────────────────
async function loadDashboard() {
  const d = await apiGet(API.dashboard);
  document.getElementById('s-total').textContent  = d.fleet.total;
  document.getElementById('s-avail').textContent  = d.fleet.available;
  document.getElementById('s-rented').textContent = d.fleet.rented;
  document.getElementById('s-rev').textContent    = fmt(d.revenue);
  document.getElementById('s-ins').textContent    = d.ins_active;
  document.getElementById('s-maint').textContent  = d.maint_due;

  const ob = document.getElementById('overdue-badge');
  ob.style.display = d.overdue ? '' : 'none';
  ob.textContent = d.overdue;
  document.getElementById('maint-badge').textContent = d.maint_due;

  // Revenue chart
  const revData = await apiGet(API.dashboard + '?type=revenue');
  const maxR = Math.max(...revData.map(r => +r.total), 1);
  document.getElementById('dash-chart').innerHTML = revData.map(m => `
    <div class="bar-wrap">
      <div class="bar" style="height:${(m.total/maxR)*120}px;background:var(--accent)" title="${fmt(m.total)}"></div>
      <span class="bar-label">${m.label}</span>
    </div>`).join('');

  // Recent rentals
  document.getElementById('recent-rentals-body').innerHTML = d.recent_rentals.map(r => `
    <tr>
      <td><div class="vthumb"><div class="vthumb-icon">${r.icon}</div><div><div class="vthumb-name">${r.vehicle_name}</div></div></div></td>
      <td>${r.customer_name}</td>
      <td class="mono">${fmt(r.amount)}</td>
      <td><span class="badge b-${r.status}">${r.status}</span></td>
    </tr>`).join('') || emptyRow(4, 'No rentals yet', '📋');

  // Upcoming maintenance
  document.getElementById('upcoming-maint-body').innerHTML = d.upcoming_maint.length
    ? d.upcoming_maint.map(m => `
      <tr>
        <td><div class="vthumb-name" style="font-size:12px">${m.vehicle_name}</div></td>
        <td style="font-size:12px">${m.type}</td>
        <td class="mono">${m.scheduled}</td>
        <td><span class="badge b-${m.status}">${m.status}</span></td>
      </tr>`).join('')
    : `<tr><td colspan="4"><div class="empty-state" style="padding:20px"><div class="empty-text">No upcoming maintenance</div></div></td></tr>`;
}

// ── VEHICLES ──────────────────────────────────────────────────
async function loadVehicles() {
  const f = state.currentFilter.vehicles;
  const params = new URLSearchParams({ search: state.searchQuery });
  if (f !== 'all') params.set('status', f);
  state.vehicles = await apiGet(API.vehicles + '?' + params);
  renderVehicleTable();
}

function renderVehicleTable() {
  const data = state.vehicles;
  const tbody = document.getElementById('vehicles-body');
  if (!data.length) { tbody.innerHTML = emptyRow(8, 'No vehicles found', '🚫'); return; }
  tbody.innerHTML = data.map(v => {
    const activePol = state.insurancePolicies.find(i => i.vehicle_id === v.vehicle_id && i.status === 'active');
    const insLabel  = activePol
      ? `<span class="badge b-${activePol.plan_id}">${activePol.plan_name}</span>`
      : `<span style="color:var(--muted);font-size:12px">None</span>`;
    return `<tr>
      <td>${vThumb(v)}</td>
      <td style="font-size:12px">${v.type_name}</td>
      <td class="mono">${v.year}</td>
      <td class="mono">${fmt(v.rate_per_day)}</td>
      <td class="mono">${Number(v.mileage).toLocaleString()} km</td>
      <td>${insLabel}</td>
      <td><span class="badge b-${v.status}">${v.status}</span></td>
      <td><div class="actions">
        <button class="btn btn-ghost btn-sm" onclick="showVehicleModal('${v.vehicle_id}')">Edit</button>
        <button class="btn btn-teal btn-sm" onclick="openRentModal('${v.vehicle_id}')">Rent</button>
        ${IS_ADMIN ? `<button class="btn btn-danger btn-sm" onclick="askDelete('Delete ${v.name}?','Vehicle will be removed.',()=>deleteVehicle('${v.vehicle_id}'))">Delete</button>` : ''}
      </div></td>
    </tr>`;
  }).join('');
}

function filterVehicles(f, btn) {
  state.currentFilter.vehicles = f;
  document.querySelectorAll('#vehicle-filters .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadVehicles();
}

async function deleteVehicle(id) {
  await apiDelete(API.vehicles + '?id=' + id);
  showToast('Vehicle deleted', 'success');
  loadVehicles(); loadDashboard();
}

// ── MAINTENANCE ───────────────────────────────────────────────
async function loadMaintenance() {
  const f = state.currentFilter.maint;
  const params = new URLSearchParams({ search: state.searchQuery });
  if (f !== 'all') params.set('status', f);
  state.maintenance = await apiGet(API.maintenance + '?' + params);
  renderMaintenanceTable();
}

function renderMaintenanceTable() {
  const data  = state.maintenance;
  const tbody = document.getElementById('maint-body');
  if (!data.length) { tbody.innerHTML = emptyRow(8, 'No maintenance records', '🔧'); return; }
  tbody.innerHTML = data.map(m => {
    const barColor = m.status==='done' ? 'var(--blue)' : m.status==='overdue' ? 'var(--red)' : m.status==='inprogress' ? 'var(--accent)' : 'var(--teal)';
    return `<tr>
      <td><div class="vthumb"><div class="vthumb-icon">${m.icon}</div><div><div class="vthumb-name">${m.vehicle_name}</div><div class="vthumb-plate">${m.plate}</div></div></div></td>
      <td style="font-size:12px;font-weight:500">${m.type}</td>
      <td style="font-size:12px;color:var(--text2);max-width:180px">${m.description || ''}</td>
      <td class="mono">${m.scheduled}</td>
      <td class="mono">${fmt(m.cost_est)}</td>
      <td style="min-width:100px">
        <div style="font-size:10px;color:var(--muted);margin-bottom:3px">${m.progress}%</div>
        <div class="maint-progress"><div class="maint-bar" style="width:${m.progress}%;background:${barColor}"></div></div>
      </td>
      <td><span class="badge b-${m.status}">${m.status}</span></td>
      <td><div class="actions">
        ${m.status !== 'done' ? `<button class="btn btn-teal btn-sm" onclick="showMaintModal('${m.maint_id}')">Edit</button>` : ''}
        ${m.status === 'inprogress' ? `<button class="btn btn-ghost btn-sm" onclick="completeMaint('${m.maint_id}')">Complete</button>` : ''}
        ${m.status === 'scheduled' ? `<button class="btn btn-purple btn-sm" onclick="startMaint('${m.maint_id}')">Start</button>` : ''}
        ${IS_ADMIN ? `<button class="btn btn-danger btn-sm" onclick="askDelete('Delete record?','Record will be removed.',()=>deleteMaint('${m.maint_id}'))">Delete</button>` : ''}
      </div></td>
    </tr>`;
  }).join('');
}

function filterMaint(f, btn) {
  state.currentFilter.maint = f;
  document.querySelectorAll('#maint-filters .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadMaintenance();
}

async function startMaint(id) {
  await apiPut(API.maintenance, { maint_id: id, status: 'inprogress', progress: 20 });
  showToast('Maintenance started', 'success');
  loadMaintenance(); loadDashboard();
}

async function completeMaint(id) {
  await apiPut(API.maintenance, { maint_id: id, status: 'done', progress: 100 });
  showToast('Maintenance completed ✓', 'success');
  loadMaintenance(); loadVehicles(); loadDashboard();
}

async function deleteMaint(id) {
  await apiDelete(API.maintenance + '?id=' + id);
  showToast('Record deleted', 'success');
  loadMaintenance(); loadDashboard();
}

// ── RENTALS ───────────────────────────────────────────────────
async function loadRentals() {
  const f = state.currentFilter.rentals;
  const params = new URLSearchParams({ search: state.searchQuery });
  if (f !== 'all') params.set('status', f);
  state.rentals = await apiGet(API.rentals + '?' + params);
  renderRentalsTable();
}

function renderRentalsTable() {
  const data  = state.rentals;
  const tbody = document.getElementById('rentals-body');
  if (!data.length) { tbody.innerHTML = emptyRow(8, 'No rentals found', '📋'); return; }
  tbody.innerHTML = data.map(r => {
    const insLabel = r.plan_id && r.plan_id !== 'none'
      ? `<span class="badge b-${r.plan_id}">${r.plan_name}</span>`
      : `<span style="color:var(--muted);font-size:12px">None</span>`;
    return `<tr>
      <td class="mono">${r.rental_id}</td>
      <td>${vThumbFromRental(r)}</td>
      <td>${r.customer_name}</td>
      <td><div style="font-size:12px"><span class="mono">${r.start_date}</span> → <span class="mono">${r.end_date}</span></div>
          <div style="font-size:11px;color:var(--muted);margin-top:2px">${r.days} day${r.days>1?'s':''}</div></td>
      <td>${insLabel}</td>
      <td class="mono">${fmt(r.amount)}</td>
      <td><span class="badge b-${r.status}">${r.status}</span></td>
      <td><div class="actions">
        ${r.status === 'active' || r.status === 'overdue'
          ? `<button class="btn btn-ghost btn-sm" onclick="returnVehicle('${r.rental_id}')">Return</button>` : ''}
        ${IS_ADMIN ? `<button class="btn btn-danger btn-sm" onclick="askDelete('Delete rental ${r.rental_id}?','Record will be removed.',()=>deleteRental('${r.rental_id}'))">Delete</button>` : ''}
      </div></td>
    </tr>`;
  }).join('');
}

function filterRentals(f, btn) {
  state.currentFilter.rentals = f;
  document.querySelectorAll('#rental-filters .filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  loadRentals();
}

async function returnVehicle(id) {
  await apiPut(API.rentals, { rental_id: id, status: 'returned' });
  showToast('Vehicle returned ✓', 'success');
  loadRentals(); loadVehicles(); loadDashboard();
}

async function deleteRental(id) {
  await apiDelete(API.rentals + '?id=' + id);
  showToast('Rental removed', 'success');
  loadRentals(); loadDashboard();
}

// ── INSURANCE ─────────────────────────────────────────────────
async function loadInsurance() {
  const params = new URLSearchParams({ search: state.searchQuery });
  state.insurancePolicies = await apiGet(API.insurance + '?' + params);
  renderInsuranceTable();
}

function renderInsuranceTable() {
  const data  = state.insurancePolicies;
  const tbody = document.getElementById('ins-body');
  if (!data.length) { tbody.innerHTML = emptyRow(9, 'No insurance policies', '🛡️'); return; }
  tbody.innerHTML = data.map(i => `
    <tr>
      <td class="mono">${i.policy_id}</td>
      <td><div class="vthumb"><div class="vthumb-icon">${i.icon}</div><div><div class="vthumb-name">${i.vehicle_name}</div><div class="vthumb-plate">${i.plate}</div></div></div></td>
      <td>${i.customer_name}</td>
      <td><span class="badge b-${i.plan_id}">${i.plan_name}</span></td>
      <td class="mono">${i.start_date}</td>
      <td class="mono">${i.expiry_date}</td>
      <td class="mono">${fmt(i.daily_rate)}/day</td>
      <td><span class="badge b-${i.status}">${i.status}</span></td>
      <td><div class="actions">
        ${IS_ADMIN ? `<button class="btn btn-danger btn-sm" onclick="askDelete('Delete policy ${i.policy_id}?','Policy will be removed.',()=>deleteInsurance('${i.policy_id}'))">Delete</button>` : ''}
      </div></td>
    </tr>`).join('');
}

async function deleteInsurance(id) {
  await apiDelete(API.insurance + '?id=' + id);
  showToast('Policy removed', 'success');
  loadInsurance();
}

// ── CUSTOMERS ─────────────────────────────────────────────────
async function loadCustomers() {
  const params = new URLSearchParams({ search: state.searchQuery });
  state.customers = await apiGet(API.customers + '?' + params);
  renderCustomersTable();
}

function renderCustomersTable() {
  const data  = state.customers;
  const tbody = document.getElementById('customers-body');
  if (!data.length) { tbody.innerHTML = emptyRow(6, 'No customers found', '👤'); return; }
  tbody.innerHTML = data.map(c => {
    const initials = c.name.split(' ').map(n => n[0]).join('').slice(0, 2);
    return `<tr>
      <td><div class="cinfo">
        <div class="cavatar" style="background:${COLORS[c.color_index % COLORS.length]}">${initials}</div>
        <div><div class="cname">${c.name}</div><div class="cemail">${c.email}</div></div>
      </div></td>
      <td class="mono">${c.phone}</td>
      <td class="mono">${c.license_no}</td>
      <td class="mono">${c.rental_count}</td>
      <td class="mono">${fmt(c.total_spent)}</td>
      <td><div class="actions">
        <button class="btn btn-ghost btn-sm" onclick="showCustomerModal('${c.customer_id}')">Edit</button>
        ${IS_ADMIN ? `<button class="btn btn-danger btn-sm" onclick="askDelete('Delete customer?','${c.name} will be removed.',()=>deleteCustomer('${c.customer_id}'))">Delete</button>` : ''}
      </div></td>
    </tr>`;
  }).join('');
}

async function deleteCustomer(id) {
  await apiDelete(API.customers + '?id=' + id);
  showToast('Customer removed', 'success');
  loadCustomers();
}

// ── REVENUE ───────────────────────────────────────────────────
async function loadRevenue() {
  // Ensure rentals + insurance are loaded
  const [rentals, insurance, revChart] = await Promise.all([
    apiGet(API.rentals),
    apiGet(API.insurance),
    apiGet(API.dashboard + '?type=revenue'),
  ]);
  state.rentals = rentals;
  state.insurancePolicies = insurance;

  const total  = rentals.reduce((s, r) => s + +r.amount, 0);
  const nowKey = today().slice(0, 7);
  const month  = rentals.filter(r => r.start_date.startsWith(nowKey)).reduce((s, r) => s + +r.amount, 0);
  const avg    = rentals.length ? Math.round(total / rentals.length) : 0;

  document.getElementById('rev-total').textContent = fmt(total);
  document.getElementById('rev-month').textContent = fmt(month);
  document.getElementById('rev-avg').textContent   = fmt(avg);

  // Revenue by vehicle
  const byVehicle = {};
  rentals.forEach(r => { byVehicle[r.vehicle_name] = (byVehicle[r.vehicle_name] || 0) + +r.amount; });
  const maxV = Math.max(...Object.values(byVehicle), 1);
  document.getElementById('rev-breakdown').innerHTML = Object.entries(byVehicle)
    .sort((a,b) => b[1]-a[1])
    .map(([name, earned], i) => `
    <div class="rev-row">
      <div class="rev-label">${name}</div>
      <div class="rev-track"><div class="rev-fill" style="width:${(earned/maxV)*100}%;background:${COLORS[i%COLORS.length]}"></div></div>
      <div class="rev-val">${fmt(earned)}</div>
    </div>`).join('') || '<div style="color:var(--muted);font-size:13px">No rental revenue yet</div>';

  // Insurance revenue by plan
  const insRevByPlan = {};
  insurance.forEach(i => {
    const days = Math.max(1, Math.round((new Date(i.expiry_date) - new Date(i.start_date)) / 86400000));
    insRevByPlan[i.plan_id] = (insRevByPlan[i.plan_id] || 0) + days * +i.daily_rate;
  });
  const insColors = { basic: COLORS[2], standard: COLORS[1], premium: COLORS[4] };
  const maxI = Math.max(...Object.values(insRevByPlan), 1);
  document.getElementById('rev-insurance').innerHTML = Object.entries(insRevByPlan).map(([plan, rev]) => `
    <div class="rev-row">
      <div class="rev-label" style="text-transform:capitalize">${plan} Plan</div>
      <div class="rev-track"><div class="rev-fill" style="width:${(rev/maxI)*100}%;background:${insColors[plan]||COLORS[0]}"></div></div>
      <div class="rev-val">${fmt(rev)}</div>
    </div>`).join('') || '<div style="color:var(--muted);font-size:13px">No insurance revenue yet</div>';
}

// ── MODAL DISPATCHER ──────────────────────────────────────────
function openAddModal() {
  const p = document.querySelector('.page.active').id.replace('page-', '');
  if      (p === 'vehicles' || p === 'dashboard') showVehicleModal(null);
  else if (p === 'rentals')    showRentalModal(null);
  else if (p === 'customers')  showCustomerModal(null);
  else if (p === 'maintenance') showMaintModal(null);
  else if (p === 'insurance')  showInsModal(null);
  else showVehicleModal(null);
}

// ── VEHICLE MODAL ─────────────────────────────────────────────
async function showVehicleModal(id) {
  let v = null;
  if (id) v = (await apiGet(API.vehicles + '?id=' + id));
  editTarget = v;
  document.getElementById('modal-title').textContent = v ? 'EDIT VEHICLE' : 'ADD VEHICLE';
  document.getElementById('modal-body').innerHTML = `
    <div class="form-grid">
      <div class="form-group full"><label>Vehicle Name</label><input id="f-name" value="${v?.name||''}" placeholder="e.g. Toyota Camry"></div>
      <div class="form-group"><label>Plate Number</label><input id="f-plate" value="${v?.plate||''}" placeholder="ABC-1234"></div>
      <div class="form-group"><label>Type</label><select id="f-type">${['Sedan','SUV','Pickup','Van','Electric','Hatchback'].map(t=>`<option ${v?.type_name===t?'selected':''}>${t}</option>`).join('')}</select></div>
      <div class="form-group"><label>Year</label><input id="f-year" type="number" value="${v?.year||new Date().getFullYear()}" min="2000" max="2030"></div>
      <div class="form-group"><label>Rate / Day ($)</label><input id="f-rate" type="number" value="${v?.rate_per_day||50}" min="1"></div>
      <div class="form-group"><label>Mileage (km)</label><input id="f-mileage" type="number" value="${v?.mileage||0}" min="0"></div>
      <div class="form-group"><label>Status</label><select id="f-status">${['available','rented','maintenance'].map(s=>`<option ${v?.status===s?'selected':''}>${s}</option>`).join('')}</select></div>
      <div class="form-group"><label>Icon</label><select id="f-icon">${['🚗','🚙','🛻','🚘','🚐','⚡','🏎️','🚕'].map(i=>`<option ${v?.icon===i?'selected':''}>${i}</option>`).join('')}</select></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveVehicle()">Save Vehicle</button>
    </div>`;
  document.getElementById('modal-overlay').classList.add('open');
}

async function saveVehicle() {
  const name = document.getElementById('f-name').value.trim();
  const plate = document.getElementById('f-plate').value.trim();
  if (!name || !plate) { showToast('Name and plate are required', 'error'); return; }
  const body = {
    name, plate,
    type_name:    document.getElementById('f-type').value,
    year:         document.getElementById('f-year').value,
    rate_per_day: document.getElementById('f-rate').value,
    mileage:      document.getElementById('f-mileage').value,
    status:       document.getElementById('f-status').value,
    icon:         document.getElementById('f-icon').value,
  };
  if (editTarget) {
    await apiPut(API.vehicles, { vehicle_id: editTarget.vehicle_id, ...body });
    showToast('Vehicle updated ✓', 'success');
  } else {
    await apiPost(API.vehicles, body);
    showToast('Vehicle added ✓', 'success');
  }
  closeModal(); loadVehicles(); loadDashboard();
}

// ── CUSTOMER MODAL ────────────────────────────────────────────
async function showCustomerModal(id) {
  let c = null;
  if (id) c = await apiGet(API.customers + '?id=' + id);
  editTarget = c;
  document.getElementById('modal-title').textContent = c ? 'EDIT CUSTOMER' : 'ADD CUSTOMER';
  document.getElementById('modal-body').innerHTML = `
    <div class="form-grid">
      <div class="form-group full"><label>Full Name</label><input id="f-cname" value="${c?.name||''}" placeholder="Full name"></div>
      <div class="form-group"><label>Email</label><input id="f-email" type="email" value="${c?.email||''}" placeholder="email@example.com"></div>
      <div class="form-group"><label>Phone</label><input id="f-phone" value="${c?.phone||''}" placeholder="+92 300 ..."></div>
      <div class="form-group full"><label>License Number</label><input id="f-license" value="${c?.license_no||''}" placeholder="DL-XX-XXXXX"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveCustomer()">Save Customer</button>
    </div>`;
  document.getElementById('modal-overlay').classList.add('open');
}

async function saveCustomer() {
  const name = document.getElementById('f-cname').value.trim();
  if (!name) { showToast('Name is required', 'error'); return; }
  const body = {
    name,
    email:      document.getElementById('f-email').value.trim(),
    phone:      document.getElementById('f-phone').value.trim(),
    license_no: document.getElementById('f-license').value.trim(),
  };
  if (editTarget) {
    await apiPut(API.customers, { customer_id: editTarget.customer_id, ...body });
    showToast('Customer updated ✓', 'success');
  } else {
    await apiPost(API.customers, body);
    showToast('Customer added ✓', 'success');
  }
  closeModal(); loadCustomers();
}

// ── RENTAL MODAL ──────────────────────────────────────────────
async function showRentalModal(rental, preVehicle) {
  // Load available vehicles + customers
  const [vehicles, customers] = await Promise.all([
    apiGet(API.vehicles + '?status=available'),
    apiGet(API.customers),
  ]);
  state.vehicles  = vehicles;
  state.customers = customers;

  editTarget = rental;
  const td = today();
  document.getElementById('modal-title').textContent = 'NEW RENTAL';
  if (!vehicles.length) { showToast('No vehicles available', 'warn'); return; }

  document.getElementById('modal-body').innerHTML = `
    <div class="form-grid">
      <div class="form-group full"><label>Vehicle</label>
        <select id="f-rveh">${vehicles.map(v=>`<option value="${v.vehicle_id}" ${v.vehicle_id===preVehicle?'selected':''}>${v.name} (${v.plate}) — ${fmt(v.rate_per_day)}/day</option>`).join('')}</select></div>
      <div class="form-group full"><label>Customer</label>
        <select id="f-rcust">${customers.map(c=>`<option value="${c.customer_id}">${c.name}</option>`).join('')}</select></div>
      <div class="form-group"><label>Start Date</label><input id="f-rstart" type="date" value="${td}" oninput="calcRental()"></div>
      <div class="form-group"><label>End Date</label><input id="f-rend" type="date" value="${td}" oninput="calcRental()"></div>
      <div class="form-group full"><label>Insurance Plan</label>
        <select id="f-rins" onchange="calcRental()">
          <option value="none">None</option>
          <option value="basic">Basic (+$8/day)</option>
          <option value="standard">Standard (+$18/day)</option>
          <option value="premium">Premium (+$32/day)</option>
        </select></div>
      <div class="form-group full"><label>Total Amount ($)</label><input id="f-ramt" type="number" value="0" readonly style="opacity:.7"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveRental()">Create Rental</button>
    </div>`;
  document.getElementById('modal-overlay').classList.add('open');
  document.getElementById('f-rveh').addEventListener('change', calcRental);
  calcRental();
}

function calcRental() {
  const vId  = document.getElementById('f-rveh')?.value;
  const s    = document.getElementById('f-rstart')?.value;
  const e    = document.getElementById('f-rend')?.value;
  const ins  = document.getElementById('f-rins')?.value;
  if (!vId || !s || !e) return;
  const v    = state.vehicles.find(x => x.vehicle_id === vId);
  const days = Math.max(1, Math.round((new Date(e) - new Date(s)) / 86400000));
  const insCost = (ins && ins !== 'none') ? (INS_RATES[ins] || 0) : 0;
  document.getElementById('f-ramt').value = ((v?.rate_per_day || 0) * days + insCost * days).toFixed(2);
}

async function saveRental() {
  const vId   = document.getElementById('f-rveh').value;
  const cId   = document.getElementById('f-rcust').value;
  const start = document.getElementById('f-rstart').value;
  const end   = document.getElementById('f-rend').value;
  const plan  = document.getElementById('f-rins').value;
  if (!vId || !cId || !start || !end) { showToast('All fields required', 'error'); return; }
  if (new Date(end) < new Date(start)) { showToast('End date must be after start', 'error'); return; }
  await apiPost(API.rentals, { vehicle_id: vId, customer_id: cId, start_date: start, end_date: end, plan_id: plan });
  // Auto-create insurance policy when not none
  if (plan && plan !== 'none') {
    await apiPost(API.insurance, { vehicle_id: vId, customer_id: cId, plan_id: plan, start_date: start, expiry_date: end });
  }
  closeModal(); showToast('Rental created ✓', 'success');
  loadRentals(); loadVehicles(); loadDashboard();
}

function openRentModal(vehicleId) { showRentalModal(null, vehicleId); }

// ── MAINTENANCE MODAL ─────────────────────────────────────────
async function showMaintModal(id) {
  let m = null;
  if (id) m = await apiGet(API.maintenance + '?id=' + id);
  // Ensure vehicles loaded
  if (!state.vehicles.length) state.vehicles = await apiGet(API.vehicles);
  editTarget = m;
  document.getElementById('modal-title').textContent = m ? 'EDIT MAINTENANCE' : 'SCHEDULE MAINTENANCE';
  document.getElementById('modal-body').innerHTML = `
    <div class="form-grid">
      <div class="form-group full"><label>Vehicle</label>
        <select id="f-mveh">${state.vehicles.map(v=>`<option value="${v.vehicle_id}" ${m?.vehicle_id===v.vehicle_id?'selected':''}>${v.name} (${v.plate})</option>`).join('')}</select></div>
      <div class="form-group"><label>Service Type</label>
        <select id="f-mtype">${['Oil Change','Tyre Rotation','Brake Pads','Engine Service','AC Service','Battery Check','Body Work','Full Inspection','Other'].map(t=>`<option ${m?.type===t?'selected':''}>${t}</option>`).join('')}</select></div>
      <div class="form-group"><label>Scheduled Date</label><input id="f-mdate" type="date" value="${m?.scheduled||today()}"></div>
      <div class="form-group"><label>Estimated Cost ($)</label><input id="f-mcost" type="number" value="${m?.cost_est||100}" min="0"></div>
      <div class="form-group full"><label>Description</label><textarea id="f-mdesc" placeholder="Service details...">${m?.description||''}</textarea></div>
      <div class="form-group"><label>Status</label>
        <select id="f-mstatus">${['scheduled','inprogress','done','overdue'].map(s=>`<option ${m?.status===s?'selected':''}>${s}</option>`).join('')}</select></div>
      <div class="form-group"><label>Progress (%)</label><input id="f-mprog" type="number" value="${m?.progress||0}" min="0" max="100"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveMaint()">Save</button>
    </div>`;
  document.getElementById('modal-overlay').classList.add('open');
}

async function saveMaint() {
  const date = document.getElementById('f-mdate').value;
  if (!date) { showToast('Date is required', 'error'); return; }
  const body = {
    vehicle_id:   document.getElementById('f-mveh').value,
    mtype_name:   document.getElementById('f-mtype').value,
    description:  document.getElementById('f-mdesc').value.trim(),
    scheduled:    date,
    cost_est:     document.getElementById('f-mcost').value,
    progress:     document.getElementById('f-mprog').value,
    status:       document.getElementById('f-mstatus').value,
  };
  if (editTarget) {
    await apiPut(API.maintenance, { maint_id: editTarget.maint_id, ...body });
    showToast('Record updated ✓', 'success');
  } else {
    await apiPost(API.maintenance, body);
    showToast('Maintenance scheduled ✓', 'success');
  }
  closeModal(); loadMaintenance(); loadDashboard(); loadVehicles();
}

// ── INSURANCE MODAL ───────────────────────────────────────────
async function showInsModal(id) {
  if (!state.vehicles.length) state.vehicles = await apiGet(API.vehicles);
  if (!state.customers.length) state.customers = await apiGet(API.customers);
  const td = today();
  document.getElementById('modal-title').textContent = 'ADD INSURANCE POLICY';
  document.getElementById('modal-body').innerHTML = `
    <div class="form-grid">
      <div class="form-group full"><label>Vehicle</label>
        <select id="f-iveh">${state.vehicles.map(v=>`<option value="${v.vehicle_id}">${v.name} (${v.plate})</option>`).join('')}</select></div>
      <div class="form-group full"><label>Customer</label>
        <select id="f-icust">${state.customers.map(c=>`<option value="${c.customer_id}">${c.name}</option>`).join('')}</select></div>
      <div class="form-group full"><label>Plan</label>
        <select id="f-iplan">
          <option value="basic">Basic — $8/day</option>
          <option value="standard">Standard — $18/day</option>
          <option value="premium">Premium — $32/day</option>
        </select></div>
      <div class="form-group"><label>Start Date</label><input id="f-istart" type="date" value="${td}"></div>
      <div class="form-group"><label>Expiry Date</label><input id="f-iexpiry" type="date" value="${td}"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveIns()">Save Policy</button>
    </div>`;
  document.getElementById('modal-overlay').classList.add('open');
}

async function saveIns() {
  const start  = document.getElementById('f-istart').value;
  const expiry = document.getElementById('f-iexpiry').value;
  if (!start || !expiry) { showToast('Dates are required', 'error'); return; }
  if (new Date(expiry) < new Date(start)) { showToast('Expiry must be after start', 'error'); return; }
  await apiPost(API.insurance, {
    vehicle_id:  document.getElementById('f-iveh').value,
    customer_id: document.getElementById('f-icust').value,
    plan_id:     document.getElementById('f-iplan').value,
    start_date:  start,
    expiry_date: expiry,
  });
  closeModal(); loadInsurance(); loadDashboard();
  showToast('Policy added ✓', 'success');
}

// ── MODAL HELPERS ─────────────────────────────────────────────
function closeModal()    { document.getElementById('modal-overlay').classList.remove('open'); editTarget = null; }
function closeModalBg(e) { if (e.target === document.getElementById('modal-overlay')) closeModal(); }

// ── CONFIRM DIALOG ────────────────────────────────────────────
function askDelete(title, msg, cb) {
  document.getElementById('confirm-title').textContent = title;
  document.getElementById('confirm-msg').textContent   = msg;
  document.getElementById('confirm-ok').onclick = () => { closeConfirm(); cb(); };
  document.getElementById('confirm-overlay').classList.add('open');
}
function closeConfirm() { document.getElementById('confirm-overlay').classList.remove('open'); }

// ── TOAST ─────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = (type==='success'?'✓  ':type==='warn'?'⚠  ':'✗  ') + msg;
  t.className = `toast show ${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = 'toast'; }, 3200);
}

// ── INIT ──────────────────────────────────────────────────────
loadDashboard();
</script>
</body>
</html>
