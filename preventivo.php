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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face { font-family: "Fauna"; src: url("/assets/fonts/fauna-thin.woff2") format("woff2"); font-weight: 300; font-display: swap; }
        body { font-family: "Fauna", "Helvetica Neue", Helvetica, Arial, sans-serif; background:#000; color:#fff; }
    </style>
</head>
<body>
    <nav class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-black/70 backdrop-blur-md">
        <a href="index.html"><img src="/assets/img/logo.png" alt="Wazlley" class="h-10" /></a>
    </nav>

    <main class="pt-28 pb-20 px-4 max-w-xl mx-auto">
    <?php if (!$deal): ?>
        <div class="bg-gray-900/50 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-2">Preventivo non disponibile</h1>
            <p class="text-gray-400">Il link non è valido o il preventivo non è ancora stato inviato.</p>
        </div>
    <?php else: ?>
        <div class="bg-gray-900/50 p-8 rounded-2xl">
            <h1 class="text-2xl font-bold mb-1">Il tuo preventivo</h1>
            <p class="text-gray-400 text-sm mb-6">Ciao <?= e($deal['contact_name']) ?>, ecco la nostra offerta.</p>

            <div class="space-y-2 border-y border-gray-700 py-4 mb-6">
                <div class="flex justify-between"><span class="text-gray-400">Prodotto</span><span><?= e($cfg['name']) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Colore</span><span><?= e($deal['variant']) ?></span></div>
                <div class="flex justify-between"><span class="text-gray-400">Quantità</span><span><?= (int)$deal['quantity'] ?></span></div>
                <div class="flex justify-between text-lg font-semibold pt-2">
                    <span>Prezzo</span>
                    <span class="text-yellow-400"><?= $deal['quoted_price'] !== null ? number_format((float)$deal['quoted_price'], 2, ',', '.') . ' ' . e($deal['currency']) : 'da definire' ?></span>
                </div>
            </div>

            <?php if ($deal['accepted_at']): ?>
                <p class="text-center text-green-400 font-semibold mb-3">Hai già accettato questa offerta. Grazie!</p>
                <p class="text-center text-sm text-gray-400"><a href="<?= e('dati.php?token=' . $token) ?>" class="text-yellow-400 underline">Inserisci/aggiorna i tuoi dati di fatturazione</a></p>
            <?php else: ?>
                <div id="box">
                    <p class="text-sm text-gray-400 mb-4">Se l'offerta ti soddisfa, accettala qui sotto: ti ricontatteremo per finalizzare l'ordine e i dati di fatturazione. Per domande o per concordare condizioni, rispondi pure all'email.</p>
                    <button id="acceptBtn" class="w-full bg-yellow-400 text-black px-6 py-3 rounded font-semibold hover:bg-yellow-300 transition">
                        Accetto l'offerta
                    </button>
                    <p class="text-center text-sm text-gray-400 mt-4">
                        Hai fretta? <a href="<?= e('dati.php?token=' . $token) ?>" class="text-yellow-400 underline">Inserisci subito i tuoi dati di fatturazione</a>
                    </p>
                </div>
                <div id="ok" class="hidden text-center text-green-400 font-semibold">Offerta accettata! Grazie, ti contatteremo a breve.</div>
                <script>
                    document.getElementById('acceptBtn').addEventListener('click', async () => {
                        const fd = new FormData();
                        fd.append('csrf', '<?= e($csrf) ?>');
                        fd.append('token', '<?= e($token) ?>');
                        const res = await fetch('/api/accept_quote.php', { method: 'POST', body: fd });
                        let data = {}; try { data = await res.json(); } catch (e) {}
                        if (data.ok) {
                            document.getElementById('box').classList.add('hidden');
                            document.getElementById('ok').classList.remove('hidden');
                        } else if (data.error === 'csrf') {
                            alert('La pagina era aperta da troppo tempo. Ricarica e riprova.');
                        } else {
                            alert('Errore. Riprova o contattaci via email.');
                        }
                    });
                </script>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </main>
</body>
</html>
