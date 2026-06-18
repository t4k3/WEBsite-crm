# 🏐 Wazlley - Ballgun Pro 2025

Sito web ufficiale per **Wazlley Ballgun Pro 2025**, la macchina spara-palloni professionale per l'allenamento nel volley, sviluppata da **Takeoff.pro**.

![Wazlley](assets/img/logo.png)

---

## 📋 Indice

- [Caratteristiche](#-caratteristiche)
- [Tecnologie](#-tecnologie)
- [Struttura del Progetto](#-struttura-del-progetto)
- [Setup e Installazione](#-setup-e-installazione)
- [Configurazione](#-configurazione)
- [Deploy](#-deploy)
- [Manutenzione](#-manutenzione)
- [TODO](#-todo)
- [Licenza](#-licenza)

---

## ✨ Caratteristiche

- ✅ **Multilingua**: Italiano / English (US) con switch dinamico
- ✅ **Responsive Design**: Mobile-first con Tailwind CSS
- ✅ **Animazioni Fluide**: GSAP + ScrollTrigger per effetti scroll
- ✅ **Carousel 3D**: Testimonianze allenatori interattive
- ✅ **Form Sicuro**: Protezione honeypot, sanitizzazione input, validazione
- ✅ **Ottimizzazioni**: Lazy loading, cache, compressione gzip
- ✅ **SEO Ready**: Meta tag, semantic HTML, performance ottimizzate
- ✅ **Accessibilità**: ARIA labels, focus visibile, keyboard navigation

---

## 🛠 Tecnologie

### Frontend
- **HTML5**: Markup semantico
- **CSS**: Tailwind CSS 3.x (via CDN)
- **JavaScript**: Vanilla JS (ES6+)
- **Font**: Fauna (custom WOFF2)
- **Animazioni**: GSAP 3.12 + ScrollTrigger

### Backend
- **PHP 7.4+**: Gestione form contatti
- **Apache**: Server con mod_rewrite, mod_deflate, mod_expires

### Assets
- **Immagini**: WebP (fallback SVG)
- **Video**: Embed YouTube

---

## 📁 Struttura del Progetto

```
wazlley/
├── index.html              # Homepage
├── contact.html            # Form preventivi
├── tutorials.html          # Video tutorial
├── privacy.html            # Privacy policy GDPR
├── thankyou.html           # Conferma invio form
├── sendmail_secure.php     # Backend form (sicuro)
├── check_wazlley.php       # Script diagnostico
├── .htaccess               # Configurazione Apache
├── README.md               # Questo file
│
├── assets/
│   ├── css/
│   │   └── style.css       # (non usato - tutto inline/Tailwind)
│   │
│   ├── js/
│   │   ├── lang.js         # Sistema multilingua
│   │   ├── sections.js     # Dati sezioni prodotto
│   │   ├── loadSections.js # Render dinamico sezioni
│   │   ├── coaches.js      # Dati testimonianze
│   │   └── carousel.js     # Carosello coaches
│   │
│   ├── img/
│   │   ├── logo.png        # Logo principale
│   │   ├── favicon.ico     # Favicon
│   │   ├── placeholder.svg # Fallback immagini
│   │   ├── hero_ballgun.webp
│   │   ├── coaches/        # Foto allenatori
│   │   └── ...             # Altre immagini prodotto
│   │
│   └── fonts/
│       └── fauna-thin.woff2
│
└── partials/
    └── navbar.html         # (non usato - navbar inline)
```

---

## 🚀 Setup e Installazione

### Requisiti Minimi
- **Web Server**: Apache 2.4+ con `mod_rewrite`, `mod_deflate`, `mod_expires`
- **PHP**: 7.4+ con funzione `mail()` abilitata
- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+

### Installazione Locale

1. **Clona/Download il progetto**:
   ```bash
   cd /path/to/webroot
   git clone <repo-url> wazlley
   cd wazlley
   ```

2. **Verifica permessi**:
   ```bash
   chmod 755 *.php
   chmod 644 *.html
   ```

3. **Configura virtual host** (Apache):
   ```apache
   <VirtualHost *:80>
       ServerName wazlley.local
       DocumentRoot /path/to/wazlley
       
       <Directory /path/to/wazlley>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

4. **Aggiungi a `/etc/hosts`**:
   ```
   127.0.0.1 wazlley.local
   ```

5. **Riavvia Apache**:
   ```bash
   sudo systemctl restart apache2
   # oppure
   sudo apachectl restart
   ```

6. **Apri nel browser**:
   ```
   http://wazlley.local
   ```

---

## ⚙️ Configurazione

### Email (Form Contatti)

Modifica `sendmail_secure.php`:

```php
// Riga 13
$to = 'info@takeoff.pro'; // 👈 Cambia con la tua email
```

### Video Tutorial

Modifica `tutorials.html` (righe 95-123):

```html
<!-- Sostituisci VIDEO_ID_1, VIDEO_ID_2, VIDEO_ID_3 -->
<iframe src="https://www.youtube.com/embed/VERO_ID_VIDEO"></iframe>
```

**Come ottenere l'ID YouTube**:
- URL video: `https://www.youtube.com/watch?v=dQw4w9WgXcQ`
- ID: `dQw4w9WgXcQ` (parte dopo `v=`)

### Traduzioni

Modifica `assets/js/lang.js` per aggiungere/modificare testi:

```javascript
const translations = {
  it: {
    nav_home: "Home",
    title: "Wazlley – Ballgun Pro 2025",
    // ... altri testi
  },
  en: {
    nav_home: "Home",
    title: "Wazlley – Ballgun Pro 2025",
    // ... traduzioni inglesi
  }
};
```

### Sezioni Prodotto

Modifica `assets/js/sections.js` per cambiare feature:

```javascript
const wazlleySections = [
  {
    side: "left", // o "right"
    img: "/assets/img/tua_immagine.webp",
    it: {
      title: "Titolo italiano",
      text: "Descrizione italiana"
    },
    en: {
      title: "English title",
      text: "English description"
    }
  }
];
```

### Testimonianze Coaches

Modifica `assets/js/coaches.js`:

```javascript
const coaches = {
  it: [
    {
      name: "Nome Allenatore",
      quote: ""Citazione in italiano"",
      img: "/assets/img/coaches/foto.webp"
    }
  ],
  en: [ /* traduzioni */ ]
};
```

---

## 🌐 Deploy

### Hosting Condiviso (cPanel)

1. **Upload via FTP/SFTP**:
   - Carica tutti i file nella cartella `public_html/` o `www/`

2. **Verifica `.htaccess`**:
   - Deve essere presente e leggibile

3. **Configura email**:
   - Verifica che PHP `mail()` sia abilitato
   - Potrebbe essere necessario configurare SMTP

4. **Test**:
   ```
   https://tuodominio.it/check_wazlley.php
   ```

### VPS/Dedicato (Linux)

1. **Installa stack**:
   ```bash
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php
   sudo a2enmod rewrite deflate expires headers
   ```

2. **Copia file**:
   ```bash
   sudo cp -r wazlley/* /var/www/html/
   sudo chown -R www-data:www-data /var/www/html/
   ```

3. **Configura SSL** (Let's Encrypt):
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d tuodominio.it -d www.tuodominio.it
   ```

4. **Test email**:
   ```bash
   php -r "mail('test@example.com', 'Test', 'Body');"
   ```

### CDN e Performance

- **Cloudflare**: Attiva per cache + CDN gratuito
- **Immagini**: Già in WebP (compressione ottimale)
- **Cache**: `.htaccess` già configurato con expire headers

---

## 🔧 Manutenzione

### Test Funzionalità

**Script diagnostico**:
```bash
curl https://tuodominio.it/check_wazlley.php
```

**Test email**:
```bash
curl https://tuodominio.it/check_wazlley.php?mailtest=1
```

### Backup Consigliato

```bash
# Backup completo
tar -czf wazlley_backup_$(date +%Y%m%d).tar.gz wazlley/

# Solo database (se usato in futuro)
# mysqldump -u user -p database > backup.sql
```

### Aggiornamenti

1. **Tailwind CSS**: Aggiorna CDN link in tutti gli HTML
2. **GSAP**: Aggiorna versione in `index.html`
3. **PHP**: Testa compatibilità con `php -v`

### Monitoraggio Errori

**Apache logs**:
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

**PHP errors** (aggiungi in `sendmail_secure.php` in dev):
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📝 TODO

### 🔴 CRITICO (Da fare subito)
- [ ] Sostituire `VIDEO_ID_1/2/3` con ID YouTube reali in `tutorials.html`
- [ ] Verificare presenza file `logo.png` e `favicon.ico`
- [ ] Testare invio email su server produzione

### 🟡 MEDIO (Entro 1 settimana)
- [ ] Implementare CSRF token nel form contatti
- [ ] Aggiungere Google Analytics / Matomo
- [ ] Ottimizzare immagini coaches (compressione)
- [ ] Testare su Safari iOS e Android Chrome

### 🟢 BASSO (Nice to have)
- [ ] Aggiungere reCAPTCHA v3 al form
- [ ] Implementare rate limiting PHP
- [ ] Creare sitemap.xml
- [ ] Aggiungere Open Graph meta tags
- [ ] Implementare Service Worker per PWA

---

## 🐛 Troubleshooting

### Form non invia email

1. Verifica PHP `mail()`:
   ```bash
   php -i | grep sendmail
   ```

2. Controlla logs:
   ```bash
   tail -f /var/log/mail.log
   ```

3. Alternativa: Usa PHPMailer con SMTP

### Immagini non caricano

1. Verifica permessi:
   ```bash
   chmod 755 assets/img/
   chmod 644 assets/img/*.webp
   ```

2. Controlla `.htaccess` CORS headers

### Animazioni GSAP non funzionano

1. Verifica connessione internet (CDN)
2. Controlla console browser (F12)
3. Testa su browser moderno

### Cambio lingua non funziona

1. Verifica localStorage abilitato (browser)
2. Controlla console per errori JS
3. Controlla ordine caricamento script

---

## 📄 Licenza

© 2025 **Takeoff.pro srl** - Tutti i diritti riservati.

Questo codice è proprietario e non può essere redistribuito senza autorizzazione scritta.

---

## 📞 Contatti

- **Email**: [info@takeoff.pro](mailto:info@takeoff.pro)
- **Website**: [www.takeoff.pro](https://www.takeoff.pro)
- **Prodotto**: Wazlley Ballgun Pro 2025

---

## 🙏 Credits

- **Design & Development**: Takeoff.pro Team
- **Font**: Fauna by [Designer]
- **Framework CSS**: Tailwind CSS
- **Animazioni**: GSAP (GreenSock)
- **Icons**: Emoji Unicode

---

**Versione**: 1.0.0  
**Ultimo aggiornamento**: Gennaio 2025  
**Status**: ✅ Production Ready (con TODO da completare)