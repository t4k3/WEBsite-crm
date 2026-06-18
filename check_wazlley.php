<?php
// ==========================================
//  WAZLLEY - SELF TEST SCRIPT
// ==========================================

header('Content-Type: text/plain; charset=utf-8');

echo "🔍 WAZLLEY SITE SELF-CHECK\n";
echo "----------------------------\n";

// 1️⃣ Dominio e percorso corrente
echo "Domain: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Directory: " . __DIR__ . "\n\n";

// 2️⃣ Test file principali
$files = ['index.html', 'contact.html', 'tutorials.html', 'thankyou.html', 'sendmail.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file found (" . filesize($file) . " bytes)\n";
    } else {
        echo "❌ $file missing!\n";
    }
}
echo "\n";

// 3️⃣ Test sottocartelle assets/lang/
$dirs = ['assets/lang/it.json', 'assets/lang/en.json', 'assets/js/lang.js'];
foreach ($dirs as $path) {
    if (file_exists($path)) {
        echo "✅ $path OK\n";
    } else {
        echo "❌ $path missing!\n";
    }
}
echo "\n";

// 4️⃣ Test invio mail (solo se richiesto via ?mailtest=1)
if (isset($_GET['mailtest'])) {
    $to = 'info@takeoff.pro';
    $subject = 'Test invio mail Wazlley';
    $body = 'Questo è un test automatico dal server Wazlley.';
    $headers = 'From: webmaster@' . $_SERVER['HTTP_HOST'];
    if (mail($to, $subject, $body, $headers)) {
        echo "📧 Email test inviata correttamente a $to\n";
    } else {
        echo "⚠️ Errore nell'invio email (verificare configurazione PHP mail).\n";
    }
}

echo "\n✅ Test completato.\n";
?>
