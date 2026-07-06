<?php
// ── api/vehicles.php ───────────────────────────────────────────
// GET    /api/vehicles.php            → list all vehicles
// GET    /api/vehicles.php?id=V001    → single vehicle
// POST   /api/vehicles.php            → create vehicle
// PUT    /api/vehicles.php            → update vehicle  (body: {vehicle_id,...})
// DELETE /api/vehicles.php?id=V001    → delete vehicle
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ── LIST / SINGLE ──────────────────────────────────────────
    case 'GET':
        if (!empty($_GET['id'])) {
            $s = $pdo->prepare(
                'SELECT v.*, t.type_name FROM vehicles v
                 JOIN vehicle_types t ON v.type_id = t.type_id
                 WHERE v.vehicle_id = ?'
            );
            $s->execute([$_GET['id']]);
            $row = $s->fetch();
            $row ? success($row) : error('Vehicle not found', 404);
        }

        $status = $_GET['status'] ?? 'all';
        $search = '%'.($_GET['search'] ?? '').'%';

        $sql = 'SELECT v.*, t.type_name FROM vehicles v
                JOIN vehicle_types t ON v.type_id = t.type_id
                WHERE (v.name LIKE ? OR v.plate LIKE ? OR t.type_name LIKE ?)';
        $params = [$search, $search, $search];

        if ($status !== 'all') { $sql .= ' AND v.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY v.vehicle_id';

        $s = $pdo->prepare($sql);
        $s->execute($params);
        success($s->fetchAll());
        break;

    // ── CREATE ─────────────────────────────────────────────────
    case 'POST':
        $b = jsonBody();
        foreach (['name','plate','type_name','year','rate_per_day'] as $f)
            if (empty($b[$f])) error("Field '$f' is required");

        // Resolve or create type
        $ts = $pdo->prepare('SELECT type_id FROM vehicle_types WHERE type_name = ?');
        $ts->execute([trim($b['type_name'])]);
        $type = $ts->fetch();
        if (!$type) {
            $pdo->prepare('INSERT INTO vehicle_types (type_name) VALUES (?)')->execute([trim($b['type_name'])]);
            $typeId = $pdo->lastInsertId();
        } else {
            $typeId = $type['type_id'];
        }

        $id = nextId('V', 'vehicles', 'vehicle_id', $pdo);
        $pdo->prepare(
            'INSERT INTO vehicles (vehicle_id,name,plate,type_id,year,rate_per_day,mileage,icon,status)
             VALUES (?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id,
            trim($b['name']),
            strtoupper(trim($b['plate'])),
            $typeId,
            (int)$b['year'],
            (float)$b['rate_per_day'],
            (int)($b['mileage'] ?? 0),
            $b['icon'] ?? '🚗',
            $b['status'] ?? 'available',
        ]);
        success(['vehicle_id' => $id], 'Vehicle added');
        break;

    // ── UPDATE ─────────────────────────────────────────────────
    case 'PUT':
        $b = jsonBody();
        if (empty($b['vehicle_id'])) error('vehicle_id required');

        $fields = []; $params = [];
        $map = ['name','plate','year','rate_per_day','mileage','icon','status'];
        foreach ($map as $f) {
            if (isset($b[$f])) { $fields[] = "$f = ?"; $params[] = $b[$f]; }
        }
        if (isset($b['type_name'])) {
            $ts = $pdo->prepare('SELECT type_id FROM vehicle_types WHERE type_name = ?');
            $ts->execute([trim($b['type_name'])]);
            $type = $ts->fetch();
            if (!$type) {
                $pdo->prepare('INSERT INTO vehicle_types (type_name) VALUES (?)')->execute([trim($b['type_name'])]);
                $typeId = $pdo->lastInsertId();
            } else { $typeId = $type['type_id']; }
            $fields[] = 'type_id = ?'; $params[] = $typeId;
        }
        if (empty($fields)) error('Nothing to update');
        $params[] = $b['vehicle_id'];
        $pdo->prepare('UPDATE vehicles SET '.implode(', ', $fields).' WHERE vehicle_id = ?')->execute($params);
        success([], 'Vehicle updated');
        break;

    // ── DELETE ─────────────────────────────────────────────────
    case 'DELETE':
        requireAdmin(); // only admins may delete
        if (empty($_GET['id'])) error('id required');
        // Guard: do not delete if active rental exists
        $chk = $pdo->prepare("SELECT rental_id FROM rentals WHERE vehicle_id = ? AND status = 'active' LIMIT 1");
        $chk->execute([$_GET['id']]);
        if ($chk->fetch()) error('Vehicle has an active rental — cannot delete');
        $pdo->prepare('DELETE FROM vehicles WHERE vehicle_id = ?')->execute([$_GET['id']]);
        success([], 'Vehicle deleted');
        break;

    default:
        error('Method not allowed', 405);
}
