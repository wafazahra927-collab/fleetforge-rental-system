<?php
// ── includes/helpers.php ───────────────────────────────────────
// JSON response helpers + CORS headers for WAMP localhost dev
// ──────────────────────────────────────────────────────────────

// Allow the front-end (same origin) to call these APIs
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

/** Send a successful JSON response and exit. */
function success(mixed $data = [], string $message = 'OK'): void {
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

/** Send an error JSON response and exit. */
function error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

/** Return the decoded JSON body of the current request. */
function jsonBody(): array {
    $raw = file_get_contents('php://input');
    return $raw ? (json_decode($raw, true) ?? []) : [];
}

/** Generate the next sequential ID like V007, C005, etc. */
function nextId(string $prefix, string $table, string $idCol, PDO $pdo): string {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING($idCol,".( strlen($prefix)+1 ).") AS UNSIGNED)) AS mx FROM $table");
    $row  = $stmt->fetch();
    $next = (int)($row['mx'] ?? 0) + 1;
    return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
}

/** Sanitise / whitelist sort column names. */
function safeCol(string $col, array $allowed, string $default): string {
    return in_array($col, $allowed, true) ? $col : $default;
}
