# Wazlley — Takeoff.pro Volleyball Machine

Sito vetrina + mini-CRM per la macchina spara-palloni **Takeoff.pro Volleyball Machine**.
Sito live: <https://www.takeoff.pro> — CRM: <https://www.takeoff.pro/admin/login.php>

Rispondi in italiano.

## Stack

- Front-end statico: HTML + **Tailwind via CDN** + JavaScript vanilla (nessun build step, nessun npm).
- Back-end: **PHP + MySQL (PDO)** su hosting condiviso easyname.
- Font: stack di sistema **SF Pro** (`-apple-system, BlinkMacSystemFont, "SF Pro Display"`) con Inter come fallback.
- Look voluto: **stile pagina prodotto Apple** — molto bianco, titoli grandi e tight, bottoni a pillola, sezioni alternate.

## Palette

| Uso | Colore |
|---|---|
| Accento / link | `#0096e0` (cyan) |
| CTA / bottoni | `#f59000` (arancione, hover `#e08400`) |
| Testo | `#1d1d1f` — testo secondario `#6e6e73` |
| Sfondo | `#ffffff` |

Le icone social usano i colori brand: Instagram `#E4405F`, Facebook `#1877F2`, LinkedIn `#0A66C2`.

## Struttura

```
index.html          home (hero, coach, video, reel IG, galleria, specifiche)
contact.html  tutorials.html  privacy.html  thankyou.html
richiesta.php       form richiesta preventivo (crea il lead)
preventivo.php      pagina preventivo per il cliente (via token)
dati.php            dati di fatturazione + conferma ordine (via token)
admin/              mini-CRM (login, dashboard, dettaglio trattativa, nuova trattativa)
api/                submit_lead.php, accept_quote.php
inc/                helpers, db, auth, mailer, product (config prodotto)
config/             credenziali — NON in git (vedi sotto)
assets/js/          coaches.js, carousel.js, reels.js, sections.js, loadSections.js, specs.js, lang.js
assets/img/         product/ (render 3D), coaches/ (foto allenatori)
assets/video/       takeoff_demo.mp4
deploy/             script di pubblicazione
```

### I dati stanno nei file JS, non nell'HTML

Per i contenuti ricorrenti si modifica **solo il file JS**, mai l'HTML:

- **`assets/js/coaches.js`** — allenatori del carosello. Un oggetto per coach: `{ name, img, quote: {it, en} }`.
  L'ordine dell'array = ordine nel carosello. Per sospenderne uno: `active: false` (resta nel file ma non appare).
  Le foto sono `assets/img/coaches/coach_NN.webp`.
- **`assets/js/reels.js`** — post Instagram mostrati in home. Basta incollare l'URL: `{ url: "https://www.instagram.com/p/XXXX/" }`.
  Se l'array è vuoto la sezione si nasconde da sola. Gli embed si caricano solo quando l'utente scrolla lì (IntersectionObserver).
- **`assets/js/specs.js`** — tabella caratteristiche tecniche (bilingue).
- **`assets/js/sections.js`** — sezioni feature con le immagini prodotto.
- **`assets/js/lang.js`** — tutte le traduzioni IT/EN. È **l'unico** gestore del cambio lingua: chiama
  `window.renderSections`, `window.renderCoaches`, `window.renderSpecs`. Non aggiungere altri handler di lingua.

Le stringhe statiche nell'HTML si traducono con `data-i18n="chiave"` (e `data-i18n-placeholder` per i form),
dove `chiave` esiste in `lang.js` sia in `it` che in `en`.

### Cache busting

Gli script sono inclusi come `/assets/js/xxx.js?v=26`. **Se modifichi un file JS, incrementa `v` in tutte le pagine HTML** che lo includono, altrimenti i browser servono la versione vecchia.

Il CRM ha un suo numero di versione: `APP_VERSION` in `inc/helpers.php` (mostrato nell'header dell'area admin) — va bumpato a ogni modifica del CRM.

## Flusso commerciale (CRM)

1. Il cliente compila `richiesta.php` → `api/submit_lead.php` crea il **deal** e invia la notifica.
2. Nel CRM (`admin/deal.php`) si imposta il prezzo e si invia il preventivo → il cliente riceve un link con token.
3. Il cliente apre `preventivo.php?token=…`, poi `dati.php?token=…` per i dati di fatturazione.
4. **Compilare i dati di fatturazione = confermare l'ordine** (imposta `accepted_at` e stato `ordine_confermato`).
   La P.IVA UE viene verificata automaticamente via VIES.

