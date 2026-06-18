# Wazlley — Piano di lavoro

Sito statico pubblicato su **wazlley.takeoff.pro** (deploy via FTP).
Fonte di verità: **il locale**. Vedi `deploy/` per il sync.

---

## Workflow di deploy

1. **Una tantum**: `cp deploy/.env.example deploy/.env` e compila host/utente/password.
2. **Prima volta**: `./deploy/sync.sh backup` → scarica il live in `_live_backup/` (rete di sicurezza).
3. Lavoro in locale, committo su git.
4. **Prima di pubblicare**: `./deploy/sync.sh preview` → controllo cosa cambierebbe (dry-run, non tocca il server).
5. **Pubblico**: `./deploy/sync.sh deploy` → carica solo i file modificati (chiede conferma).

> `check_wazlley.php` e i file `*.md` sono esclusi dall'upload (vedi `EXCLUDES` in sync.sh).

---

## Backlog

### 🔴 Priorità alta — da fare prima del prossimo deploy
- [x] **Immagini sezioni**: tutte e 5 ora sono cutout trasparenti di foto reali (pipeline `tools/photo2webp.sh`). CSS aggiornato a `object-contain` + `drop-shadow`.
- [ ] **Foto batteria al sodio**: lo slot `ballgun_battery_pack.webp` mostra TEMPORANEAMENTE un cutout della macchina rosa (l'illustrazione AI è stata rimossa). Sostituire con una foto vera della batteria appena disponibile.
- [ ] **Dettaglio "rollers"**: `detail_rollers_aluminum.webp` è la macchina intera; volendo, ritagliare un vero primo piano della testa/rulli.
- [ ] **Immagini coach mancanti**: `coach_08/09/10/13.webp` (in attesa anche delle frasi autorizzate).
- [ ] **Video tutorial**: sostituire `VIDEO_ID_1/2/3` con gli ID YouTube reali in `tutorials.html`.
- [ ] **Bug PHP**: rimuovere la riga vuota prima di `<?php` in `sendmail_secure.php` (rischio "headers already sent").
- [ ] **Rimuovere `check_wazlley.php`** dal server live (espone path/host e con `?mailtest=1` consente invio mail a chiunque). Già escluso dall'upload; verificare che non sia già online.

### 🟠 Priorità media — bug e pulizia
- [ ] **Bug lingua** in `assets/js/loadSections.js` riga 7: parentesizzare il ternario (vedi note sotto).
- [ ] **Traduzioni duplicate**: decidere fonte unica tra `assets/js/lang.js` (usato) e `assets/lang/*.json` (morti, non caricati). Rimuovere i JSON o passare a fetch da JSON.
- [ ] **Navbar duplicata** in ogni pagina: valutare include di `partials/navbar.html` (oggi inutilizzato).
- [ ] **Handler lingua tripli** (lang.js + loadSections.js + carousel.js) → unificare.
- [ ] **Asset orfani**: `Float.webp`, `spin.webp`, `reverse.webp` esistono ma non sono usati (erano per la sezione effetti?).
- [ ] **GSAP/parallax**: `[data-parallax]` e `--scrollY` non sono usati da nessun elemento → rimuovere o implementare.

### 🔵 Priorità bassa — SEO / performance / accessibilità
- [ ] **Tailwind via CDN → build statico** prima del go-live definitivo (perf, no FOUC).
- [ ] **SEO**: aggiungere Open Graph / Twitter card, canonical, `hreflang` IT/EN, meta description su tutte le pagine, sitemap + robots.txt.
- [ ] **Accessibilità**: `title` sugli iframe tutorial; `aria-hidden` sulle card del carosello non centrali; rivedere il selettore lingua a bandiere.
- [ ] **Coerenza brand**: uniformare "Wazlley" vs "Takeoff.pro" tra title/alt logo/footer.

### ⏳ In attesa di contenuti
- [ ] Frasi testimonianze coach (ok dei coach) → poi sostituire i placeholder in `assets/js/coaches.js`.

---

## Note tecniche

**Bug ternario** (`loadSections.js:7`):
```js
// ❌ attuale: la precedenza di || rende la condizione sempre truthy se c'è un valore salvato
let currentLang = localStorage.getItem("lang") || navigator.language.startsWith("it") ? "it" : "en";
// ✅ corretto:
let currentLang = localStorage.getItem("lang") || (navigator.language.startsWith("it") ? "it" : "en");
```
