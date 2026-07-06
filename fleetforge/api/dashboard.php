<?php
// ── api/dashboard.php ─────────────────────────────────────────
// GET /api/dashboard.php             → all dashboard stats
// GET /api/dashboard.php?type=revenue → 6-month revenue chart data
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo  = getDB();
$type = $_GET['type'] ?? 'stats';

if ($type === 'revenue') {
    // Last 6 months revenue by month
    $s = $pdo->query(
        "SELECT DATE_FORMAT(start_date,'%Y-%m') AS ym,
                DATE_FORMAT(start_date,'%b')    AS label,
                SUM(amount) AS total
         FROM rentals
         WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
         GROUP BY ym, label
         ORDER BY ym"
    );
    success($s->fetchAll());
}

// ── Main stats ───────────────────────────────────────────────
// Auto-mark overdue rentals
$pdo->prepare("UPDATE rentals SET status='overdue' WHERE status='active' AND end_date < CURDATE()")->execute();
// Auto-expire insurance
$pdo->prepare("UPDATE insurance_policies SET status='expired' WHERE expiry_date < CURDATE() AND status='active'")->execute();
// Auto-mark overdue maintenance
$pdo->prepare("UPDATE maintenance_records SET status='overdue' WHERE status='scheduled' AND scheduled < CURDATE()")->execute();

// Fleet counters
$fleet   = $pdo->query("SELECT status, COUNT(*) AS cnt FROM vehicles GROUP BY status")->fetchAll();
$total   = array_sum(array_column($fleet, 'cnt'));
$avail   = 0; $rented = 0; $maint = 0;
foreach ($fleet as $r) {
    if ($r['status']==='available')   $avail  = $r['cnt'];
    if ($r['status']==='rented')      $rented = $r['cnt'];
    if ($r['status']==='maintenance') $maint  = $r['cnt'];
}

// Revenue
$rev = $pdo->query("SELECT COALESCE(SUM(amount),0) AS total FROM rentals")->fetchColumn();

// Active insurance
$insActive = $pdo->query("SELECT COUNT(*) FROM insurance_policies WHERE status='active'")->fetchColumn();

// Maintenance due
$maintDue = $pdo->query("SELECT COUNT(*) FROM maintenance_records WHERE status IN ('scheduled','overdue','inprogress')")->fetchColumn();

// Overdue rentals
$overdue = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status='overdue'")->fetchColumn();

// Recent rentals (last 5)
$recentRentals = $pdo->query(
    "SELECT r.rental_id, r.amount, r.status,
            v.name AS vehicle_name, v.icon,
            c.name AS customer_name
     FROM rentals r
     JOIN vehicles  v ON r.vehicle_id  = v.vehicle_id
     JOIN customers c ON r.customer_id = c.customer_id
     ORDER BY r.rental_id DESC LIMIT 5"
)->fetchAll();

// Upcoming maintenance (next 5 non-done)
$upcomingMaint = $pdo->query(
    "SELECT m.maint_id, m.scheduled, m.status,
            v.name AS vehicle_name, v.icon,
            mt.mtype_name AS type
     FROM maintenance_records m
     JOIN vehicles          v  ON m.vehicle_id = v.vehicle_id
     JOIN maintenance_types mt ON m.mtype_id   = mt.mtype_id
     WHERE m.status != 'done'
     ORDER BY m.scheduled ASC LIMIT 5"
)->fetchAll();

success([
    'fleet' => [
        'total'       => (int)$total,
        'available'   => (int)$avail,
        'rented'      => (int)$rented,
        'maintenance' => (int)$maint,
    ],
    'revenue'       => (float)$rev,
    'ins_active'    => (int)$insActive,
    'maint_due'     => (int)$maintDue,
    'overdue'       => (int)$overdue,
    'recent_rentals'  => $recentRentals,
    'upcoming_maint'  => $upcomingMaint,
]);
