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
- [x] **Immagini sezioni**: ora usano i **render 3D ufficiali** (`assets/img/product/takeoff_*.webp`). I vecchi cutout `ballgun_*`/`effects_combo`/`detail_rollers_aluminum` sono stati rimossi. Render incorniciati in pannello `bg-[#2e2e2e]` arrotondato (loadSections.js).
- [x] **Galleria prodotto**: 6 render 3D (`takeoff_full_front/_full_angle/_play_mode/_controls/_head_front/_head_rollers`) al posto dei placeholder.
- [x] **Sorgenti render**: PNG originali archiviati con nomi coerenti in `images/renders/` (gitignorato).
- [ ] **Immagini coach mancanti**: `coach_08/09/10/13.webp` (in attesa anche delle frasi autorizzate).
- [ ] **Video tutorial**: sostituire `VIDEO_ID_1/2/3` con gli ID YouTube reali in `tutorials.html`.
- [x] **Bug PHP**: rimossa la riga vuota prima di `<?php` in `sendmail_secure.php`.
- [x] **Rimuovere `check_wazlley.php`**: eliminato dal repo (`git rm`) e da disco. ⚠️ Verificare ancora che non sia rimasto online sul server live.

### 🟠 Priorità media — bug e pulizia
- [x] **Bug lingua** in `assets/js/loadSections.js`: ternario parentesizzato.
- [x] **Traduzioni duplicate**: rimossi i JSON morti (`assets/lang/*.json`). Fonte unica = `assets/js/lang.js`.
- [x] **Navbar duplicata**: rimosso il partial inutilizzato `partials/navbar.html`. (La navbar resta duplicata nelle pagine `.html`: scelta accettabile per sito statico senza include server-side.)
- [x] **Handler lingua tripli** → unificati: `lang.js` è l'unico gestore del click `.lang-switch` (delega su `body` + `window.renderSections`/`window.renderCoaches`). Rimossi gli handler duplicati in `loadSections.js` e `carousel.js`.
- [x] **Asset orfani**: rimossi `Float.webp`, `spin.webp`, `reverse.webp`.
- [x] **GSAP/parallax**: rimossa la regola morta `[data-parallax]`/`--scrollY` (mai usata). GSAP **resta**: è usato da `loadSections.js` per le animazioni ScrollTrigger delle sezioni.

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
