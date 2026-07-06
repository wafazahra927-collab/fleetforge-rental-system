<?php
// ── api/maintenance.php ────────────────────────────────────────
// GET    /api/maintenance.php          → list all records
// GET    /api/maintenance.php?id=M001  → single record
// POST   /api/maintenance.php          → create record
// PUT    /api/maintenance.php          → update record
// DELETE /api/maintenance.php?id=M001  → delete record
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

const MAINT_SELECT = '
    SELECT m.*, v.name AS vehicle_name, v.plate, v.icon, mt.mtype_name AS type
    FROM maintenance_records m
    JOIN vehicles v         ON m.vehicle_id = v.vehicle_id
    JOIN maintenance_types mt ON m.mtype_id = mt.mtype_id
';

switch ($method) {

    case 'GET':
        if (!empty($_GET['id'])) {
            $s = $pdo->prepare(MAINT_SELECT.' WHERE m.maint_id = ?');
            $s->execute([$_GET['id']]);
            $row = $s->fetch();
            $row ? success($row) : error('Record not found', 404);
        }

        $status = $_GET['status'] ?? 'all';
        $search = '%'.($_GET['search'] ?? '').'%';

        $sql    = MAINT_SELECT."WHERE (v.name LIKE ? OR v.plate LIKE ? OR mt.mtype_name LIKE ?)";
        $params = [$search, $search, $search];
        if ($status !== 'all') { $sql .= ' AND m.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY m.scheduled ASC';

        $s = $pdo->prepare($sql);
        $s->execute($params);
        success($s->fetchAll());
        break;

    case 'POST':
        $b = jsonBody();
        foreach (['vehicle_id','mtype_name','scheduled'] as $f)
            if (empty($b[$f])) error("Field '$f' is required");

        // Resolve or create maintenance type
        $ts = $pdo->prepare('SELECT mtype_id FROM maintenance_types WHERE mtype_name = ?');
        $ts->execute([trim($b['mtype_name'])]);
        $mt = $ts->fetch();
        if (!$mt) {
            $pdo->prepare('INSERT INTO maintenance_types (mtype_name) VALUES (?)')->execute([trim($b['mtype_name'])]);
            $mtId = $pdo->lastInsertId();
        } else { $mtId = $mt['mtype_id']; }

        $status = $b['status'] ?? 'scheduled';
        $progress = ($status === 'done') ? 100 : (int)($b['progress'] ?? 0);
        $id = nextId('M', 'maintenance_records', 'maint_id', $pdo);

        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'INSERT INTO maintenance_records (maint_id,vehicle_id,mtype_id,description,scheduled,cost_est,progress,status)
                 VALUES (?,?,?,?,?,?,?,?)'
            )->execute([
                $id, $b['vehicle_id'], $mtId,
                trim($b['description'] ?? ''),
                $b['scheduled'],
                (float)($b['cost_est'] ?? 0),
                $progress, $status,
            ]);
            // Set vehicle to maintenance if in-progress
            if ($status === 'inprogress') {
                $pdo->prepare("UPDATE vehicles SET status='maintenance' WHERE vehicle_id=? AND status='available'")
                    ->execute([$b['vehicle_id']]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error('Transaction failed: '.$e->getMessage(), 500);
        }
        success(['maint_id' => $id], 'Maintenance scheduled');
        break;

    case 'PUT':
        $b = jsonBody();
        if (empty($b['maint_id'])) error('maint_id required');

        $pdo->beginTransaction();
        try {
            // Resolve type if provided
            $extraFields = []; $extraParams = [];
            if (!empty($b['mtype_name'])) {
                $ts = $pdo->prepare('SELECT mtype_id FROM maintenance_types WHERE mtype_name = ?');
                $ts->execute([trim($b['mtype_name'])]);
                $mt = $ts->fetch();
                if (!$mt) {
                    $pdo->prepare('INSERT INTO maintenance_types (mtype_name) VALUES (?)')->execute([trim($b['mtype_name'])]);
                    $mtId = $pdo->lastInsertId();
                } else { $mtId = $mt['mtype_id']; }
                $extraFields[] = 'mtype_id = ?'; $extraParams[] = $mtId;
            }

            $fields = []; $params = [];
            foreach (['vehicle_id','description','scheduled','cost_est','status'] as $f)
                if (isset($b[$f])) { $fields[] = "$f = ?"; $params[] = $b[$f]; }

            // Auto-set progress when done
            if (isset($b['status']) && $b['status'] === 'done') {
                $fields[] = 'progress = ?'; $params[] = 100;
            } elseif (isset($b['progress'])) {
                $fields[] = 'progress = ?'; $params[] = (int)$b['progress'];
            }

            $allFields  = array_merge($fields, $extraFields);
            $allParams  = array_merge($params, $extraParams);

            if (!empty($allFields)) {
                $allParams[] = $b['maint_id'];
                $pdo->prepare('UPDATE maintenance_records SET '.implode(', ',$allFields).' WHERE maint_id = ?')->execute($allParams);
            }

            // Sync vehicle status when maintenance done
            if (isset($b['status']) && $b['status'] === 'done') {
                $vid = $pdo->prepare('SELECT vehicle_id FROM maintenance_records WHERE maint_id = ?');
                $vid->execute([$b['maint_id']]);
                $vehicleId = $vid->fetchColumn();
                $pdo->prepare("UPDATE vehicles SET status='available' WHERE vehicle_id=? AND status='maintenance'")
                    ->execute([$vehicleId]);
            }
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error('Transaction failed: '.$e->getMessage(), 500);
        }
        success([], 'Record updated');
        break;

    case 'DELETE':
        requireAdmin(); // only admins may delete
        if (empty($_GET['id'])) error('id required');
        $pdo->prepare('DELETE FROM maintenance_records WHERE maint_id = ?')->execute([$_GET['id']]);
        success([], 'Record deleted');
        break;

    default:
        error('Method not allowed', 405);
}
