<?php
require __DIR__ . '/../inc/auth.php';
require __DIR__ . '/../inc/inventory.php';
require_login();

$colors = inv_colors();
$models = inv_models();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $action = post('action');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'create') {
        $cond  = post('item_condition') === 'usata' ? 'usata' : 'nuova';
        $model = post('model');
        $color = post('color');
        if (!isset($models[$model]))     { $err = 'Modello non valido.'; }
        elseif (!isset($colors[$color])) { $err = 'Colore non valido.'; }
        else {
            inv_create([
                'item_condition'   => $cond,
                'model'            => $model,
                'color'            => $color,
                'quantity'         => max(0, (int) post('quantity', '0')),
                'price'            => inv_parse_price(post('price')),
                'machine_warranty' => post('machine_warranty'),
                'battery_warranty' => post('battery_warranty'),
                'status'           => 'disponibile',
                'notes'            => post('notes'),
            ]);
            header('Location: inventory.php'); exit;
        }
    }

    // Cliente selezionato valido? (vuoto = nessuno)
    $dealId = (int) ($_POST['deal_id'] ?? 0);
    if ($dealId) {
        $chk = db()->prepare('SELECT 1 FROM deals WHERE id = ?');
        $chk->execute([$dealId]);
        if (!$chk->fetchColumn()) $dealId = 0;
    }

    // Salva la riga: quantità, stato, cliente collegato, prezzo/garanzie/colore.
    if ($action === 'save') {
        $item  = inv_get($id);
        $model = post('model');
        $color = post('color');
        $status = post('status');
        $newQty = max(0, (int) post('quantity', (string) ($item['quantity'] ?? 0)));
        // Rifornire una riga esaurita (0 + non disponibile) la rimette automaticamente disponibile.
        if ($item && $newQty > 0 && (int) $item['quantity'] === 0 && $item['status'] === 'non_disponibile') {
            $status = 'disponibile';
        }
        if ($item && isset($models[$model]) && isset($colors[$color]) && array_key_exists($status, inv_statuses())) {
            inv_update($id, [
                'model'            => $model,
                'color'            => $color,
                'price'            => inv_parse_price(post('price')),
                'machine_warranty' => post('machine_warranty'),
                'battery_warranty' => post('battery_warranty'),
                'status'           => $status,
                'deal_id'          => $dealId ?: null,
                'notes'            => $item['notes'],
            ]);
            inv_set_quantity($id, $newQty, current_admin());
            header('Location: inventory.php'); exit;
        }
        $err = 'Dati non validi, riga non salvata.';
    }

    // Vende un pezzo della riga al cliente selezionato (-1, storico, 0 → non disponibile).
    if ($action === 'sell') {
        if (!inv_get($id))      { $err = 'Riga non trovata.'; }
        elseif (!$dealId)       { $err = 'Seleziona prima il cliente, poi premi Venduta.'; }
        elseif (!inv_sell($id, $dealId, current_admin())) { $err = 'Nessun pezzo disponibile da vendere in questa riga.'; }
        else { header('Location: inventory.php'); exit; }
    }

    // Lotto nuove → genera una riga singola: "In trattativa" (reserve) o "Venduta" (sell_pool).
    if ($action === 'reserve' || $action === 'sell_pool') {
        $item = inv_get($id);
        if (!$item || !inv_is_pool($item)) { $err = 'Azione valida solo sui lotti di macchine nuove.'; }
        elseif (!$dealId)                  { $err = 'Seleziona prima il cliente.'; }
        elseif (!inv_commit_from_pool($id, $dealId, $action === 'sell_pool', current_admin())) { $err = 'Nessun pezzo disponibile nel lotto.'; }
        else { header('Location: inventory.php'); exit; }
    }

    // Riga singola → rientro nel lotto (trattativa saltata).
    if ($action === 'return') {
        if (inv_get($id)) inv_return_to_pool($id, current_admin());
        header('Location: inventory.php'); exit;
    }

    if ($action === 'delete') {
        if (inv_get($id)) inv_delete($id);
        header('Location: inventory.php'); exit;
    }
}

$csrf = csrf_token();
$total = inv_total_available();

// Colonne condivise da intestazione e righe: tutto su una riga sola,
// Cliente e azioni comprese. Su schermi stretti la tabella scorre in orizzontale.
const INV_GRID = 'grid grid-cols-[52px_140px_minmax(110px,0.9fr)_54px_80px_88px_88px_120px_minmax(140px,1.1fr)_250px] gap-2 items-center';

