<?php
// ── includes/auth.php ──────────────────────────────────────────
// Session-based authentication + role-based access control.
//   role 'admin' → full access (incl. delete)
//   role 'staff' → view/create/update only, no delete
// ──────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is anyone currently logged in? */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/** Get the logged-in user's basic info, or null. */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'user_id'   => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role'      => $_SESSION['role'],
    ];
}

/** Is the logged-in user an admin? */
function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/** Attempt to log a user in. Returns true on success. */
function attemptLogin(string $username, string $password, PDO $pdo): bool {
    $s = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $s->execute([$username]);
    $user = $s->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    return true;
}

/** Destroy the current session (logout). */
function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Guard for PAGE routes (index.php) — redirect to login if not authenticated. */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/** Guard for API routes — respond 401 JSON if not authenticated. */
function requireApiAuth(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated. Please log in.']);
        exit;
    }
}

/** Guard for API routes — respond 403 JSON if not an admin. */
function requireAdmin(): void {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Admin access required for this action.']);
        exit;
    }
}
