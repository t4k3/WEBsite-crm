<?php
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/db.php';

$token = $_GET['token'] ?? '';
$deal = null;
if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $st = db()->prepare('SELECT * FROM deals WHERE token = ?');
    $st->execute([$token]);
    $deal = $st->fetch() ?: null;
}

$done = false;
$viesMsg = '';
$viesClass = '';

if ($deal && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $ctype = in_array(post('customer_type'), ['azienda', 'privato'], true) ? post('customer_type') : null;
    $shipSame = isset($_POST['ship_same']) ? 1 : 0;

    // Verifica VIES se è indicata una P.IVA
    $vatValid = null;
    $viesName = null;
    $checkedAt = null;
    if (post('vat_number') !== '') {
        $v = vies_check(post('vat_number'));
        if ($v['status'] === 'valid')   { $vatValid = 1; $viesName = $v['name'] ?? ''; $checkedAt = date('Y-m-d H:i:s');
            $viesMsg = 'Partita IVA verificata su VIES' . ($viesName ? ' — intestatario: ' . $viesName : '') . '.'; $viesClass = 'text-green-400'; }
        elseif ($v['status'] === 'invalid') { $vatValid = 0; $checkedAt = date('Y-m-d H:i:s');
            $viesMsg = 'Attenzione: la partita IVA non risulta valida in VIES. I dati sono stati salvati, la ricontrolleremo.'; $viesClass = 'text-amber-400'; }
        elseif ($v['status'] === 'error') {
            $viesMsg = 'Non è stato possibile verificare ora la P.IVA su VIES: la controlleremo noi.'; $viesClass = 'text-gray-500'; }
    }

    db()->prepare(
        'UPDATE deals SET customer_type=?, company_name=?, vat_number=?, tax_code=?, sdi_code=?, pec=?, eori=?,
            vat_valid=?, vat_vies_name=?, vat_checked_at=?,
            bill_address=?, bill_city=?, bill_zip=?, bill_province=?, bill_country=?,
            ship_same=?, ship_address=?, ship_city=?, ship_zip=?, ship_province=?, ship_country=?
         WHERE id=?'
    )->execute([
        $ctype, post('company_name'), post('vat_number'), post('tax_code'), post('sdi_code'), post('pec'), post('eori'),
        $vatValid, $viesName, $checkedAt,
        post('bill_address'), post('bill_city'), post('bill_zip'), post('bill_province'), post('bill_country'),
        $shipSame, post('ship_address'), post('ship_city'), post('ship_zip'), post('ship_province'), post('ship_country'),
        $deal['id'],
    ]);
    // Compilare i dati di fatturazione = confermare l'ordine (salvo trattativa 'persa').
    $newStatus = $deal['status'] === 'perso' ? 'perso' : 'ordine_confermato';
    db()->prepare('UPDATE deals SET accepted_at = COALESCE(accepted_at, NOW()), status = ? WHERE id = ?')
        ->execute([$newStatus, $deal['id']]);
    db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
        ->execute([$deal['id'], $deal['status'], $newStatus, 'Ordine confermato e dati di fatturazione inseriti dal cliente', 'cliente']);

    $cfg = require __DIR__ . '/inc/product.php';
    send_mail(
        $cfg['notify_to'],
        'Ordine confermato — dati fatturazione ricevuti — Wazlley',
        "Il cliente ha confermato l'ordine e inserito i dati di fatturazione.\n"
            . "Deal #{$deal['id']} — {$deal['contact_name']} ({$deal['email']})\n"
    );
    $done = true;

    // ricarico i dati aggiornati per il riepilogo
    $st = db()->prepare('SELECT * FROM deals WHERE id = ?');
    $st->execute([$deal['id']]);
    $deal = $st->fetch();
}

