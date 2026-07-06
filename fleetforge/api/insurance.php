<?php
// ── api/insurance.php ─────────────────────────────────────────
// GET    /api/insurance.php           → list all policies
// POST   /api/insurance.php           → add policy
// PUT    /api/insurance.php           → update policy
// DELETE /api/insurance.php?id=I001  → delete policy
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

const INS_SELECT = '
    SELECT p.*,
           v.name  AS vehicle_name, v.plate, v.icon,
           c.name  AS customer_name,
           ip.plan_name, ip.daily_rate
    FROM insurance_policies p
    JOIN vehicles        v  ON p.vehicle_id  = v.vehicle_id
    JOIN customers       c  ON p.customer_id = c.customer_id
    JOIN insurance_plans ip ON p.plan_id     = ip.plan_id
';

switch ($method) {

    case 'GET':
        if (!empty($_GET['id'])) {
            $s = $pdo->prepare(INS_SELECT.' WHERE p.policy_id = ?');
            $s->execute([$_GET['id']]);
            $row = $s->fetch();
            $row ? success($row) : error('Policy not found', 404);
        }

        $status = $_GET['status'] ?? 'all';
        $search = '%'.($_GET['search'] ?? '').'%';

        // Also auto-expire policies past expiry_date
        $pdo->prepare("UPDATE insurance_policies SET status='expired' WHERE expiry_date < CURDATE() AND status='active'")->execute();

        $sql    = INS_SELECT.'WHERE (v.name LIKE ? OR c.name LIKE ? OR ip.plan_name LIKE ?)';
        $params = [$search, $search, $search];
        if ($status !== 'all') { $sql .= ' AND p.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY p.policy_id DESC';

        $s = $pdo->prepare($sql);
        $s->execute($params);
        success($s->fetchAll());
        break;

    case 'POST':
        $b = jsonBody();
        foreach (['vehicle_id','customer_id','plan_id','start_date','expiry_date'] as $f)
            if (empty($b[$f])) error("Field '$f' is required");

        if (strtotime($b['expiry_date']) < strtotime($b['start_date']))
            error('Expiry must be after start date');

        $status = (strtotime($b['expiry_date']) < time()) ? 'expired' : 'active';
        $id = nextId('I', 'insurance_policies', 'policy_id', $pdo);

        $pdo->prepare(
            'INSERT INTO insurance_policies (policy_id,vehicle_id,customer_id,plan_id,start_date,expiry_date,status)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([$id, $b['vehicle_id'], $b['customer_id'], $b['plan_id'],
                    $b['start_date'], $b['expiry_date'], $status]);
        success(['policy_id' => $id], 'Policy added');
        break;

    case 'PUT':
        $b = jsonBody();
        if (empty($b['policy_id'])) error('policy_id required');
        $fields = []; $params = [];
        foreach (['vehicle_id','customer_id','plan_id','start_date','expiry_date','status'] as $f)
            if (isset($b[$f])) { $fields[] = "$f = ?"; $params[] = $b[$f]; }
        if (empty($fields)) error('Nothing to update');
        $params[] = $b['policy_id'];
        $pdo->prepare('UPDATE insurance_policies SET '.implode(', ',$fields).' WHERE policy_id = ?')->execute($params);
        success([], 'Policy updated');
        break;

    case 'DELETE':
        requireAdmin(); // only admins may delete
        if (empty($_GET['id'])) error('id required');
        $pdo->prepare('DELETE FROM insurance_policies WHERE policy_id = ?')->execute([$_GET['id']]);
        success([], 'Policy deleted');
        break;

    default:
        error('Method not allowed', 405);
}