function inv_row(array $it, array $colors, array $models, array $deals, string $csrf): string {
    $specs = inv_model_specs($it['model']);
    $hex = inv_color_hex($it['color']);
    $priceVal = $it['price'] !== null ? number_format((float) $it['price'], 0, ',', '.') : '';

    $colorOpts = '';
    foreach (array_keys($colors) as $name) {
        $colorOpts .= '<option value="' . e($name) . '"' . ($name === $it['color'] ? ' selected' : '') . '>' . e($name) . '</option>';
    }
    $modelOpts = '';
    foreach (array_keys($models) as $m) {
        $modelOpts .= '<option value="' . e($m) . '"' . ($m === $it['model'] ? ' selected' : '') . '>' . e($m) . '</option>';
    }
    $statusOpts = '';
    foreach (inv_statuses() as $s => $label) {
        $statusOpts .= '<option value="' . $s . '"' . ($s === $it['status'] ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    $crm = crm_statuses();
    $dealOpts = '<option value="">— nessuno —</option>';
    foreach ($deals as $d) {
        $lbl = $d['contact_name'];
        if (!empty($d['company_name'])) $lbl .= ' · ' . $d['company_name'];
        $lbl .= ' (' . ($crm[$d['status']] ?? $d['status']) . ')';
        $sel = ((int) $it['deal_id'] === (int) $d['id']) ? ' selected' : '';
        $dealOpts .= '<option value="' . (int) $d['id'] . '"' . $sel . '>' . e($lbl) . '</option>';
    }

    // Storico vendite di questa riga
    $soldHtml = '';
    $sales = inv_sales((int) $it['id']);
    if ($sales) {
        $parts = [];
        foreach ($sales as $s) {
            $who = $s['contact_name'] ?: ($s['company_name'] ?: 'cliente rimosso');
            $parts[] = e($who) . ' <span class="text-gray-600">(' . e(substr($s['created_at'], 0, 10)) . ')</span>';
        }
        $soldHtml = '<span class="text-gray-400 text-xs">Venduta a: ' . implode(', ', $parts) . '</span>';
    }

    $isPool = inv_is_pool($it);
    $isCommittedNew = ($it['item_condition'] === 'nuova' && !empty($it['deal_id']));

    $btnSave = '<button name="action" value="save" class="bg-yellow-400 text-black px-2 py-1 rounded text-xs font-semibold hover:bg-yellow-300">Salva</button>';
    $btnDelete = '<button name="action" value="delete" onclick="return confirm(\'Eliminare definitivamente questa riga?\')" class="text-red-400 hover:text-red-300 text-xs">Elimina</button>';
    if ($isPool) {
        // Lotto: i pulsanti generano una riga singola per il pezzo.
        $actionsHtml =
            '<button name="action" value="reserve" class="bg-gray-700 text-white px-2 py-1 rounded text-xs font-semibold hover:bg-gray-600">In trattativa</button>'
          . '<button name="action" value="sell_pool" onclick="return confirm(\'Segnare una macchina del lotto come venduta al cliente selezionato? Il disponibile calerà di 1.\')" class="bg-green-700 text-white px-2 py-1 rounded text-xs font-semibold hover:bg-green-600">Venduta</button>'
          . $btnSave . $btnDelete;
    } else {
        $sellBtn = '<button name="action" value="sell" onclick="return confirm(\'Segnare come venduta al cliente selezionato? Il numero calerà di 1.\')" class="bg-green-700 text-white px-2 py-1 rounded text-xs font-semibold hover:bg-green-600">Venduta</button>';
        $returnBtn = $isCommittedNew
            ? '<button name="action" value="return" title="Rimetti nel lotto disponibile" onclick="return confirm(\'Rimettere questo pezzo nel lotto disponibile?\')" class="bg-gray-700 text-white px-2 py-1 rounded text-xs hover:bg-gray-600">↩ Lotto</button>'
            : '';
        $actionsHtml = $sellBtn . $returnBtn . $btnSave . $btnDelete;
    }

    $inp = 'p-1 rounded text-black text-sm w-full';
    $rowBg = $isCommittedNew ? ' bg-gray-900/40' : '';
    ob_start(); ?>
    <form method="post" class="border-b border-gray-800 py-2<?= $rowBg ?>">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
        <div class="<?= INV_GRID ?>">
            <select name="model" class="<?= $inp ?>"><?= $modelOpts ?></select>
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-full shrink-0" style="background:<?= e($hex) ?>"></span>
                <select name="color" class="<?= $inp ?>"><?= $colorOpts ?></select>
            </div>
            <div class="text-gray-400 text-xs"><?= e($specs['speed']) ?> · <?= e($specs['height']) ?> · <?= e($specs['battery']) ?></div>
            <input type="number" name="quantity" min="0" value="<?= (int) $it['quantity'] ?>" class="<?= $inp ?> text-center font-bold">
            <input type="text" name="price" value="<?= e($priceVal) ?>" placeholder="€" class="<?= $inp ?>">
            <input type="text" name="machine_warranty" value="<?= e($it['machine_warranty'] ?? '') ?>" placeholder="es. 2 anni" class="<?= $inp ?>">
            <input type="text" name="battery_warranty" value="<?= e($it['battery_warranty'] ?? '') ?>" placeholder="es. 3 anni" class="<?= $inp ?>">
            <select name="status" class="<?= $inp ?> status-select font-semibold"><?= $statusOpts ?></select>
            <select name="deal_id" title="<?= $isPool ? 'Cliente a cui assegnare il pezzo' : 'Cliente collegato' ?>" class="<?= $inp ?>"><?= $dealOpts ?></select>
            <div class="flex flex-wrap items-center gap-1"><?= $actionsHtml ?></div>
        </div>
        <?php if ($soldHtml): ?><div class="mt-1 pl-1"><?= $soldHtml ?></div><?php endif; ?>
    </form>
    <?php
    return ob_get_clean();
}

function inv_group(array $rows, array $colors, array $models, array $deals, string $csrf): string {
    if (!$rows) {
        return '<p class="text-gray-500 text-sm py-4">Nessuna macchina in questo gruppo.</p>';
    }
    $head = '<div class="' . INV_GRID . ' text-gray-400 text-xs border-b border-gray-700 pb-2">'
        . '<div>Modello</div><div>Colore</div><div>Specifiche</div><div class="text-center">Q.tà</div>'
        . '<div>Prezzo €</div><div>Gar. macchina</div><div>Gar. batteria</div><div>Stato</div>'
        . '<div>Cliente</div><div></div></div>';
    $body = '';
    foreach ($rows as $it) { $body .= inv_row($it, $colors, $models, $deals, $csrf); }
    return '<div class="overflow-x-auto"><div class="min-w-[1240px]">' . $head . $body . '</div></div>';
}

$deals = inv_deals_for_select();
$nuove = inv_list('nuova');
$usate = inv_list('usata');
?>
<!doctype html>
<html lang="it">
<head>
    <meta name="robots" content="noindex, nofollow" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Magazzino · V12 CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0b0f19;color:#e5e7eb;font-family:system-ui,Arial,sans-serif}</style>
</head>
<body class="p-6">
    <header class="flex justify-between items-center mb-2">
        <h1 class="text-xl font-bold">Magazzino
            <span class="text-gray-500 text-sm font-normal">· <?= (int) $total ?> disponibili</span>
        </h1>
        <div class="flex items-center gap-4 text-sm text-gray-400">
            <a href="index.php" class="text-yellow-400">← Trattative</a>
            <span><?= e(current_admin()) ?> · <a href="logout.php" class="text-yellow-400">Esci</a></span>
        </div>
    </header>
    <p class="text-gray-500 text-xs mb-6 max-w-4xl">
        <b>Nuove (lotto):</b> per rendere disponibile un modello scrivi la <b>Q.tà</b> e premi <b>Salva</b>. Poi scegli il cliente e premi <b>In trattativa</b> o <b>Venduta</b> — nasce una riga per quel pezzo e il disponibile cala di 1. Se una trattativa salta, sul pezzo premi <b>↩ Lotto</b>.
        &nbsp;·&nbsp; <b>Usate:</b> gestione diretta sulla riga (quantità, stato, Venduta).
    </p>

    <?php if ($err): ?>
        <div class="mb-4 px-3 py-2 rounded bg-red-900/60 text-red-200 text-sm"><?= e($err) ?></div>
    <?php endif; ?>

    <section class="mb-10">
        <h2 class="text-lg font-semibold mb-3">Nuove</h2>
        <?= inv_group($nuove, $colors, $models, $deals, $csrf) ?>
    </section>

    <section class="mb-10">
        <h2 class="text-lg font-semibold mb-3">Usate / Ricondizionate</h2>
        <?= inv_group($usate, $colors, $models, $deals, $csrf) ?>
    </section>

    <section class="mb-10">
        <h2 class="text-lg font-semibold mb-3">Vendute</h2>
        <?php $sales = inv_sales_all(); if (!$sales): ?>
            <p class="text-gray-500 text-sm">Nessuna vendita registrata.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse min-w-[640px]">
                <thead class="text-left text-gray-400 border-b border-gray-700">
                    <tr>
                        <th class="py-2 pr-4">Data</th><th class="py-2 pr-4">Modello</th>
                        <th class="py-2 pr-4">Colore</th><th class="py-2 pr-4">Tipo</th>
                        <th class="py-2 pr-4">Cliente</th><th class="py-2 pr-4">Prezzo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($sales as $s):
                    $client = $s['contact_name'] ?: ($s['company_name'] ?: '—');
                    $price = $s['price'] !== null ? number_format((float) $s['price'], 0, ',', '.') . ' ' . e($s['currency']) : '—';
                ?>
                    <tr class="border-b border-gray-800">
                        <td class="py-2 pr-4 text-gray-400 whitespace-nowrap"><?= e(substr($s['created_at'], 0, 10)) ?></td>
                        <td class="py-2 pr-4 font-mono"><?= e($s['model']) ?></td>
                        <td class="py-2 pr-4 whitespace-nowrap"><span class="inline-block w-2.5 h-2.5 rounded-full align-middle mr-1" style="background:<?= e(inv_color_hex($s['color'])) ?>"></span><?= e($s['color']) ?></td>
                        <td class="py-2 pr-4 text-gray-400"><?= $s['item_condition'] === 'usata' ? 'Usata' : 'Nuova' ?></td>
                        <td class="py-2 pr-4"><?php if ($s['deal_id']): ?><a href="deal.php?id=<?= (int) $s['deal_id'] ?>" class="text-yellow-400 hover:underline"><?= e($client) ?></a><?php else: ?><?= e($client) ?><?php endif; ?></td>
                        <td class="py-2 pr-4 font-semibold whitespace-nowrap"><?= $price ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>

    <section class="border-t border-gray-800 pt-6">
        <h2 class="text-lg font-semibold mb-3">Aggiungi una macchina</h2>
        <form method="post" class="flex flex-wrap items-end gap-3 text-sm">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="create">
            <label>Condizione<br>
                <select name="item_condition" class="p-2 rounded text-black mt-1">
                    <option value="nuova">Nuova</option>
                    <option value="usata">Usata / Ricondizionata</option>
                </select>
            </label>
            <label>Modello<br>
                <select name="model" class="p-2 rounded text-black mt-1">
                    <?php foreach (array_keys($models) as $m): ?>
                        <option value="<?= e($m) ?>"><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Colore<br>
                <select name="color" class="p-2 rounded text-black mt-1">
                    <?php foreach (array_keys($colors) as $c): ?>
                        <option value="<?= e($c) ?>"><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Quantità<br><input type="number" name="quantity" min="0" value="1" class="w-20 p-2 rounded text-black mt-1"></label>
            <label>Prezzo €<br><input name="price" placeholder="es. 4900" class="w-28 p-2 rounded text-black mt-1"></label>
            <label>Garanzia macchina<br><input name="machine_warranty" placeholder="es. 2 anni" class="w-32 p-2 rounded text-black mt-1"></label>
            <label>Garanzia batteria<br><input name="battery_warranty" placeholder="es. 3 anni" class="w-32 p-2 rounded text-black mt-1"></label>
            <button class="bg-yellow-400 text-black px-4 py-2 rounded font-semibold hover:bg-yellow-300">Aggiungi</button>
        </form>
    </section>

    <script>
        // Colora lo stato: verde disponibile/prenotata, rosso non disponibile, arancione in trattativa.
        (function () {
            var col = {
                disponibile: '#15803d',
                prenotata: '#15803d',
                non_disponibile: '#dc2626',
                in_trattativa: '#ea580c'
            };
            function paint(s) { s.style.color = col[s.value] || '#000'; }
            document.querySelectorAll('select.status-select').forEach(function (s) {
                paint(s);
                s.addEventListener('change', function () { paint(s); });
            });
        })();
    </script>
</body>
</html>
