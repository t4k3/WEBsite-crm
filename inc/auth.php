<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

function admin_count(): int {
    return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

function admin_create(string $user, string $pass): void {
    $st = db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $st->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
}

function admin_login(string $user, string $pass): bool {
    h_session();
    $st = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
    $st->execute([$user]);
    $row = $st->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int) $row['id'];
        $_SESSION['admin_user'] = $row['username'];
        return true;
    }
    return false;
}

function current_admin(): ?string {
    h_session();
    return $_SESSION['admin_user'] ?? null;
}

function require_login(): void {
    if (current_admin() === null) {
        header('Location: login.php');
        exit;
    }
}

function admin_logout(): void {
    h_session();
    $_SESSION = [];
    session_destroy();
}
