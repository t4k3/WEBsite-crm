<?php
// Il cliente accetta il preventivo dal suo link personale.
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'method'], 405);
if (!csrf_check($_POST['csrf'] ?? null)) json_out(['ok' => false, 'error' => 'csrf'], 403);

$token = post('token');
if (!preg_match('/^[a-f0-9]{32}$/', $token)) json_out(['ok' => false, 'error' => 'token'], 422);

try {
    $st = db()->prepare('SELECT id, status FROM deals WHERE token = ? AND quote_sent_at IS NOT NULL AND accepted_at IS NULL');
    $st->execute([$token]);
    $deal = $st->fetch();
    if (!$deal) json_out(['ok' => false, 'error' => 'not_found'], 404);

    db()->prepare("UPDATE deals SET accepted_at = NOW(), status = 'ordine_confermato' WHERE id = ?")->execute([$deal['id']]);
    db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
        ->execute([$deal['id'], $deal['status'], 'ordine_confermato', 'Offerta accettata dal cliente', 'cliente']);
} catch (Throwable $ex) {
    json_out(['ok' => false, 'error' => 'db'], 500);
}

$cfg = require __DIR__ . '/../inc/product.php';
send_mail($cfg['notify_to'], 'Preventivo ACCETTATO — Wazlley', "Il cliente ha accettato il preventivo.\nDeal #{$deal['id']}\n");

json_out(['ok' => true]);
