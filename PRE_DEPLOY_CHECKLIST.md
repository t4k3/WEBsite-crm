# ✅ PRE-DEPLOY CHECKLIST - Wazlley Website

Usa questa checklist prima di mettere il sito in produzione.

---

## 🔴 CRITICI (BLOCCA DEPLOY SE NON COMPLETATI)

### 1. Video Tutorial
- [ ] Aprire `tutorials.html`
- [ ] Sostituire `VIDEO_ID_1` con ID YouTube reale (riga ~95)
- [ ] Sostituire `VIDEO_ID_2` con ID YouTube reale (riga ~105)
- [ ] Sostituire `VIDEO_ID_3` con ID YouTube reale (riga ~115)
- [ ] Verificare embed funzionanti: https://www.youtube.com/embed/TUO_ID

**Come ottenere ID**:
```
URL: https://www.youtube.com/watch?v=dQw4w9WgXcQ
ID:  dQw4w9WgXcQ (parte dopo v=)
```

### 2. File Immagini Essenziali
- [ ] Verificare esistenza: `/assets/img/logo.png`
- [ ] Verificare esistenza: `/assets/img/favicon.ico`
- [ ] Verificare esistenza: `/assets/img/hero_ballgun.webp`
- [ ] Verificare dimensioni immagini coaches (< 200KB ciascuna)
- [ ] Testare fallback: Rinominare temporaneamente un'immagine e verificare che appaia placeholder.svg

### 3. Configurazione Email
- [ ] Aprire `sendmail_secure.php`
- [ ] Verificare email destinazione (riga 13): `$to = 'info@takeoff.pro';`
- [ ] Cambiare se necessario con email reale
- [ ] Testare su server produzione: `check_wazlley.php?mailtest=1`

### 4. Permessi File (Server Linux)
```bash
chmod 755 *.php
chmod 644 *.html
chmod -R 755 assets/
chmod 644 .htaccess
```
- [ ] Eseguito comando
- [ ] Verificato nessun errore 403

### 5. Server Requirements
- [ ] Apache 2.4+ con mod_rewrite attivo
- [ ] PHP 7.4+ installato
- [ ] Funzione `mail()` abilitata (o SMTP configurato)
- [ ] HTTPS/SSL attivo (Let's Encrypt consigliato)

---

## 🟡 IMPORTANTI (CONSIGLIATI)

### 6. Test Funzionalità
- [ ] Homepage carica senza errori
- [ ] Cambio lingua IT → EN funziona
- [ ] Carousel coaches: frecce prev/next
- [ ] Animazioni scroll attive (GSAP)
- [ ] Form contatti: compila e invia test
- [ ] Privacy page: navbar e contenuto visualizzati
- [ ] Tutorial page: video embed visibili
- [ ] Thank you page: redirect dopo form

### 7. Test Browser
- [ ] Chrome/Edge (desktop)
- [ ] Firefox (desktop)
- [ ] Safari (macOS)
- [ ] Chrome (Android mobile)
- [ ] Safari (iOS mobile)
- [ ] Tablet (iPad/Android)

### 8. Performance
- [ ] Test PageSpeed Insights: https://pagespeed.web.dev/
  - Target: > 80 mobile, > 90 desktop
- [ ] Test GTmetrix: https://gtmetrix.com/
- [ ] Immagini WebP funzionanti
- [ ] Lazy loading attivo
- [ ] Cache headers verificati (F12 → Network)

### 9. SEO Base
- [ ] Title tag presente in tutte le pagine
- [ ] Meta description presente (index.html)
- [ ] Favicon visibile in tab browser
- [ ] Robots.txt presente (opzionale)
- [ ] Sitemap.xml creato (opzionale)

### 10. Console Browser
- [ ] Aprire F12 → Console
- [ ] Verificare: 0 errori JavaScript
- [ ] Verificare: 0 errori 404 (Network tab)
- [ ] Verificare: 0 warning CORS

---

## 🟢 OPZIONALI (NICE TO HAVE)

### 11. Analytics
- [ ] Google Analytics configurato
- [ ] Google Tag Manager (opzionale)
- [ ] Hotjar o similare per heatmap (opzionale)

### 12. Sicurezza Extra
- [ ] HTTPS forzato (redirect HTTP → HTTPS)
- [ ] Headers di sicurezza in `.htaccess`:
  ```apache
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set X-XSS-Protection "1; mode=block"
  ```
- [ ] reCAPTCHA v3 implementato (opzionale)
- [ ] CSRF token nel form (opzionale)

### 13. Backup
- [ ] Backup completo files pre-deploy
  ```bash
  tar -czf wazlley_backup_$(date +%Y%m%d).tar.gz wazlley/
  ```
- [ ] Backup salvato in location sicura
- [ ] Procedura di rollback testata

### 14. Monitoring
- [ ] UptimeRobot configurato: https://uptimerobot.com/
- [ ] Google Search Console attivo
- [ ] Alert email configurati

### 15. DNS & Dominio
- [ ] Record A punta a IP server
- [ ] Record CNAME www → dominio principale
- [ ] Propagazione DNS completata (24-48h)
- [ ] SSL certificate valido (https://)

---

## 🧪 TEST FINALI

### Test Manuale 5 Minuti
1. [ ] Vai su homepage
2. [ ] Clicca logo (reload homepage)
3. [ ] Cambia lingua IT/EN (3 volte)
4. [ ] Scroll verso basso (animazioni attive?)
5. [ ] Carousel: premi frecce 5 volte
6. [ ] Vai su Contatti
7. [ ] Compila form (usa email reale)
8. [ ] Invia → ricevi email?
9. [ ] Thank you page appare?
10. [ ] Torna Home da thank you

### Test Script Automatico
```bash
# Esegui su server
curl https://tuodominio.it/check_wazlley.php

# Output atteso:
# ✅ index.html found
# ✅ contact.html found
# ✅ tutorials.html found
# ✅ assets/js/lang.js OK
```

### Test Email
```bash
curl https://tuodominio.it/check_wazlley.php?mailtest=1

# Verifica ricezione email a: info@takeoff.pro
```

---

## 📋 CHECKLIST VELOCE (1 MINUTO)

Prima di premere DEPLOY:

- [ ] Video ID sostituiti?
- [ ] Email corretta in sendmail_secure.php?
- [ ] Test form inviato con successo?
- [ ] 0 errori in console browser (F12)?
- [ ] HTTPS attivo?

**SE TUTTI ✅ → DEPLOY APPROVED** 🚀

---

## 🆘 ROLLBACK VELOCE

In caso di problemi dopo deploy:

```bash
# 1. Ripristina backup
cd /var/www/html
sudo rm -rf *
sudo tar -xzf /path/to/backup/wazlley_backup_YYYYMMDD.tar.gz

# 2. Riavvia Apache
sudo systemctl restart apache2

# 3. Verifica
curl -I https://tuodominio.it
```

---

## 📞 CONTATTI EMERGENZA

- **Email**: info@takeoff.pro
- **Server Provider**: [INSERIRE CONTATTI]
- **DNS Provider**: [INSERIRE CONTATTI]
- **SSL Provider**: Let's Encrypt / [ALTRO]

---

**Ultima Revisione**: Gennaio 2025  
**Versione Checklist**: 1.0  
**Tempo Stimato**: 15-30 minuti