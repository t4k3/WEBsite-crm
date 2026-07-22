<?php
// MODELLO. Copia questo file in:
//   config/db.php        → credenziali di PRODUZIONE (già presente sul server)
//   config/db.local.php  → credenziali del tuo MySQL LOCALE (solo sviluppo)
//
// ⚠️ config/db.local.php NON deve MAI essere caricato sul server:
//    inc/db.php gli dà la precedenza e il CRM andrebbe in HTTP 500.
// Entrambi sono in .gitignore: non committarli.
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'name'    => 'nome_database',
    'user'    => 'utente',
    'pass'    => 'password',
    'charset' => 'utf8mb4',
];
