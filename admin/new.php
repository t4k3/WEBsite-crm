<?php
require __DIR__ . '/../inc/auth.php';
require_login();
$cfg = require __DIR__ . '/../inc/product.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $name  = post('contact_name');
    $email = post('email');
    $qty   = (int) post('quantity', '1');
    if ($qty < 1) $qty = 1;

    if ($name === '') {
        $err = 'Il nome è obbligatorio.';
    } elseif ($email !== '' && !valid_email($email)) {
        $err = 'Email non valida (lasciala vuota se non ce l\'hai).';
    } else {
        $token   = gen_token();
        $variant = in_array(post('variant'), $cfg['variants'], true) ? post('variant') : ($cfg['variants'][0] ?? null);
        $src     = post('source');

        $st = db()->prepare(
            'INSERT INTO deals (token, contact_name, email, phone, country, quantity, variant, notes, currency, consent, admin_notes)
             VALUES (?,?,?,?,?,?,?,?,?,1,?)'
        );
        $st->execute([
            $token, $name, $email, post('phone'), post('country'), $qty, $variant, post('notes'),
            $cfg['currency'], $src ? "Fonte: $src" : null,
        ]);
        $id = (int) db()->lastInsertId();

        db()->prepare('INSERT INTO deal_history (deal_id, old_status, new_status, note, changed_by) VALUES (?,?,?,?,?)')
            ->execute([$id, null, 'nuovo', 'Trattativa creata manualmente' . ($src ? " — fonte: $src" : ''), current_admin()]);

        header('Location: deal.php?id=' . $id);
        exit;
    }
}
$csrf = csrf_token();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nuova trattativa — Wazlley CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0b0f19;color:#e5e7eb;font-family:system-ui,Arial,sans-serif}</style>
</head>
<body class="p-6 max-w-xl mx-auto">
    <a href="index.php" class="text-yellow-400 text-sm">← Tutte le trattative</a>
    <h1 class="text-xl font-bold mt-2 mb-4">Nuova trattativa
        <span class="text-gray-500 text-sm font-normal"><?= e(APP_VERSION) ?></span>
    </h1>
    <p class="text-sm text-gray-400 mb-4">Aggiungi manualmente un contatto (telefonata, fiera, passaparola…). Solo il nome è obbligatorio.</p>

    <?php if ($err): ?>
        <div class="bg-red-900/40 border border-red-700 text-red-300 text-sm p-3 rounded mb-4"><?= e($err) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4 text-sm bg-gray-900 p-5 rounded-xl">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />

        <label class="block"><span class="text-gray-400 text-xs">Nome e cognome *</span>
            <input name="contact_name" required value="<?= e(post('contact_name')) ?>" class="w-full p-2 rounded text-black mt-1" />
        </label>

        <div class="grid grid-cols-2 gap-3">
            <label class="block"><span class="text-gray-400 text-xs">Email</span>
                <input name="email" type="email" value="<?= e(post('email')) ?>" class="w-full p-2 rounded text-black mt-1" />
            </label>
            <label class="block"><span class="text-gray-400 text-xs">Telefono</span>
                <input name="phone" value="<?= e(post('phone')) ?>" class="w-full p-2 rounded text-black mt-1" />
            </label>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <label class="block"><span class="text-gray-400 text-xs">Paese</span>
                <input name="country" value="<?= e(post('country')) ?>" class="w-full p-2 rounded text-black mt-1" />
            </label>
            <label class="block"><span class="text-gray-400 text-xs">Quantità</span>
                <input name="quantity" type="number" min="1" value="<?= e(post('quantity') ?: '1') ?>" class="w-full p-2 rounded text-black mt-1" />
            </label>
            <label class="block"><span class="text-gray-400 text-xs">Colore</span>
                <select name="variant" class="w-full p-2 rounded text-black mt-1">
                    <?php foreach ($cfg['variants'] as $v): ?>
                        <option value="<?= e($v) ?>"><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <label class="block"><span class="text-gray-400 text-xs">Fonte (es. telefonata, fiera, passaparola)</span>
            <input name="source" value="<?= e(post('source')) ?>" class="w-full p-2 rounded text-black mt-1" />
        </label>

        <label class="block"><span class="text-gray-400 text-xs">Note</span>
            <textarea name="notes" rows="3" class="w-full p-2 rounded text-black mt-1"><?= e(post('notes')) ?></textarea>
        </label>

        <button class="w-full bg-yellow-400 text-black py-2 rounded font-semibold hover:bg-yellow-300">Crea trattativa</button>
    </form>
</body>
</html>
