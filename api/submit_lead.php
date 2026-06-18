<?php
// Step 1 — salva il lead (richiesta preventivo) e ritorna il token.
require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'method'], 405);
if (!csrf_check($_POST['csrf'] ?? null)) json_out(['ok' => false, 'error' => 'csrf'], 403);

// Honeypot anti-bot: campo "company" invisibile, se compilato fingo successo.
if (post('company') !== '') json_out(['ok' => true, 'token' => '']);

$name  = post('contact_name');
$email = post('email');
$qty   = (int) post('quantity', '1');
if ($qty < 1) $qty = 1;

$errors = [];
if ($name === '')            $errors['contact_name'] = 'Campo obbligatorio';
if (!valid_email($email))    $errors['email'] = 'Email non valida';
if (post('consent') === '')  $errors['consent'] = 'Consenso obbligatorio';
if ($errors) json_out(['ok' => false, 'errors' => $errors], 422);

$cfg   = require __DIR__ . '/../inc/product.php';
$token = gen_token();

try {
    $st = db()->prepare(
        'INSERT INTO deals (token, contact_name, email, phone, country, quantity, variant, notes, currency, consent, ip, user_agent)
         VALUES (:token,:name,:email,:phone,:country,:qty,:variant,:notes,:currency,1,:ip,:ua)'
    );
    $st->execute([
        ':token' => $token,
        ':name' => $name,
        ':email' => $email,
        ':phone' => post('phone'),
        ':country' => post('country'),
        ':qty' => $qty,
        ':variant' => post('variant'),
        ':notes' => post('notes'),
        ':currency' => $cfg['currency'],
        ':ip' => client_ip(),
        ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
} catch (Throwable $ex) {
    json_out(['ok' => false, 'error' => 'db'], 500);
}

@mail(
    $cfg['notify_to'],
    'Nuova richiesta preventivo — Wazlley',
    "Nome: $name\nEmail: $email\nTelefono: " . post('phone') . "\nPaese: " . post('country')
        . "\nQuantita: $qty\nVariante: " . post('variant') . "\nNote:\n" . post('notes') . "\n",
    "From: noreply@takeoff.pro\r\nReply-To: $email\r\n"
);

json_out(['ok' => true, 'token' => $token]);
