<?php
// Connessione PDO condivisa.
function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    // In locale usa config/db.local.php se presente (non caricato sul server),
    // altrimenti le credenziali di produzione.
    $local = __DIR__ . '/../config/db.local.php';
    $c = is_file($local) ? require $local : require __DIR__ . '/../config/db.php';
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset={$c['charset']}";
    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}
