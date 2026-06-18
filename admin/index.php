<?php
require __DIR__ . '/../inc/auth.php';
require_login();

$statuses = crm_statuses();
$filter = $_GET['status'] ?? '';

$sql = 'SELECT id, created_at, status, paid, shipment, contact_name, email, country, quantity, company_name FROM deals';
$params = [];
if (isset($statuses[$filter])) {
    $sql .= ' WHERE status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$counts = db()->query('SELECT status, COUNT(*) c FROM deals GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($counts);
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wazlley CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0b0f19;color:#e5e7eb;font-family:system-ui,Arial,sans-serif}</style>
</head>
<body class="p-6">
    <header class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Wazlley CRM <span class="text-gray-500 text-sm">(<?= (int)$total ?> trattative)</span></h1>
        <div class="text-sm text-gray-400">
            <?= e(current_admin()) ?> · <a href="logout.php" class="text-yellow-400">Esci</a>
        </div>
    </header>

    <nav class="flex flex-wrap gap-2 mb-6 text-sm">
        <a href="index.php" class="px-3 py-1 rounded-full <?= $filter === '' ? 'bg-yellow-400 text-black' : 'bg-gray-800' ?>">Tutte (<?= (int)$total ?>)</a>
        <?php foreach ($statuses as $k => $label): ?>
            <a href="?status=<?= e($k) ?>" class="px-3 py-1 rounded-full <?= $filter === $k ? 'bg-yellow-400 text-black' : 'bg-gray-800' ?>">
                <?= e($label) ?> (<?= (int)($counts[$k] ?? 0) ?>)
            </a>
        <?php endforeach; ?>
    </nav>

    <table class="w-full text-sm border-collapse">
        <thead class="text-left text-gray-400 border-b border-gray-700">
            <tr>
                <th class="py-2 pr-4">Data</th>
                <th class="py-2 pr-4">Nome / Azienda</th>
                <th class="py-2 pr-4">Email</th>
                <th class="py-2 pr-4">Paese</th>
                <th class="py-2 pr-4">Q.tà</th>
                <th class="py-2 pr-4">Stato</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="6" class="py-6 text-center text-gray-500">Nessuna trattativa.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr class="border-b border-gray-800 hover:bg-gray-900 cursor-pointer" onclick="location='deal.php?id=<?= (int)$r['id'] ?>'">
                <td class="py-2 pr-4 whitespace-nowrap text-gray-400"><?= e(substr($r['created_at'], 0, 16)) ?></td>
                <td class="py-2 pr-4"><?= e($r['contact_name']) ?><?= $r['company_name'] ? ' · <span class="text-gray-400">' . e($r['company_name']) . '</span>' : '' ?></td>
                <td class="py-2 pr-4 text-gray-300"><?= e($r['email']) ?></td>
                <td class="py-2 pr-4"><?= e($r['country']) ?></td>
                <td class="py-2 pr-4"><?= (int)$r['quantity'] ?></td>
                <td class="py-2 pr-4">
                    <span class="px-2 py-0.5 rounded bg-gray-700 text-xs"><?= e($statuses[$r['status']] ?? $r['status']) ?></span>
                    <?php if ($r['paid']): ?><span class="px-1.5 py-0.5 rounded bg-green-700 text-xs">pagato</span><?php endif; ?>
                    <?php if ($r['shipment'] !== 'non_spedito'): ?><span class="px-1.5 py-0.5 rounded bg-blue-700 text-xs"><?= e($r['shipment'] === 'consegnato' ? 'consegnato' : 'spedito') ?></span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
