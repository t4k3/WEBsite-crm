<?php
require __DIR__ . '/../inc/auth.php';
require_login();

$statuses = crm_statuses();
$cfg = require __DIR__ . '/../inc/product.php';
$id = (int) ($_GET['id'] ?? 0);
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $cur = db()->prepare('SELECT * FROM deals WHERE id = ?');
    $cur->execute([$id]);
    $deal = $cur->fetch();
    if ($deal) {
        $action = post('action');

        if ($action === 'check_vies') {
            $v = vies_check(post('vat_number'));
            $valid = $v['status'] === 'valid' ? 1 : ($v['status'] === 'invalid' ? 0 : null);
            $vname = $v['status'] === 'valid' ? ($v['name'] ?? '') : null;
            $chk = in_array($v['status'], ['valid', 'invalid'], true) ? date('Y-m-d H:i:s') : null;
            db()->prepare('UPDATE deals SET vat_number=?, vat_valid=?, vat_vies_name=?, vat_checked_at=? WHERE id=?')
                ->execute([post('vat_number'), $valid, $vname, $chk, $id]);
            header('Location: deal.php?id=' . $id . '&vies=' . $v['status']);
            exit;
        }

        $price = post('quoted_price') === '' ? null : (float) str_replace(',', '.', post('quoted_price'));
        $shipSame = isset($_POST['ship_same']) ? 1 : 0;
        $newStatus = isset($statuses[$_POST['status'] ?? '']) ? $_POST['status'] : $deal['status'];

        // send_quote forza lo stato e richiede il prezzo
        $sending = ($action === 'send_quote');
        if ($sending && $price === null) {
            header('Location: deal.php?id=' . $id . '&err=price');
            exit;
        }
        if ($sending) $newStatus = 'preventivo_inviato';

        $up = db()->prepare(
            'UPDATE deals SET status=?, quoted_price=?, admin_notes=?, tracking_number=?,
                customer_type=?, company_name=?, vat_number=?, tax_code=?, sdi_code=?, pec=?, eori=?,
                bill_address=?, bill_city=?, bill_zip=?, bill_province=?, bill_country=?,
                ship_same=?, ship_address=?, ship_city=?, ship_zip=?, ship_province=?, ship_country=?'
            . ($sending ? ', quote_sent_at = COALESCE(quote_sent_at, NOW())' : '')
            . ' WHERE id=?'
        );
        $up->execute([
            $newStatus, $price, post('admin_notes'), post('tracking_number'),
            (in_array(post('customer_type'), ['azienda', 'privato'], true) ? post('customer_type') : null),
            post('company_name'), post('vat_number'), post('tax_code'), post('sdi_code'), post('pec'), post('eori'),
            post('bill_address'), post('bill_city'), post('bill_zip'), post('bill_province'), post('bill_country'),
            $shipSame, post('ship_address'), post('ship_city'), post('ship_zip'), post('ship_province'), post('ship_country'),
            $id,
        ]);

        if ($newStatus !== $deal['status']) {
            $note = $sending ? 'Preventivo inviato' : post('history_note');
            db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$id, $deal['status'], $newStatus, $note ?: null, current_admin()]);
        }

        if ($sending) {
            $link = base_url() . '/preventivo.php?token=' . $deal['token'];
            $body = "Ciao {$deal['contact_name']},\n\nin allegato la nostra offerta per {$cfg['name']}"
                . " (q.tà {$deal['quantity']}, colore {$deal['variant']}).\n"
                . "Prezzo: " . number_format((float)$price, 2, ',', '.') . " {$deal['currency']}\n\n"
                . "Vedi e accetta il preventivo qui:\n$link\n\nGrazie,\nTakeoff.pro";
            @mail($deal['email'], 'Il tuo preventivo Wazlley', $body, "From: noreply@takeoff.pro\r\nReply-To: {$cfg['notify_to']}\r\n");
            header('Location: deal.php?id=' . $id . '&sent=1');
            exit;
        }
    }
    header('Location: deal.php?id=' . $id);
    exit;
}

