<?php
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/inc/db.php';
$cfg = require __DIR__ . '/inc/product.php';

$token = $_GET['token'] ?? '';
$deal = null;
if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $st = db()->prepare('SELECT * FROM deals WHERE token = ? AND quote_sent_at IS NOT NULL');
    $st->execute([$token]);
    $deal = $st->fetch() ?: null;
}
$csrf = csrf_token();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Il tuo preventivo — Wazlley</title>
    <link rel="icon" href="/assets/img/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", "Helvetica Neue", Arial, sans-serif; background:#ffffff; color:#1d1d1f; }
        h1, h2, h3 { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Inter", sans-serif; font-weight: 600; letter-spacing:-0.022em; }
    </style>
</head>
<body>
    <nav class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-white/85 border-b border-gray-200 text-gray-800 backdrop-blur-md">
        <a href="index.html"><img src="/assets/img/logo.png" alt="Wazlley" class="h-10" /></a>
    </nav>

    <main class="pt-28 pb-20 px-4 max-w-xl mx-auto">
    <?php if (!$deal): ?>
        <div class="bg-gray-50 border border-gray-200 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-2">Preventivo non disponibile</h1>
            <p class="text-gray-500">Il link non è valido o il preventivo non è ancora stato inviato.</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-50 border border-gray-200 p-8 rounded-2xl">
            <h1 class="text-2xl font-bold mb-1">Il tuo preventivo</h1>
            <p class="text-gray-500 text-sm mb-6">Ciao <?= e($deal['contact_name']) ?>, ecco la nostra offerta.</p>

            <div class="space-y-2 border-y border-gray-200 py-4 mb-6">
                <div class="flex justify-between"><span class="text-gray-500">Prodotto</span><span><?= e($cfg['name']) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Colore</span><span><?= e($deal['variant']) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Quantità</span><span><?= (int)$deal['quantity'] ?></span></div>
                <div class="flex justify-between text-lg font-semibold pt-2">
                    <span>Prezzo</span>
                    <span class="text-[#0096e0]"><?= $deal['quoted_price'] !== null ? number_format((float)$deal['quoted_price'], 2, ',', '.') . ' ' . e($deal['currency']) : 'da definire' ?></span>
                </div>
            </div>

            <?php if ($deal['accepted_at']): ?>
                <p class="text-center text-green-600 font-semibold mb-3">Ordine confermato. Grazie!</p>
                <p class="text-center text-sm text-gray-500"><a href="<?= e('dati.php?token=' . $token) ?>" class="text-[#0096e0] underline">Rivedi o aggiorna i tuoi dati di fatturazione</a></p>
            <?php else: ?>
                <p class="text-sm text-gray-500 mb-4">Per procedere con l'ordine, completa i tuoi dati di fatturazione: ci vogliono un paio di minuti. La P.IVA viene verificata automaticamente.</p>
                <a href="<?= e('dati.php?token=' . $token) ?>" class="block w-full bg-[#f59000] text-white px-6 py-3 rounded-full font-medium hover:bg-[#e08400] transition text-center">
                    Compila i dati e conferma l'ordine
                </a>
                <p class="text-center text-xs text-gray-400 mt-4">Per domande o condizioni particolari, rispondi pure all'email.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </main>
</body>
</html>
