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

        // Nota evento: aggiunge una riga allo storico (senza cambiare stato)
        if ($action === 'add_note') {
            $note = post('event_note');
            if ($note !== '') {
                db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
                    ->execute([$id, $deal['status'], $deal['status'], $note, current_admin()]);
            }
            header('Location: deal.php?id=' . $id . '#storico');
            exit;
        }

        // Cambio stato rapido da pulsante (non tocca gli altri campi)
        $setTo = $_POST['set_to'] ?? '';
        if (isset($statuses[$setTo])) {
            if ($setTo !== $deal['status']) {
                db()->prepare('UPDATE deals SET status = ? WHERE id = ?')->execute([$setTo, $id]);
                db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
                    ->execute([$id, $deal['status'], $setTo, null, current_admin()]);
            }
            header('Location: deal.php?id=' . $id);
            exit;
        }

        // Pagamento (indipendente dalla spedizione)
        if (isset($_POST['set_paid'])) {
            $p = ($_POST['set_paid'] === '1') ? 1 : 0;
            db()->prepare('UPDATE deals SET paid = ?, paid_at = ? WHERE id = ?')
                ->execute([$p, $p ? date('Y-m-d H:i:s') : null, $id]);
            db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$id, $deal['status'], $deal['status'], $p ? 'Pagamento ricevuto' : 'Pagamento annullato', current_admin()]);
            header('Location: deal.php?id=' . $id);
            exit;
        }

        // Spedizione (indipendente dal pagamento)
        $ship = $_POST['set_ship'] ?? '';
        if (array_key_exists($ship, shipment_statuses())) {
            $shippedAt = $deal['shipped_at'];
            if ($ship === 'spedito' && !$shippedAt) $shippedAt = date('Y-m-d H:i:s');
            if ($ship === 'non_spedito') $shippedAt = null;
            db()->prepare('UPDATE deals SET shipment = ?, shipped_at = ? WHERE id = ?')
                ->execute([$ship, $shippedAt, $id]);
            db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
                ->execute([$id, $deal['status'], $deal['status'], 'Spedizione: ' . shipment_statuses()[$ship], current_admin()]);
            header('Location: deal.php?id=' . $id);
            exit;
        }

        $price = post('quoted_price') === '' ? null : (float) str_replace(',', '.', post('quoted_price'));
        $shipSame = isset($_POST['ship_same']) ? 1 : 0;
        // Lo stato si cambia coi pulsanti rapidi (set_to); qui resta invariato salvo invio preventivo.
        $newStatus = $deal['status'];

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
                . "Vedi il prezzo e completa l'ordine (inserendo i dati di fatturazione) qui:\n$link\n\nGrazie,\nTakeoff.pro";
            send_mail($deal['email'], 'Il tuo preventivo Wazlley', $body, 'info@takeoff.pro');
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

