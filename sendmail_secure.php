<?php
// ==========================================
//  SENDMAIL SECURE – Takeoff.pro / Wazlley
//  Versione 2025 – con validazione, honeypot e sicurezza header
// ==========================================

// ✅ Solo POST consentito
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// ================== CONFIG ==================
$to = 'info@takeoff.pro';
$subject = 'Richiesta Preventivo Ballgun Pro 2025';
$redirect_ok = 'thankyou.html';
$redirect_error = 'contact.html';

// ================== HONEYPOT ==================
// Campo invisibile per bloccare bot
if (!empty($_POST['company'])) {
    exit; // se è compilato → bot
}

// ================== VALIDAZIONE ==================
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

$name     = clean($_POST['name'] ?? '');
$email    = clean($_POST['email'] ?? '');
$country  = clean($_POST['country'] ?? '');
$city     = clean($_POST['city'] ?? '');
$zip      = clean($_POST['zip'] ?? '');
$message  = clean($_POST['message'] ?? '');
$consent  = isset($_POST['consent']);

// Verifiche base
if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$consent) {
    header("Location: $redirect_error");
    exit;
}

// Blocca header injection
if (preg_match("/[\r\n]/", $email) || preg_match("/[\r\n]/", $name)) {
    exit('Invalid input detected');
}

// Limita dimensione messaggio
if (strlen($message) > 2000) {
    $message = substr($message, 0, 2000) . '...';
}

// ================== COSTRUZIONE EMAIL ==================
$body = "Richiesta dal sito Takeoff.pro\n\n";
$body .= "Nome: $name\n";
$body .= "Email: $email\n";
$body .= "Paese: $country\n";
$body .= "Città: $city\n";
$body .= "CAP: $zip\n";
$body .= "Messaggio:\n$message\n";
$body .= "\nIP: " . $_SERVER['REMOTE_ADDR'] . "\n";
$body .= "User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A');

// ================== INVIO ==================
require __DIR__ . '/inc/mailer.php';
if (send_mail($to, $subject, $body, $email)) {
    header("Location: $redirect_ok");
} else {
    header("Location: $redirect_error");
}
exit;
?>
