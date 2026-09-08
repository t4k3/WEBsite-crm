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
    <title data-i18n="req_page_title">Richiedi un preventivo | Takeoff.pro</title>
    <meta name="description" content="Richiedi un preventivo personalizzato per la macchina spara palloni Takeoff.pro. Ti rispondiamo con un'offerta dedicata al tuo club." />
    <link rel="canonical" href="https://www.takeoff.pro/richiesta.php" />
    <link rel="icon" href="/assets/img/favicon.ico" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", "Helvetica Neue", Arial, sans-serif; background:#ffffff; color:#1d1d1f; }
        h1, h2, h3 { font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Inter", sans-serif; font-weight: 600; letter-spacing:-0.022em; }
        .field { width:100%; padding:.65rem; border-radius:.5rem; color:#000; background:#fff; border:1px solid #d1d5db; }
        .lbl { display:block; font-size:.8rem; color:#44443f; margin-bottom:.25rem; }
        .err { color:#dc2626; font-size:.75rem; margin-top:.2rem; min-height:1rem; }
        .lang-switch { cursor:pointer; }
    </style>
</head>
<body>
    <nav class="fixed top-0 w-full z-50 flex justify-between items-center px-6 py-4 bg-white/85 border-b border-gray-200 text-gray-800 backdrop-blur-md">
        <a href="index.html" class="flex items-center gap-2"><img src="/assets/img/logo.png" alt="V12" class="h-10" /></a>
        <div class="flex items-center gap-4">
            <div class="flex gap-2">
                <button class="lang-switch text-lg hover:opacity-80 transition" data-lang="it" title="Italiano">🇮🇹</button>
                <button class="lang-switch text-lg hover:opacity-80 transition" data-lang="en" title="English (USA)">🇺🇸</button>
                <button class="lang-switch text-lg hover:opacity-80 transition" data-lang="fr" title="Français">🇫🇷</button>
            </div>
            <a href="index.html" class="text-sm hover:text-[#0096e0]">← <span data-i18n="nav_home">Home</span></a>
        </div>
    </nav>

    <main class="pt-28 pb-20 px-4 max-w-2xl mx-auto">
        <form id="reqForm" class="space-y-4 bg-gray-50 border border-gray-200 p-6 rounded-2xl">
            <h1 class="text-2xl font-bold mb-1" data-i18n="req_heading">Richiedi un preventivo</h1>
            <p class="text-gray-500 text-sm mb-4">
                <?= e($cfg['name']) ?> — <span data-i18n="req_intro_tail">ti invieremo un preventivo personalizzato.</span>
            </p>

            <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
            <input type="text" name="company" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true" />

            <div>
                <label class="lbl" data-i18n="req_name">Nome e cognome *</label>
                <input class="field" name="contact_name" required />
                <div class="err" data-err="contact_name"></div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="lbl" data-i18n="req_email">Email *</label>
                    <input class="field" type="email" name="email" required />
                    <div class="err" data-err="email"></div>
                </div>
                <div>
                    <label class="lbl" data-i18n="req_phone">Telefono</label>
                    <input class="field" name="phone" />
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="lbl" data-i18n="req_country">Paese *</label>
                    <select class="field" id="country" name="country" required>
                        <option value="" disabled selected>Seleziona il paese…</option>
                    </select>
                    <div class="err" data-err="country"></div>
                </div>
                <div>
                    <label class="lbl" data-i18n="req_quantity">Quantità</label>
                    <input class="field" type="number" name="quantity" min="1" value="1" />
                </div>
            </div>
            <div>
                <label class="lbl" data-i18n="req_notes">Note (esigenze, tempistiche, domande)</label>
                <textarea class="field" name="notes" rows="3"></textarea>
            </div>

            <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 space-y-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="want_call" value="1" id="wantCall" />
                    <span data-i18n="req_call">Vorrei essere ricontattato con una call</span>
                </label>
                <div id="availBox" class="hidden">
                    <label class="lbl" data-i18n="req_avail">Quando sei reperibile?</label>
                    <input class="field" name="availability" data-i18n-placeholder="req_avail_ph" placeholder="es. lun–ven 9–13, oppure un orario preciso" />
                </div>
            </div>

            <label class="flex items-start gap-2 text-sm text-gray-600">
                <input type="checkbox" name="consent" value="1" class="mt-1" required />
                <span data-i18n="privacy_consent">Acconsento al trattamento dei dati secondo la
                    <a href="privacy.html" class="text-[#0096e0] underline">Privacy Policy</a>.</span>
            </label>
            <div class="err" data-err="consent"></div>

            <button type="submit" data-i18n="req_submit" class="w-full bg-[#f59000] text-white px-6 py-3 rounded-full font-medium hover:bg-[#e08400] transition">
                Invia richiesta
            </button>
        </form>

        <div id="done" class="hidden bg-gray-50 border border-gray-200 p-8 rounded-2xl text-center">
            <h1 class="text-2xl font-bold mb-3" data-i18n="req_done_title">Grazie! ✅</h1>
            <p class="text-gray-600" data-i18n="req_done_msg">Abbiamo ricevuto la tua richiesta. Ti invieremo al più presto un preventivo personalizzato.</p>
            <p id="callNote" class="text-gray-600 mt-2 hidden"></p>
            <a href="index.html" data-i18n="thankyou_back" class="inline-block mt-6 px-6 py-3 bg-[#f59000] text-white rounded-full font-medium">Torna alla Home</a>
        </div>
    </main>

    <!-- Menu paesi + traduzioni (countries.js PRIMA di lang.js) -->
    <script src="/assets/js/countries.js?v=39"></script>
    <script src="/assets/js/lang.js?v=39"></script>
    <script>
        const $ = (s) => document.querySelector(s);

        // Messaggi dinamici (inseriti da JS) nella lingua salvata da lang.js
        const REQ_MSG = {
            it: { call: 'Ti ricontatteremo anche per la call.', csrf: 'La pagina era aperta da troppo tempo. Ricarica (Cmd+R) e riprova.', err: 'Errore nell\'invio. Riprova.' },
            en: { call: 'We’ll also get back to you for the call.', csrf: 'The page was open too long. Reload (Cmd+R) and try again.', err: 'Submission error. Please try again.' },
            fr: { call: 'Nous vous recontacterons également pour l’appel.', csrf: 'La page est restée ouverte trop longtemps. Rechargez (Cmd+R) et réessayez.', err: 'Erreur lors de l’envoi. Veuillez réessayer.' },
        };
        const REQ_ERR = {
            it: { contact_name: 'Campo obbligatorio', email: 'Email non valida', country: 'Campo obbligatorio', consent: 'Consenso obbligatorio' },
            en: { contact_name: 'Required field', email: 'Invalid email', country: 'Required field', consent: 'Consent required' },
            fr: { contact_name: 'Champ obligatoire', email: 'E-mail invalide', country: 'Champ obligatoire', consent: 'Consentement requis' },
        };
        const reqLang = () => (REQ_MSG[localStorage.getItem('lang')] ? localStorage.getItem('lang') : 'it');

        $('#wantCall').addEventListener('change', () => $('#availBox').classList.toggle('hidden', !$('#wantCall').checked));

        $('#reqForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.target.querySelectorAll('[data-err]').forEach(el => el.textContent = '');
            const res = await fetch('/api/submit_lead.php', { method: 'POST', body: new FormData(e.target) });
            let data = {}; try { data = await res.json(); } catch (err) {}
            const L = reqLang();
            if (data.ok) {
                if ($('#wantCall').checked) { $('#callNote').textContent = REQ_MSG[L].call; $('#callNote').classList.remove('hidden'); }
                $('#reqForm').classList.add('hidden');
                $('#done').classList.remove('hidden');
                window.scrollTo(0, 0);
            } else if (data.errors) {
                for (const k in data.errors) {
                    const el = e.target.querySelector(`[data-err="${k}"]`);
                    if (el) el.textContent = (REQ_ERR[L] && REQ_ERR[L][k]) || data.errors[k];
                }
            } else if (data.error === 'csrf') {
                alert(REQ_MSG[L].csrf);
            } else {
                alert(REQ_MSG[L].err);
            }
        });
    </script>
</body>
</html>