// Fasi lineari (per la barra di avanzamento) e prossime azioni suggerite
$flow = ['nuovo', 'preventivo_inviato', 'in_trattativa', 'ordine_confermato'];
$curIdx = array_search($d['status'], $flow, true); // false se 'perso'
$shipStatuses = shipment_statuses();

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
<body class="p-6 max-w-6xl mx-auto">
    <a href="index.php" class="text-yellow-400 text-sm">← Tutte le trattative</a>
    <h1 class="text-xl font-bold mt-2 mb-4">#<?= (int)$d['id'] ?> · <?= e($d['contact_name']) ?>
        <span class="px-2 py-0.5 rounded bg-gray-700 text-xs align-middle"><?= e($statuses[$d['status']] ?? $d['status']) ?></span>
        <?php if ($d['paid']): ?><span class="px-2 py-0.5 rounded bg-green-700 text-xs align-middle">Pagato</span><?php endif; ?>
        <?php if ($d['shipment'] !== 'non_spedito'): ?><span class="px-2 py-0.5 rounded bg-blue-700 text-xs align-middle"><?= e($shipStatuses[$d['shipment']]) ?></span><?php endif; ?>
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

    <!-- Tre dimensioni indipendenti: trattativa · pagamento · spedizione -->
    <div class="grid md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-900 p-4 rounded-xl">
            <h2 class="text-xs uppercase tracking-wide text-gray-500 mb-2">Trattativa</h2>
            <form method="POST" class="flex flex-wrap gap-1 text-xs">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <?php foreach ($flow as $i => $s):
                    $done = ($curIdx !== false && $i < $curIdx);
                    $isCur = ($d['status'] === $s);
                    $cls = $isCur ? 'bg-yellow-400 text-black font-semibold' : ($done ? 'bg-green-700 text-white hover:bg-green-600' : 'bg-gray-800 text-gray-400 hover:bg-gray-700');
                ?>
                    <button name="set_to" value="<?= e($s) ?>" class="px-2 py-1 rounded <?= $cls ?>"><?= $done ? '✓ ' : '' ?><?= e($statuses[$s]) ?></button>
                <?php endforeach; ?>
                <?php if ($d['status'] === 'perso'): ?>
                    <button name="set_to" value="in_trattativa" class="px-2 py-1 rounded bg-gray-700 text-white hover:bg-gray-600">Riapri</button>
                <?php else: ?>
                    <button name="set_to" value="perso" class="px-2 py-1 rounded bg-gray-800 text-red-300 hover:bg-gray-700">Persa</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-gray-900 p-4 rounded-xl">
            <h2 class="text-xs uppercase tracking-wide text-gray-500 mb-2">Pagamento</h2>
            <form method="POST" class="flex items-center gap-2">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <?php if ($d['paid']): ?>
                    <span class="px-2 py-1 rounded bg-green-700 text-white text-xs">✓ Pagato<?= $d['paid_at'] ? ' · ' . e(substr($d['paid_at'], 0, 10)) : '' ?></span>
                    <button name="set_paid" value="0" class="px-2 py-1 rounded bg-gray-800 text-gray-300 text-xs hover:bg-gray-700">Annulla</button>
                <?php else: ?>
                    <span class="text-gray-400 text-xs">Non pagato</span>
                    <button name="set_paid" value="1" class="px-3 py-1 rounded bg-yellow-400 text-black font-semibold text-xs hover:bg-yellow-300">Segna pagato</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-gray-900 p-4 rounded-xl">
            <h2 class="text-xs uppercase tracking-wide text-gray-500 mb-2">Spedizione</h2>
            <form method="POST" class="flex flex-wrap gap-1 text-xs">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                <?php foreach ($shipStatuses as $sk => $sl):
                    $cls = $d['shipment'] === $sk ? 'bg-yellow-400 text-black font-semibold' : 'bg-gray-800 text-gray-400 hover:bg-gray-700';
                ?>
                    <button name="set_ship" value="<?= e($sk) ?>" class="px-2 py-1 rounded <?= $cls ?>"><?= e($sl) ?></button>
                <?php endforeach; ?>
            </form>
            <?php if ($d['tracking_number']): ?><p class="text-xs text-gray-500 mt-2">Tracking: <?= e($d['tracking_number']) ?></p><?php endif; ?>
        </div>
    </div>

    <?php $hasBilling = ($d['customer_type'] || $d['company_name'] || $d['vat_number'] || $d['tax_code'] || $d['bill_address']); ?>
    <div class="grid md:grid-cols-2 gap-8">

        <!-- ===== SINISTRA: richiesta · nota evento · storico ===== -->
        <section class="space-y-6">
            <div>
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
                <div class="mt-3 text-xs text-gray-500">
                    Link preventivo cliente:<br>
                    <a href="<?= e($link) ?>" target="_blank" class="text-yellow-400 break-all"><?= e($link) ?></a>
                </div>
            </div>

            <div id="storico" class="bg-gray-900 p-4 rounded-xl">
                <h2 class="font-semibold mb-3 text-gray-300">Storico</h2>

                <!-- Nota evento → aggiunge una riga allo storico -->
                <form method="POST" class="mb-4 flex gap-2 items-end">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
                    <label class="block flex-1"><span class="text-gray-400 text-xs">Nota evento (telefonata, accordo, promemoria…)</span>
                        <input name="event_note" placeholder="Es. Chiamato, richiamare lunedì" class="w-full p-2 rounded text-black mt-1 text-sm" />
                    </label>
                    <button name="action" value="add_note" class="bg-yellow-400 text-black px-3 py-2 rounded text-sm font-semibold hover:bg-yellow-300 whitespace-nowrap">+ Aggiungi</button>
                </form>

                <div class="text-xs text-gray-400 space-y-1 border-t border-gray-800 pt-3">
                    <?php foreach ($history as $h): ?>
                        <div>• <?= e(substr($h['changed_at'], 0, 16)) ?>
                            <?php if ($h['old_status'] !== $h['new_status']): ?>
                                — <?= e($statuses[$h['old_status']] ?? $h['old_status']) ?> → <?= e($statuses[$h['new_status']] ?? $h['new_status']) ?>
                            <?php endif; ?>
                            <?= $h['note'] ? '<span class="text-gray-200">' . e($h['note']) . '</span>' : '' ?>
                            <span class="text-gray-600"><?= e($h['changed_by']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (!$history): ?><div class="text-gray-600">Ancora nessun evento.</div><?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ===== DESTRA: form preventivo + dati cliente collassabili ===== -->
        <section>
            <form method="POST" class="space-y-4 text-sm">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

                <div class="bg-gray-900 p-4 rounded-xl space-y-4">
                    <h2 class="font-semibold text-gray-300">Preventivo &amp; note interne</h2>
                    <?php if ($d['status'] === 'nuovo'): ?>
                        <p class="text-xs text-gray-400">Imposta il prezzo e premi «Salva e invia preventivo» per mandare l'offerta al cliente.</p>
                    <?php endif; ?>
                    <?php inp('quoted_price', 'Prezzo preventivo (' . $d['currency'] . ')', $d['quoted_price']); ?>
                    <?php inp('tracking_number', 'Tracking spedizione', $d['tracking_number']); ?>
                    <label class="block"><span class="text-gray-400 text-xs">Note interne</span>
                        <textarea name="admin_notes" rows="3" class="w-full p-2 rounded text-black mt-1 text-sm"><?= e($d['admin_notes']) ?></textarea>
                    </label>
                </div>

                <details class="bg-gray-900 rounded-xl" <?= $hasBilling ? 'open' : '' ?>>
                    <summary class="cursor-pointer select-none font-semibold text-gray-300 px-4 py-3">Dati di fatturazione</summary>
                    <div class="px-4 pb-4 space-y-3">
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
                        <button name="action" value="check_vies" class="w-full bg-gray-700 text-white py-2 rounded text-xs hover:bg-gray-600">Verifica P.IVA su VIES</button>
                    </div>
                </details>

                <details class="bg-gray-900 rounded-xl" <?= (!$d['ship_same'] && $d['ship_address']) ? 'open' : '' ?>>
                    <summary class="cursor-pointer select-none font-semibold text-gray-300 px-4 py-3">Indirizzo di spedizione</summary>
                    <div class="px-4 pb-4 space-y-3">
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
                    </div>
                </details>

                <div class="flex gap-3">
                    <button name="action" value="save" class="flex-1 bg-gray-200 text-black py-2 rounded font-semibold hover:bg-white">Salva dati</button>
                    <button name="action" value="send_quote" class="flex-1 bg-yellow-400 text-black py-2 rounded font-semibold hover:bg-yellow-300">Salva e invia preventivo</button>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
