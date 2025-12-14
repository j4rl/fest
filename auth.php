<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db_prepare('SELECT id, username FROM users WHERE id = ?');
    db_execute($stmt, [$_SESSION['user_id']]);
    $user = db_fetch_one($stmt);
    return $user ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function attempt_login(string $username, string $password): bool
{
    $stmt = db_prepare('SELECT id, password_hash FROM users WHERE username = ?');
    db_execute($stmt, [$username]);
    $user = db_fetch_one($stmt);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        return true;
    }
    return false;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
