<?php
// ── api/customers.php ──────────────────────────────────────────
// GET    /api/customers.php           → list all customers (+rental stats)
// GET    /api/customers.php?id=C001   → single customer
// POST   /api/customers.php           → create customer
// PUT    /api/customers.php           → update customer
// DELETE /api/customers.php?id=C001  → delete customer
// ──────────────────────────────────────────────────────────────
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/helpers.php';
require_once __DIR__.'/../includes/auth.php';

requireApiAuth(); // must be logged in to use this API

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (!empty($_GET['id'])) {
            $s = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
            $s->execute([$_GET['id']]);
            $row = $s->fetch();
            $row ? success($row) : error('Customer not found', 404);
        }

        $search = '%'.($_GET['search'] ?? '').'%';
        // Include rental count and total spent
        $s = $pdo->prepare(
            'SELECT c.*,
                    COUNT(r.rental_id)   AS rental_count,
                    COALESCE(SUM(r.amount),0) AS total_spent
             FROM customers c
             LEFT JOIN rentals r ON c.customer_id = r.customer_id
             WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.license_no LIKE ?
             GROUP BY c.customer_id
             ORDER BY c.customer_id'
        );
        $s->execute([$search, $search, $search, $search]);
        success($s->fetchAll());
        break;

    case 'POST':
        $b = jsonBody();
        foreach (['name','email','phone','license_no'] as $f)
            if (empty($b[$f])) error("Field '$f' is required");

        $id = nextId('C', 'customers', 'customer_id', $pdo);
        $pdo->prepare(
            'INSERT INTO customers (customer_id,name,email,phone,license_no,color_index)
             VALUES (?,?,?,?,?,?)'
        )->execute([
            $id,
            trim($b['name']),
            strtolower(trim($b['email'])),
            trim($b['phone']),
            strtoupper(trim($b['license_no'])),
            (int)($b['color_index'] ?? 0),
        ]);
        success(['customer_id' => $id], 'Customer added');
        break;

    case 'PUT':
        $b = jsonBody();
        if (empty($b['customer_id'])) error('customer_id required');
        $fields = []; $params = [];
        foreach (['name','email','phone','license_no','color_index'] as $f)
            if (isset($b[$f])) { $fields[] = "$f = ?"; $params[] = $b[$f]; }
        if (empty($fields)) error('Nothing to update');
        $params[] = $b['customer_id'];
        $pdo->prepare('UPDATE customers SET '.implode(', ',$fields).' WHERE customer_id = ?')->execute($params);
        success([], 'Customer updated');
        break;

    case 'DELETE':
        requireAdmin(); // only admins may delete
        if (empty($_GET['id'])) error('id required');
        $chk = $pdo->prepare("SELECT rental_id FROM rentals WHERE customer_id = ? AND status = 'active' LIMIT 1");
        $chk->execute([$_GET['id']]);
        if ($chk->fetch()) error('Customer has an active rental — cannot delete');
        $pdo->prepare('DELETE FROM customers WHERE customer_id = ?')->execute([$_GET['id']]);
        success([], 'Customer deleted');
        break;

    default:
        error('Method not allowed', 405);
}