$st = db()->prepare('SELECT * FROM deals WHERE id = ?');
$st->execute([$id]);
$d = $st->fetch();
if (!$d) { http_response_code(404); exit('Trattativa non trovata. <a href="index.php">Torna</a>'); }
$hist = db()->prepare('SELECT * FROM deal_history WHERE deal_id = ? ORDER BY changed_at DESC');
$hist->execute([$id]);
$history = $hist->fetchAll();
$csrf = csrf_token();
$link = base_url() . '/preventivo.php?token=' . $d['token'];

function row($label, $val) {
    if ($val === null || $val === '') return;
    echo '<div class="flex gap-2 py-1 border-b border-gray-800"><span class="w-44 text-gray-500">' . e($label) . '</span><span>' . e($val) . '</span></div>';
}
function inp($name, $label, $val, $w = '') {
    echo '<label class="block ' . $w . '"><span class="text-gray-400 text-xs">' . e($label) . '</span>'
        . '<input name="' . e($name) . '" value="' . e($val) . '" class="w-full p-2 rounded text-black mt-1 text-sm" /></label>';
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
<body class="p-6 max-w-5xl mx-auto">
    <a href="index.php" class="text-yellow-400 text-sm">← Tutte le trattative</a>
    <h1 class="text-xl font-bold mt-2 mb-4">#<?= (int)$d['id'] ?> · <?= e($d['contact_name']) ?>
        <span class="px-2 py-0.5 rounded bg-gray-700 text-xs align-middle"><?= e($statuses[$d['status']] ?? $d['status']) ?></span>
    </h1>

    <?php if (isset($_GET['sent'])): ?>
        <div class="bg-green-900/40 border border-green-700 text-green-300 text-sm p-3 rounded mb-4">Preventivo inviato al cliente (<?= e($d['email']) ?>).</div>
    <?php endif; ?>
    <?php if (isset($_GET['err']) && $_GET['err'] === 'price'): ?>
        <div class="bg-red-900/40 border border-red-700 text-red-300 text-sm p-3 rounded mb-4">Imposta prima il prezzo del preventivo.</div>
    <?php endif; ?>
    <?php if (isset($_GET['vies'])):
        $vmap = [
            'valid' => ['bg-green-900/40 border-green-700 text-green-300', 'Partita IVA VALIDA in VIES.'],
            'invalid' => ['bg-red-900/40 border-red-700 text-red-300', 'Partita IVA NON valida in VIES.'],
            'error' => ['bg-gray-800 border-gray-600 text-gray-300', 'Servizio VIES non raggiungibile ora, riprova.'],
            'skip' => ['bg-gray-800 border-gray-600 text-gray-300', 'P.IVA non UE o senza prefisso paese: VIES non applicabile.'],
        ];
        $vm = $vmap[$_GET['vies']] ?? null;
        if ($vm): ?>
        <div class="border <?= $vm[0] ?> text-sm p-3 rounded mb-4"><?= e($vm[1]) ?></div>
    <?php endif; endif; ?>

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
                row('Vuole una call', $d['want_call'] ? 'Sì' : '');
                row('Disponibilità', $d['availability']);
                row('Note cliente', $d['notes']);
                row('Preventivo inviato il', $d['quote_sent_at']);
                row('Accettato il', $d['accepted_at']);
                ?>
            </div>
            <div class="mt-4 text-xs text-gray-500">
                Link preventivo cliente:<br>
                <a href="<?= e($link) ?>" target="_blank" class="text-yellow-400 break-all"><?= e($link) ?></a>
            </div>
        </section>

        <section>
            <form method="POST" class="space-y-4 text-sm bg-gray-900 p-4 rounded-xl">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

                <h2 class="font-semibold text-gray-300">Gestione</h2>
                <label class="block"><span class="text-gray-400 text-xs">Stato</span>
                    <select name="status" class="w-full p-2 rounded text-black mt-1">
                        <?php foreach ($statuses as $k => $label): ?>
                            <option value="<?= e($k) ?>" <?= $d['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <?php inp('history_note', 'Nota cambio stato (opzionale)', ''); ?>
                <?php inp('quoted_price', 'Prezzo preventivo (' . $d['currency'] . ')', $d['quoted_price']); ?>
                <?php inp('tracking_number', 'Tracking spedizione', $d['tracking_number']); ?>
                <label class="block"><span class="text-gray-400 text-xs">Note interne</span>
                    <textarea name="admin_notes" rows="3" class="w-full p-2 rounded text-black mt-1 text-sm"><?= e($d['admin_notes']) ?></textarea>
                </label>

                <h2 class="font-semibold text-gray-300 pt-2">Dati di fatturazione</h2>
                <?php if ($d['vat_valid'] !== null): ?>
                    <p class="text-xs <?= $d['vat_valid'] ? 'text-green-400' : 'text-red-400' ?>">
                        VIES: <?= $d['vat_valid'] ? 'P.IVA valida' : 'P.IVA non valida' ?>
                        <?= $d['vat_vies_name'] ? '— ' . e($d['vat_vies_name']) : '' ?>
                        <?= $d['vat_checked_at'] ? '<span class="text-gray-500">(' . e(substr($d['vat_checked_at'], 0, 16)) . ')</span>' : '' ?>
                    </p>
                <?php endif; ?>
                <label class="block"><span class="text-gray-400 text-xs">Tipo cliente</span>
                    <select name="customer_type" class="w-full p-2 rounded text-black mt-1">
                        <option value="">—</option>
                        <option value="privato" <?= $d['customer_type'] === 'privato' ? 'selected' : '' ?>>Privato</option>
                        <option value="azienda" <?= $d['customer_type'] === 'azienda' ? 'selected' : '' ?>>Azienda</option>
                    </select>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    inp('company_name', 'Ragione sociale', $d['company_name']);
                    inp('vat_number', 'P.IVA / VAT', $d['vat_number']);
                    inp('tax_code', 'Codice fiscale', $d['tax_code']);
                    inp('sdi_code', 'SDI', $d['sdi_code']);
                    inp('pec', 'PEC', $d['pec']);
                    inp('eori', 'EORI', $d['eori']);
                    ?>
                </div>
                <?php inp('bill_address', 'Indirizzo fatturazione', $d['bill_address']); ?>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    inp('bill_city', 'Città', $d['bill_city']);
                    inp('bill_zip', 'CAP', $d['bill_zip']);
                    inp('bill_province', 'Provincia/Stato', $d['bill_province']);
                    inp('bill_country', 'Paese', $d['bill_country']);
                    ?>
                </div>
                <label class="flex items-center gap-2 text-xs text-gray-400">
                    <input type="checkbox" name="ship_same" value="1" <?= $d['ship_same'] ? 'checked' : '' ?> /> Spedizione = fatturazione
                </label>
                <?php inp('ship_address', 'Indirizzo spedizione (se diverso)', $d['ship_address']); ?>
                <div class="grid grid-cols-2 gap-3">
                    <?php
                    inp('ship_city', 'Città', $d['ship_city']);
                    inp('ship_zip', 'CAP', $d['ship_zip']);
                    inp('ship_province', 'Provincia/Stato', $d['ship_province']);
                    inp('ship_country', 'Paese', $d['ship_country']);
                    ?>
                </div>

                <button name="action" value="check_vies" class="w-full bg-gray-700 text-white py-2 rounded text-xs hover:bg-gray-600">Verifica P.IVA su VIES</button>
                <div class="flex gap-3 pt-1">
                    <button name="action" value="save" class="flex-1 bg-gray-200 text-black py-2 rounded font-semibold hover:bg-white">Salva</button>
                    <button name="action" value="send_quote" class="flex-1 bg-yellow-400 text-black py-2 rounded font-semibold hover:bg-yellow-300">Salva e invia preventivo</button>
                </div>
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
