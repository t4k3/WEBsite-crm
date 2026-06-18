<?php
require __DIR__ . '/inc/helpers.php';
$cfg  = require __DIR__ . '/inc/product.php';
$csrf = csrf_token();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Richiedi un preventivo — Wazlley</title>
    <link rel="icon" href="/assets/img/favicon.ico" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face { font-family: "Fauna"; src: url("/assets/fonts/fauna-thin.woff2") format("woff2"); font-weight: 300; font-display: swap; }
        body { font-family: "Fauna", "Helvetica Neue", Helvetica, Arial, sans-serif; background:#000; color:#fff; }
        .field { width:100%; padding:.65rem; border-radius:.5rem; color:#000; }
        .lbl { display:block; font-size:.8rem; color:#cbd5e1; margin-bottom:.25rem; }
        .err { color:#f87171; font-size:.75rem; margin-top:.2rem; min-height:1rem; }
    </style>
</head>
<body>
    <nav class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-black/70 backdrop-blur-md">
        <a href="index.html" class="flex items-center gap-2"><img src="/assets/img/logo.png" alt="Wazlley" class="h-10" /></a>
        <a href="index.html" class="text-sm hover:text-yellow-400">← Home</a>
    </nav>

    <main class="pt-28 pb-20 px-4 max-w-2xl mx-auto">
        <form id="reqForm" class="space-y-4 bg-gray-900/50 p-6 rounded-2xl">
            <h1 class="text-2xl font-bold mb-1">Richiedi un preventivo</h1>
            <p class="text-gray-400 text-sm mb-4">
                <?= e($cfg['name']) ?> — a partire da
                <span class="text-yellow-400 font-semibold"><?= number_format((float)$cfg['price_from'], 0, ',', '.') ?> <?= e($cfg['currency']) ?></span> a unità.
                Ti invieremo un preventivo personalizzato.
            </p>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <input type="text" name="company" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />

            <div>
                <label class="lbl">Nome e cognome *</label>
                <input class="field" name="contact_name" required />
                <div class="err" data-err="contact_name"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="lbl">Email *</label>
                    <input class="field" type="email" name="email" required />
                    <div class="err" data-err="email"></div>
                </div>
                <div>
                    <label class="lbl">Telefono</label>
                    <input class="field" name="phone" />
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="lbl">Paese</label>
                    <input class="field" name="country" />
                </div>
                <div>
                    <label class="lbl">Quantità</label>
                    <input class="field" type="number" name="quantity" min="1" value="1" />
                </div>
                <div>
                    <label class="lbl">Colore</label>
                    <select class="field" name="variant">
                        <?php foreach ($cfg['variants'] as $v): ?>
                            <option value="<?= e($v) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="lbl">Note (esigenze, tempistiche, domande)</label>
                <textarea class="field" name="notes" rows="3"></textarea>
            </div>

            <div class="bg-black/30 rounded-lg p-4 space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="want_call" value="1" id="wantCall" />
                    Vorrei essere ricontattato con una call
                </label>
                <div id="availBox" class="hidden">
                    <label class="lbl">Quando sei reperibile?</label>
                    <input class="field" name="availability" placeholder="es. lun–ven 9–13, oppure un orario preciso" />
                </div>
            </div>

            <label class="flex items-start gap-2 text-sm text-gray-300">
                <input type="checkbox" name="consent" value="1" class="mt-1" required />
                <span>Acconsento al trattamento dei dati secondo la
                    <a href="privacy.html" class="text-yellow-400 underline">Privacy Policy</a>.</span>
            </label>
            <div class="err" data-err="consent"></div>

            <button type="submit" class="w-full bg-yellow-400 text-black px-6 py-3 rounded font-semibold hover:bg-yellow-300 transition">
                Invia richiesta
            </button>
        </form>

        <div id="done" class="hidden bg-gray-900/50 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-3">Grazie! ✅</h1>
            <p class="text-gray-300">Abbiamo ricevuto la tua richiesta. Ti invieremo al più presto un preventivo personalizzato<span id="callNote"></span>.</p>
            <a href="index.html" class="inline-block mt-6 px-6 py-3 bg-yellow-400 text-black rounded font-semibold">Torna alla Home</a>
        </div>
    </main>

    <script>
        const $ = (s) => document.querySelector(s);
        $('#wantCall').addEventListener('change', () => $('#availBox').classList.toggle('hidden', !$('#wantCall').checked));

        $('#reqForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.target.querySelectorAll('[data-err]').forEach(el => el.textContent = '');
            const res = await fetch('/api/submit_lead.php', { method: 'POST', body: new FormData(e.target) });
            let data = {}; try { data = await res.json(); } catch (err) {}
            if (data.ok) {
                if ($('#wantCall').checked) $('#callNote').textContent = ' e ti ricontatteremo per la call';
                $('#reqForm').classList.add('hidden');
                $('#done').classList.remove('hidden');
                window.scrollTo(0, 0);
            } else if (data.errors) {
                for (const k in data.errors) { const el = e.target.querySelector(`[data-err="${k}"]`); if (el) el.textContent = data.errors[k]; }
            } else if (data.error === 'csrf') {
                alert('La pagina era aperta da troppo tempo. Ricarica (Cmd+R) e riprova.');
            } else {
                alert('Errore nell\'invio. Riprova.');
            }
        });
    </script>
</body>
</html>
