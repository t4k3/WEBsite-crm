<?php
// Configurazione prodotto (fonte unica per form + backend).
return [
    'name'       => 'Takeoff.pro Volleyball Machine',
    'currency'   => 'EUR',
    'variants'   => ['Giallo'],
    'notify_to'  => 'info@takeoff.pro, margherita@takeoff.pro',

    // --- Magazzino (admin/inventory.php) ---
    // Specifiche per modello: velocità/altezza/batteria dipendono dal modello,
    // non dal singolo pezzo. Aggiungi qui un nuovo modello quando serve.
    'models' => [
        'V12' => ['speed' => '115 km/h', 'height' => '220–280 cm', 'battery' => 'Ioni di sodio'],
        'V11' => ['speed' => '115 km/h', 'height' => '220–280 cm', 'battery' => 'Ioni di sodio'],
        'V10' => ['speed' => '95 km/h',  'height' => '260 cm',     'battery' => 'Litio'],
        'V9'  => ['speed' => '90 km/h',  'height' => '250 cm',     'battery' => 'Litio'],
    ],

    // Colori selezionabili in magazzino, con il pallino colorato mostrato nel CRM.
    'inventory_colors' => [
        'Rossa'   => '#c0392b',
        'Nera'    => '#1d1d1f',
        'Rosa'    => '#e4405f',
        'Azzurra' => '#4aa3e0',
        'Blu'     => '#1877f2',
        'Gialla'  => '#f5c518',
    ],
];
