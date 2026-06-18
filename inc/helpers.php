<?php
// Helper condivisi: sessione, CSRF, validazione, output.

function h_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (($_SERVER['HTTPS'] ?? '') !== ''),
        ]);
        session_start();
    }
}

function csrf_token(): string {
    h_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $t): bool {
    h_session();
    return !empty($_SESSION['csrf']) && is_string($t) && hash_equals($_SESSION['csrf'], $t);
}

function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function post(string $k, string $def = ''): string {
    return isset($_POST[$k]) ? trim((string) $_POST[$k]) : $def;
}

function valid_email(string $e): bool {
    return (bool) filter_var($e, FILTER_VALIDATE_EMAIL);
}

function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function e(?string $s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function gen_token(): string {
    return bin2hex(random_bytes(16)); // 32 caratteri hex
}

function base_url(): string {
    $scheme = (($_SERVER['HTTPS'] ?? '') !== '' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

// Pipeline CRM (stato => etichetta leggibile), in ordine.
function crm_statuses(): array {
    return [
        'nuovo'            => 'Nuova richiesta',
        'ordine_ricevuto'  => 'Ordine ricevuto',
        'preventivo_inviato' => 'Preventivo inviato',
        'in_trattativa'    => 'In trattativa',
        'ordine_confermato' => 'Ordine confermato',
        'pagato'           => 'Pagato',
        'spedito'          => 'Spedito',
        'consegnato'       => 'Consegnato',
        'perso'            => 'Perso',
    ];
}