Le email di notifica vanno agli indirizzi in `inc/product.php` → `notify_to` (attualmente info@ + margherita@, separati da virgola).
`inc/mailer.php` espone `send_mail($to, $subject, $body, $replyTo)`: usa SMTP autenticato se esiste `config/smtp.php`, altrimenti ripiega su `mail()`. Supporta più destinatari separati da virgola.

## Configurazione (NON in git)

Questi file contengono credenziali, sono in `.gitignore` e **non vanno mai committati**:

- `config/db.php` — credenziali MySQL di produzione (presente sul server).
- `config/db.local.php` — credenziali MySQL locali per lo sviluppo.
- `config/smtp.php` — credenziali SMTP (opzionale; senza, si usa `mail()`).
- `deploy/.env` e `deploy/.ssh_pass` — accessi per la pubblicazione.

Ci sono i corrispondenti `.example` come modello. Chiedi le credenziali al titolare del progetto.

> ⚠️ **`config/db.local.php` non deve MAI finire sul server.** `inc/db.php` gli dà la precedenza su `config/db.php`:
> se esiste in produzione, il sito prova a connettersi al database locale e **tutto il CRM va in HTTP 500**.
> Gli script di deploy lo escludono già — non rimuovere quell'esclusione.

## Sviluppo locale

Anteprima (già configurata in `.claude/launch.json`):

```bash
php -S localhost:8000 -t .
```

Le pagine PHP hanno bisogno di un database: copia `config/db.example.php` in `config/db.local.php` con i dati del tuo MySQL locale. Le pagine HTML funzionano anche senza.

## Pubblicazione

**Solo via SSH/rsync.** L'FTP è inaffidabile su questo hosting (il canale dati si blocca e satura le connessioni): non usarlo.

```bash
./deploy/ssh_sync.sh preview   # dry-run: mostra cosa verrebbe caricato
./deploy/ssh_sync.sh deploy    # carica solo i file modificati (chiede CONFERMO)
```

Server: `e36797-ssh.services.easyname.eu`, porta **11001**, utente `e36797`, webroot **`/data/web/e36797/html/wazlley`**.

Note tecniche sul deploy:

- macOS ha `openrsync`, che **non supporta `--chmod`**. Si usa `rsync -rltz` (senza `-p`): se si preservassero i permessi locali, la cartella `assets/` (700 in locale) diventerebbe illeggibile per il web server e le immagini darebbero **403**.
- I permessi corretti sul server sono **755 per le cartelle, 644 per i file**. Se compaiono 403 su file esistenti, è quasi sempre questo:
  ```bash
  ssh -p 11001 e36797@e36797-ssh.services.easyname.eu \
    "cd /data/web/e36797/html/wazlley && find . -type d -exec chmod 755 {} + && find . -type f -exec chmod 644 {} +"
  ```
- Per pubblicare singoli file con rsync usa **`-R`** (path relativi), altrimenti finiscono nella root del sito invece che nella loro cartella.

Dopo il deploy verifica sempre il risultato reale, es.:

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://www.takeoff.pro/admin/login.php
```

## Convenzioni

- Commenti e testi UI in italiano; il codice segue lo stile già presente nei file.
- Niente framework, niente build: modifica direttamente i file.
- Le immagini vanno in **WebP** (`cwebp -q 85 input.png -o output.webp`), con nomi coerenti (`coach_NN.webp`, `takeoff_*.webp`).
- Ogni modifica visibile va verificata in anteprima prima di pubblicare.
- Non generare zip di deploy: si pubblica con `ssh_sync.sh`.

## Da sapere

- Gli embed Instagram caricano script e cookie di Meta: andrebbe aggiunta una nota nella privacy policy (**ancora da fare**).
- Il video demo compare sia nell'hero (autoplay muto in loop) sia nella sezione "Guarda in azione" (con controlli): **ridondanza nota**, da decidere se tenere entrambi.
- Gli allenatori **Mick Haley** e **Claudio Busato** hanno ancora frasi di esempio, non reali: vanno sostituite quando arrivano quelle vere.
