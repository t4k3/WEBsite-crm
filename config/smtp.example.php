<?php
// ============================================================
//  Configurazione invio email via SMTP.
//  COPIA questo file in  config/smtp.php  e compila utente/password.
//  (config/ è protetta da .htaccess e config/smtp.php non va in git/zip)
//
//  Scegli UNO dei provider qui sotto.
// ============================================================

// --- Opzione A: easyname (gratis, già tua) -----------------
return [
    'host'      => 'smtp.easyname.com',
    'port'      => 587,             // 587 = STARTTLS  ·  465 = SSL
    'user'      => '',              // utente SMTP easyname (a volte un codice tipo 12345mail1, non l'email)
    'pass'      => '',              // password della casella info@takeoff.pro
    'from'      => 'info@takeoff.pro',
    'from_name' => 'Takeoff.pro',
];

// --- Opzione B: Brevo (gratis ~300 mail/giorno, ottima consegna) ---
// return [
//     'host'      => 'smtp-relay.brevo.com',
//     'port'      => 587,
//     'user'      => '',           // la tua login Brevo (email account)
//     'pass'      => '',           // la "SMTP key" generata in Brevo
//     'from'      => 'info@takeoff.pro',   // mittente verificato in Brevo
//     'from_name' => 'Takeoff.pro',
// ];

// --- Opzione C: SendGrid (gratis ~100 mail/giorno) ----------
// return [
//     'host'      => 'smtp.sendgrid.net',
//     'port'      => 587,
//     'user'      => 'apikey',      // letterale "apikey"
//     'pass'      => '',           // la tua API key SendGrid
//     'from'      => 'info@takeoff.pro',
//     'from_name' => 'Takeoff.pro',
// ];
