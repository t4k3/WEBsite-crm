<?php
require __DIR__ . '/../inc/auth.php';
require_login();

$statuses = crm_statuses();
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $cur = db()->prepare('SELECT status FROM deals WHERE id = ?');
    $cur->execute([$id]);
    $old = $cur->fetchColumn();
    if ($old !== false) {
        $new = isset($statuses[$_POST['status'] ?? '']) ? $_POST['status'] : $old;
        $price = ($_POST['quoted_price'] ?? '') === '' ? null : (float) str_replace(',', '.', $_POST['quoted_price']);
        $up = db()->prepare('UPDATE deals SET status = ?, quoted_price = ?, admin_notes = ?, tracking_number = ? WHERE id = ?');
        $up->execute([$new, $price, post('admin_notes'), post('tracking_number'), $id]);
        if ($new !== $old) {
            $h = db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)');
            $h->execute([$id, $old, $new, post('history_note') ?: null, current_admin()]);
        }
    }
    header('Location: deal.php?id=' . $id);
    exit;
}

$st = db()->prepare('SELECT * FROM deals WHERE id = ?');
$st->execute([$id]);
$d = $st->fetch();
if (!$d) {
    http_response_code(404);
    exit('Trattativa non trovata. <a href="index.php">Torna</a>');
}
$hist = db()->prepare('SELECT * FROM deal_history WHERE deal_id = ? ORDER BY changed_at DESC');
$hist->execute([$id]);
$history = $hist->fetchAll();
$csrf = csrf_token();

function row($label, $val) {
    if ($val === null || $val === '') return;
    echo '<div class="flex gap-2 py-1 border-b border-gray-800"><span class="w-44 text-gray-500">' . e($label) . '</span><span>' . e($val) . '</span></div>';
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Trattativa #<?= (int)$d['id'] ?> — Wazlley CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0b0f19;color:#e5e7eb;font-family:system-ui,Arial,sans-serif}</style>
</head>
<body class="p-6 max-w-4xl mx-auto">
    <a href="index.php" class="text-yellow-400 text-sm">← Tutte le trattative</a>
    <h1 class="text-xl font-bold mt-2 mb-6">#<?= (int)$d['id'] ?> · <?= e($d['contact_name']) ?>
        <span class="px-2 py-0.5 rounded bg-gray-700 text-xs align-middle"><?= e($statuses[$d['status']] ?? $d['status']) ?></span>
    </h1>

    <div class="grid md:grid-cols-2 gap-8">
        <section>
            <h2 class="font-semibold mb-2 text-gray-300">Richiesta</h2>
            <div class="text-sm">
                <?php
                row('Creato il', $d['created_at']);
                row('Email', $d['email']);
                row('Telefono', $d['phone']);
                row('Paese', $d['country']);
                row('Quantità', $d['quantity']);
                row('Colore', $d['variant']);
                row('Note cliente', $d['notes']);
                ?>
            </div>
            <h2 class="font-semibold mt-6 mb-2 text-gray-300">Fatturazione</h2>
            <div class="text-sm">
                <?php
                row('Tipo', $d['customer_type']);
                row('Ragione sociale', $d['company_name']);
                row('P.IVA / VAT', $d['vat_number']);
                row('Codice fiscale', $d['tax_code']);
                row('SDI', $d['sdi_code']);
                row('PEC', $d['pec']);
                row('EORI', $d['eori']);
                row('Indirizzo', trim($d['bill_address'] . ' ' . $d['bill_zip'] . ' ' . $d['bill_city'] . ' ' . $d['bill_province'] . ' ' . $d['bill_country']));
                if (!$d['ship_same']) {
                    row('Spedizione', trim($d['ship_address'] . ' ' . $d['ship_zip'] . ' ' . $d['ship_city'] . ' ' . $d['ship_province'] . ' ' . $d['ship_country']));
                }
                ?>
            </div>
        </section>

        <section>
            <h2 class="font-semibold mb-2 text-gray-300">Gestione</h2>
            <form method="POST" class="space-y-3 text-sm bg-gray-900 p-4 rounded-xl">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <label class="block">
                    <span class="text-gray-400">Stato</span>
                    <select name="status" class="w-full p-2 rounded text-black mt-1">
                        <?php foreach ($statuses as $k => $label): ?>
                            <option value="<?= e($k) ?>" <?= $d['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="block">
                    <span class="text-gray-400">Nota cambio stato (opzionale)</span>
                    <input name="history_note" class="w-full p-2 rounded text-black mt-1" />
                </label>
                <label class="block">
                    <span class="text-gray-400">Prezzo preventivo (<?= e($d['currency']) ?>)</span>
                    <input name="quoted_price" value="<?= e($d['quoted_price']) ?>" class="w-full p-2 rounded text-black mt-1" />
                </label>
                <label class="block">
                    <span class="text-gray-400">Tracking spedizione</span>
                    <input name="tracking_number" value="<?= e($d['tracking_number']) ?>" class="w-full p-2 rounded text-black mt-1" />
                </label>
                <label class="block">
                    <span class="text-gray-400">Note interne</span>
                    <textarea name="admin_notes" rows="4" class="w-full p-2 rounded text-black mt-1"><?= e($d['admin_notes']) ?></textarea>
                </label>
                <button class="w-full bg-yellow-400 text-black py-2 rounded font-semibold hover:bg-yellow-300">Salva</button>
            </form>

            <h2 class="font-semibold mt-6 mb-2 text-gray-300">Storico</h2>
            <div class="text-xs text-gray-400 space-y-1">
                <?php foreach ($history as $h): ?>
                    <div>• <?= e(substr($h['changed_at'], 0, 16)) ?> — <?= e($statuses[$h['old_status']] ?? $h['old_status']) ?> → <?= e($statuses[$h['new_status']] ?? $h['new_status']) ?><?= $h['note'] ? ' (' . e($h['note']) . ')' : '' ?> <span class="text-gray-600"><?= e($h['changed_by']) ?></span></div>
                <?php endforeach; ?>
                <?php if (!$history): ?><div class="text-gray-600">Nessun cambio di stato.</div><?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
