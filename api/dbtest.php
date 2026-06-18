<?php
// ⚠️ TEMPORANEO — verifica connessione DB. ELIMINARE dopo il test.
header('Content-Type: text/plain; charset=utf-8');
require __DIR__ . '/../lib/db.php';
try {
    $pdo = db();
    $ver = $pdo->query('SELECT VERSION()')->fetchColumn();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "OK — connessione al DB riuscita\n";
    echo "MySQL: $ver\n";
    echo 'Tabelle: ' . (count($tables) ? implode(', ', $tables) : '(nessuna ancora)') . "\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "ERRORE connessione: " . $e->getMessage() . "\n";
}
