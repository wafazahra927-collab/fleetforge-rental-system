<?php
// ── api/rentals.php ────────────────────────────────────────────
// GET    /api/rentals.php             → list rentals (with joins)
// GET    /api/rentals.php?id=R001     → single rental
// POST   /api/rentals.php             → create rental
// PUT    /api/rentals.php             → update rental (status, dates, etc.)
// DELETE /api/rentals.php?id=R001     → delete rental
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// Common SELECT fragment (joins vehicles + customers + plans)
const RENTAL_SELECT = '
    SELECT r.*,
           v.name   AS vehicle_name, v.plate, v.icon, v.rate_per_day,
           c.name   AS customer_name, c.email AS customer_email,
           ip.plan_name, ip.daily_rate AS insurance_rate
    FROM rentals r
    JOIN vehicles v  ON r.vehicle_id  = v.vehicle_id
    JOIN customers c ON r.customer_id = c.customer_id
    LEFT JOIN insurance_plans ip ON r.plan_id = ip.plan_id
';

switch ($method) {

    // ── LIST ───────────────────────────────────────────────────
    case 'GET':
        if (!empty($_GET['id'])) {
            $s = $pdo->prepare(RENTAL_SELECT.' WHERE r.rental_id = ?');
            $s->execute([$_GET['id']]);
            $row = $s->fetch();
            $row ? success($row) : error('Rental not found', 404);
        }

        $status = $_GET['status'] ?? 'all';
        $search = '%'.($_GET['search'] ?? '').'%';

        $sql    = RENTAL_SELECT.'WHERE (v.name LIKE ? OR c.name LIKE ? OR r.rental_id LIKE ?)';
        $params = [$search, $search, $search];

        if ($status !== 'all') { $sql .= ' AND r.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY r.rental_id DESC';

        $s = $pdo->prepare($sql);
        $s->execute($params);
        success($s->fetchAll());
        break;

    // ── CREATE ─────────────────────────────────────────────────
    case 'POST':
        $b = jsonBody();
        foreach (['vehicle_id','customer_id','start_date','end_date'] as $f)
            if (empty($b[$f])) error("Field '$f' is required");

        // Check vehicle is available
        $chk = $pdo->prepare("SELECT status FROM vehicles WHERE vehicle_id = ?");
        $chk->execute([$b['vehicle_id']]);
        $veh = $chk->fetch();
        if (!$veh) error('Vehicle not found', 404);
        if ($veh['status'] !== 'available') error('Vehicle is not available');

        $start  = new DateTime($b['start_date']);
        $end    = new DateTime($b['end_date']);
        if ($end <= $start) error('End date must be after start date');

        $days   = (int)$start->diff($end)->days;
        $planId = $b['plan_id'] ?? 'none';

        // Get vehicle rate
        $vs = $pdo->prepare('SELECT rate_per_day FROM vehicles WHERE vehicle_id = ?');
        $vs->execute([$b['vehicle_id']]);
        $rate = (float)$vs->fetchColumn();

        // Get insurance daily rate
        $is = $pdo->prepare('SELECT daily_rate FROM insurance_plans WHERE plan_id = ?');
        $is->execute([$planId]);
        $insRate = (float)($is->fetchColumn() ?? 0);

        $amount = ($rate + $insRate) * $days;
        $id     = nextId('R', 'rentals', 'rental_id', $pdo);

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO rentals (rental_id,vehicle_id,customer_id,start_date,end_date,days,amount,plan_id,status)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            )->execute([$id, $b['vehicle_id'], $b['customer_id'],
                        $b['start_date'], $b['end_date'], $days, $amount, $planId, 'active']);

            // Mark vehicle as rented
            $pdo->prepare("UPDATE vehicles SET status='rented' WHERE vehicle_id=?")->execute([$b['vehicle_id']]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error('Transaction failed: '.$e->getMessage(), 500);
        }
        success(['rental_id' => $id, 'amount' => $amount, 'days' => $days], 'Rental created');
        break;

    // ── UPDATE ─────────────────────────────────────────────────
    case 'PUT':
        $b = jsonBody();
        if (empty($b['rental_id'])) error('rental_id required');

        $pdo->beginTransaction();
        try {
            $fields = []; $params = [];
            foreach (['start_date','end_date','days','amount','plan_id'] as $f)
                if (isset($b[$f])) { $fields[] = "$f = ?"; $params[] = $b[$f]; }

            // If marking as returned
            if (isset($b['status'])) {
                $fields[] = 'status = ?'; $params[] = $b['status'];

                if ($b['status'] === 'returned') {
                    // Free the vehicle
                    $vid = $pdo->prepare('SELECT vehicle_id FROM rentals WHERE rental_id = ?');
                    $vid->execute([$b['rental_id']]);
                    $vehicleId = $vid->fetchColumn();
                    $pdo->prepare("UPDATE vehicles SET status='available' WHERE vehicle_id=?")->execute([$vehicleId]);
                }
            }

            if (!empty($fields)) {
                $params[] = $b['rental_id'];
                $pdo->prepare('UPDATE rentals SET '.implode(', ',$fields).' WHERE rental_id = ?')->execute($params);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error('Transaction failed: '.$e->getMessage(), 500);
        }
        success([], 'Rental updated');
        break;

    // ── DELETE ─────────────────────────────────────────────────
    case 'DELETE':
        requireAdmin(); // only admins may delete
        if (empty($_GET['id'])) error('id required');
        $chk = $pdo->prepare("SELECT status, vehicle_id FROM rentals WHERE rental_id = ?");
        $chk->execute([$_GET['id']]);
        $rental = $chk->fetch();
        if (!$rental) error('Rental not found', 404);

        $pdo->beginTransaction();
        try {
            if ($rental['status'] === 'active') {
                $pdo->prepare("UPDATE vehicles SET status='available' WHERE vehicle_id=?")->execute([$rental['vehicle_id']]);
            }
            $pdo->prepare('DELETE FROM rentals WHERE rental_id = ?')->execute([$_GET['id']]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error('Transaction failed: '.$e->getMessage(), 500);
        }
        success([], 'Rental deleted');
        break;

    default:
        error('Method not allowed', 405);
}
