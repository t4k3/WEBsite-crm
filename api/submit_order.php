<?php
// Step 2 — completa il lead con i dati di fatturazione/ordine.
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'method'], 405);
if (!csrf_check($_POST['csrf'] ?? null)) json_out(['ok' => false, 'error' => 'csrf'], 403);

$token = post('token');
if (!preg_match('/^[a-f0-9]{32}$/', $token)) json_out(['ok' => false, 'error' => 'token'], 422);

$ctype = post('customer_type');
if (!in_array($ctype, ['azienda', 'privato'], true)) $ctype = 'privato';

$errors = [];
if (post('bill_address') === '') $errors['bill_address'] = 'Campo obbligatorio';
if (post('bill_city') === '')    $errors['bill_city'] = 'Campo obbligatorio';
if (post('bill_country') === '') $errors['bill_country'] = 'Campo obbligatorio';
if ($ctype === 'azienda') {
    if (post('company_name') === '') $errors['company_name'] = 'Campo obbligatorio';
    if (post('vat_number') === '')   $errors['vat_number'] = 'Campo obbligatorio';
}
if ($errors) json_out(['ok' => false, 'errors' => $errors], 422);

$shipSame = post('ship_same') !== '' ? 1 : 0;

try {
    $st = db()->prepare(
        "UPDATE deals SET
            status = 'ordine_ricevuto',
            customer_type = :ctype, company_name = :company, vat_number = :vat, tax_code = :tax,
            sdi_code = :sdi, pec = :pec, eori = :eori,
            bill_address = :ba, bill_city = :bc, bill_zip = :bz, bill_province = :bp, bill_country = :bco,
            ship_same = :ssame,
            ship_address = :sa, ship_city = :sc, ship_zip = :sz, ship_province = :sp, ship_country = :sco
         WHERE token = :token AND status = 'nuovo'"
    );
    $st->execute([
        ':ctype' => $ctype,
        ':company' => post('company_name'),
        ':vat' => post('vat_number'),
        ':tax' => post('tax_code'),
        ':sdi' => post('sdi_code'),
        ':pec' => post('pec'),
        ':eori' => post('eori'),
        ':ba' => post('bill_address'),
        ':bc' => post('bill_city'),
        ':bz' => post('bill_zip'),
        ':bp' => post('bill_province'),
        ':bco' => post('bill_country'),
        ':ssame' => $shipSame,
        ':sa' => $shipSame ? post('bill_address') : post('ship_address'),
        ':sc' => $shipSame ? post('bill_city') : post('ship_city'),
        ':sz' => $shipSame ? post('bill_zip') : post('ship_zip'),
        ':sp' => $shipSame ? post('bill_province') : post('ship_province'),
        ':sco' => $shipSame ? post('bill_country') : post('ship_country'),
        ':token' => $token,
    ]);
} catch (Throwable $ex) {
    json_out(['ok' => false, 'error' => 'db'], 500);
}

if ($st->rowCount() === 0) {
    // token inesistente o ordine già inviato
    json_out(['ok' => false, 'error' => 'not_found'], 404);
}

$cfg = require __DIR__ . '/../inc/product.php';
@mail(
    $cfg['notify_to'],
    'Ordine completato — Wazlley',
    "Un cliente ha completato i dati d'ordine.\nToken: $token\nTipo: $ctype\nRagione sociale: "
        . post('company_name') . "\nP.IVA/VAT: " . post('vat_number') . "\n",
    "From: noreply@takeoff.pro\r\n"
);

json_out(['ok' => true]);
