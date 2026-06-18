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
    <title>Richiesta preventivo / ordine — Wazlley</title>
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
        <!-- Step indicator -->
        <div class="flex items-center gap-3 mb-8 text-sm">
            <span id="ind1" class="px-3 py-1 rounded-full bg-yellow-400 text-black font-semibold">1 · Preventivo</span>
            <span class="text-gray-600">—</span>
            <span id="ind2" class="px-3 py-1 rounded-full bg-gray-800 text-gray-400">2 · Dati ordine</span>
        </div>

        <!-- STEP 1 -->
        <form id="step1" class="space-y-4 bg-gray-900/50 p-6 rounded-2xl">
            <h1 class="text-2xl font-bold mb-2">Richiedi un preventivo</h1>
            <p class="text-gray-400 text-sm mb-4">
                <?= e($cfg['name']) ?> — a partire da
                <span class="text-yellow-400 font-semibold"><?= number_format((float)$cfg['price_from'], 0, ',', '.') ?> <?= e($cfg['currency']) ?></span> a unità.
            </p>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <!-- honeypot -->
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
                    <input class="field" type="number" name="quantity" min="1" value="1" id="qty" />
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
                <label class="lbl">Note</label>
                <textarea class="field" name="notes" rows="3"></textarea>
            </div>
            <label class="flex items-start gap-2 text-sm text-gray-300">
                <input type="checkbox" name="consent" value="1" class="mt-1" required />
                <span>Acconsento al trattamento dei dati secondo la
                    <a href="privacy.html" class="text-yellow-400 underline">Privacy Policy</a>.</span>
            </label>
            <div class="err" data-err="consent"></div>

            <button type="submit" class="w-full bg-yellow-400 text-black px-6 py-3 rounded font-semibold hover:bg-yellow-300 transition">
                Continua →
            </button>
            <p class="text-center text-xs text-gray-500">Riceverai il preventivo via email. Vuoi anche procedere all'ordine? Compila il passo successivo.</p>
        </form>

        <!-- STEP 2 -->
        <form id="step2" class="space-y-4 bg-gray-900/50 p-6 rounded-2xl hidden">
            <h1 class="text-2xl font-bold mb-2">Dati per l'ordine e la fatturazione</h1>
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <input type="hidden" name="token" id="orderToken" />

            <div class="flex gap-4 text-sm">
                <label class="flex items-center gap-2"><input type="radio" name="customer_type" value="privato" checked /> Privato</label>
                <label class="flex items-center gap-2"><input type="radio" name="customer_type" value="azienda" /> Azienda</label>
            </div>

            <div id="companyFields" class="grid md:grid-cols-2 gap-4 hidden">
                <div>
                    <label class="lbl">Ragione sociale *</label>
                    <input class="field" name="company_name" />
                    <div class="err" data-err="company_name"></div>
                </div>
                <div>
                    <label class="lbl">P.IVA / VAT *</label>
                    <input class="field" name="vat_number" />
                    <div class="err" data-err="vat_number"></div>
                </div>
                <div>
                    <label class="lbl">Codice SDI</label>
                    <input class="field" name="sdi_code" maxlength="7" />
                </div>
                <div>
                    <label class="lbl">PEC</label>
                    <input class="field" name="pec" />
                </div>
                <div>
                    <label class="lbl">EORI (export)</label>
                    <input class="field" name="eori" />
                </div>
            </div>
            <div>
                <label class="lbl">Codice fiscale</label>
                <input class="field" name="tax_code" />
            </div>

            <h2 class="text-lg font-semibold pt-2">Indirizzo di fatturazione</h2>
            <div>
                <label class="lbl">Indirizzo *</label>
                <input class="field" name="bill_address" />
                <div class="err" data-err="bill_address"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="lbl">Città *</label>
                    <input class="field" name="bill_city" />
                    <div class="err" data-err="bill_city"></div>
                </div>
                <div>
                    <label class="lbl">CAP</label>
                    <input class="field" name="bill_zip" />
                </div>
                <div>
                    <label class="lbl">Provincia / Stato</label>
                    <input class="field" name="bill_province" />
                </div>
                <div>
                    <label class="lbl">Paese *</label>
                    <input class="field" name="bill_country" />
                    <div class="err" data-err="bill_country"></div>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="ship_same" value="1" id="shipSame" checked />
                Spedizione allo stesso indirizzo
            </label>

            <div id="shipFields" class="hidden space-y-4">
                <h2 class="text-lg font-semibold">Indirizzo di spedizione</h2>
                <input class="field" name="ship_address" placeholder="Indirizzo" />
                <div class="grid md:grid-cols-2 gap-4">
                    <input class="field" name="ship_city" placeholder="Città" />
                    <input class="field" name="ship_zip" placeholder="CAP" />
                    <input class="field" name="ship_province" placeholder="Provincia / Stato" />
                    <input class="field" name="ship_country" placeholder="Paese" />
                </div>
            </div>

            <button type="submit" class="w-full bg-yellow-400 text-black px-6 py-3 rounded font-semibold hover:bg-yellow-300 transition">
                Invia ordine
            </button>
        </form>

        <!-- DONE -->
        <div id="done" class="hidden bg-gray-900/50 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-3">Grazie! ✅</h1>
            <p class="text-gray-300" id="doneMsg"></p>
            <a href="index.html" class="inline-block mt-6 px-6 py-3 bg-yellow-400 text-black rounded font-semibold">Torna alla Home</a>
        </div>
    </main>

    <script>
        const $ = (s) => document.querySelector(s);
        function clearErrors(form) { form.querySelectorAll('[data-err]').forEach(el => el.textContent = ''); }
        function showErrors(form, errors) {
            for (const k in errors) { const el = form.querySelector(`[data-err="${k}"]`); if (el) el.textContent = errors[k]; }
        }
        async function send(url, form) {
            const res = await fetch(url, { method: 'POST', body: new FormData(form) });
            let data = {}; try { data = await res.json(); } catch (e) {}
            return data;
        }

        // STEP 1
        $('#step1').addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors(e.target);
            const data = await send('/api/submit_lead.php', e.target);
            if (data.ok) {
                $('#orderToken').value = data.token;
                $('#step1').classList.add('hidden');
                $('#step2').classList.remove('hidden');
                $('#ind1').className = 'px-3 py-1 rounded-full bg-gray-800 text-gray-400';
                $('#ind2').className = 'px-3 py-1 rounded-full bg-yellow-400 text-black font-semibold';
                window.scrollTo(0, 0);
            } else if (data.errors) {
                showErrors($('#step1'), data.errors);
            } else if (data.error === 'csrf') {
                alert('La pagina era aperta da troppo tempo. Ricarica (Cmd+R) e riprova.');
            } else {
                alert('Errore nell\'invio. Riprova.');
            }
        });

        // toggle azienda
        document.querySelectorAll('input[name="customer_type"]').forEach(r =>
            r.addEventListener('change', () => {
                $('#companyFields').classList.toggle('hidden', document.querySelector('input[name="customer_type"]:checked').value !== 'azienda');
            }));
        // toggle spedizione
        $('#shipSame').addEventListener('change', () => {
            $('#shipFields').classList.toggle('hidden', $('#shipSame').checked);
        });

        // STEP 2
        $('#step2').addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors(e.target);
            const data = await send('/api/submit_order.php', e.target);
            if (data.ok) {
                $('#step2').classList.add('hidden');
                $('#doneMsg').textContent = 'Abbiamo ricevuto la tua richiesta d\'ordine. Ti contatteremo al più presto con il preventivo definitivo.';
                $('#done').classList.remove('hidden');
                window.scrollTo(0, 0);
            } else if (data.errors) {
                showErrors($('#step2'), data.errors);
            } else if (data.error === 'not_found') {
                alert('Sessione scaduta o ordine già inviato.');
            } else if (data.error === 'csrf') {
                alert('La pagina era aperta da troppo tempo. Ricarica (Cmd+R) e riprova.');
            } else {
                alert('Errore nell\'invio. Riprova.');
            }
        });
    </script>
</body>
</html>