$csrf = csrf_token();
function f($d, $k) { return e($d[$k] ?? ''); }
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>I tuoi dati — Wazlley</title>
    <link rel="icon" href="/assets/img/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", "Helvetica Neue", Arial, sans-serif; background:#ffffff; color:#1d1d1f; }
        h1, h2, h3 { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Inter", sans-serif; font-weight: 600; letter-spacing:-0.022em; }
        .field { width:100%; padding:.6rem; border-radius:.5rem; color:#000; background:#fff; border:1px solid #d1d5db; }
        .lbl { display:block; font-size:.75rem; color:#44443f; margin-bottom:.2rem; }
    </style>
</head>
<body>
    <nav class="fixed top-0 w-full z-50 flex items-center px-6 py-4 bg-white/85 border-b border-gray-200 text-gray-800 backdrop-blur-md">
        <a href="index.html"><img src="/assets/img/logo.png" alt="Wazlley" class="h-10" /></a>
    </nav>
    <main class="pt-28 pb-20 px-4 max-w-2xl mx-auto">
    <?php if (!$deal): ?>
        <div class="bg-gray-50 border border-gray-200 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-2">Link non valido</h1>
            <p class="text-gray-500">Il link per l'inserimento dati non è corretto.</p>
        </div>
    <?php elseif ($done): ?>
        <div class="bg-gray-50 border border-gray-200 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-3">Ordine confermato ✅</h1>
            <?php if ($viesMsg): ?><p class="<?= $viesClass ?> mb-2"><?= e($viesMsg) ?></p><?php endif; ?>
            <p class="text-gray-600">Grazie, abbiamo ricevuto la tua conferma e i dati di fatturazione. Ti contatteremo a breve per finalizzare la spedizione.</p>
            <a href="<?= e('preventivo.php?token=' . $token) ?>" class="inline-block mt-6 text-[#0096e0] underline">Torna al preventivo</a>
        </div>
    <?php else: ?>
        <form method="POST" class="space-y-4 bg-gray-50 border border-gray-200 p-6 rounded-2xl">
            <h1 class="text-2xl font-bold mb-1">Completa l'ordine</h1>
            <p class="text-gray-500 text-sm mb-4">Ciao <?= e($deal['contact_name']) ?>, inserisci i dati di fatturazione per <strong>confermare l'ordine</strong>. La partita IVA (se UE) viene verificata automaticamente.</p>
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="customer_type" value="privato" <?= $deal['customer_type'] !== 'azienda' ? 'checked' : '' ?> /> Privato</label>
                <label class="flex items-center gap-2"><input type="radio" name="customer_type" value="azienda" <?= $deal['customer_type'] === 'azienda' ? 'checked' : '' ?> /> Azienda</label>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div><label class="lbl">Ragione sociale</label><input class="field" name="company_name" value="<?= f($deal,'company_name') ?>" /></div>
                <div><label class="lbl">P.IVA / VAT (con prefisso paese, es. IT…)</label><input class="field" name="vat_number" value="<?= f($deal,'vat_number') ?>" /></div>
                <div><label class="lbl">Codice fiscale</label><input class="field" name="tax_code" value="<?= f($deal,'tax_code') ?>" /></div>
                <div><label class="lbl">Codice SDI</label><input class="field" name="sdi_code" maxlength="7" value="<?= f($deal,'sdi_code') ?>" /></div>
                <div><label class="lbl">PEC</label><input class="field" name="pec" value="<?= f($deal,'pec') ?>" /></div>
                <div><label class="lbl">EORI (export)</label><input class="field" name="eori" value="<?= f($deal,'eori') ?>" /></div>
            </div>

            <h2 class="text-lg font-semibold pt-2">Indirizzo di fatturazione</h2>
            <input class="field" name="bill_address" placeholder="Indirizzo" value="<?= f($deal,'bill_address') ?>" />
            <div class="grid md:grid-cols-4 gap-3">
                <input class="field" name="bill_city" placeholder="Città" value="<?= f($deal,'bill_city') ?>" />
                <input class="field" name="bill_zip" placeholder="CAP" value="<?= f($deal,'bill_zip') ?>" />
                <input class="field" name="bill_province" placeholder="Prov./Stato" value="<?= f($deal,'bill_province') ?>" />
                <input class="field" name="bill_country" placeholder="Paese" value="<?= f($deal,'bill_country') ?>" />
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="ship_same" value="1" id="shipSame" <?= $deal['ship_same'] ? 'checked' : '' ?> /> Spedizione allo stesso indirizzo
            </label>
            <div id="shipFields" class="space-y-3 <?= $deal['ship_same'] ? 'hidden' : '' ?>">
                <h2 class="text-lg font-semibold">Indirizzo di spedizione</h2>
                <input class="field" name="ship_address" placeholder="Indirizzo" value="<?= f($deal,'ship_address') ?>" />
                <div class="grid md:grid-cols-4 gap-3">
                    <input class="field" name="ship_city" placeholder="Città" value="<?= f($deal,'ship_city') ?>" />
                    <input class="field" name="ship_zip" placeholder="CAP" value="<?= f($deal,'ship_zip') ?>" />
                    <input class="field" name="ship_province" placeholder="Prov./Stato" value="<?= f($deal,'ship_province') ?>" />
                    <input class="field" name="ship_country" placeholder="Paese" value="<?= f($deal,'ship_country') ?>" />
                </div>
            </div>

            <button type="submit" class="w-full bg-[#f59000] text-white px-6 py-3 rounded-full font-medium hover:bg-[#e08400] transition">Conferma l'ordine</button>
        </form>
        <script>
            document.getElementById('shipSame').addEventListener('change', function(){ document.getElementById('shipFields').classList.toggle('hidden', this.checked); });
        </script>
    <?php endif; ?>
    </main>
</body>
</html>
