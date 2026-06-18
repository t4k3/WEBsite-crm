<?php
require __DIR__ . '/../inc/auth.php';

$firstRun = admin_count() === 0;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $err = 'Sessione scaduta, riprova.';
    } elseif ($firstRun) {
        $u = post('username');
        $p = (string) ($_POST['password'] ?? '');
        if (strlen($u) < 3 || strlen($p) < 8) {
            $err = 'Username min 3 caratteri, password min 8.';
        } else {
            admin_create($u, $p);
            admin_login($u, $p);
            header('Location: index.php');
            exit;
        }
    } else {
        if (admin_login(post('username'), (string) ($_POST['password'] ?? ''))) {
            header('Location: index.php');
            exit;
        }
        usleep(400000);
        $err = 'Credenziali non valide.';
    }
}
$csrf = csrf_token();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $firstRun ? 'Crea accesso admin' : 'Login' ?> — Wazlley CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body{background:#0b0f19;color:#fff;font-family:system-ui,Arial,sans-serif}</style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <form method="POST" class="w-full max-w-sm bg-gray-900 p-8 rounded-2xl space-y-4">
        <h1 class="text-xl font-bold"><?= $firstRun ? 'Crea il primo accesso admin' : 'Wazlley CRM — Login' ?></h1>
        <?php if ($firstRun): ?>
            <p class="text-sm text-gray-400">Nessun utente presente. Imposta ora le tue credenziali di accesso.</p>
        <?php endif; ?>
        <?php if ($err): ?>
            <p class="text-sm text-red-400"><?= e($err) ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>" />
        <input name="username" placeholder="Username" required autofocus
               class="w-full p-3 rounded text-black" />
        <input name="password" type="password" placeholder="Password" required
               class="w-full p-3 rounded text-black" />
        <button class="w-full bg-yellow-400 text-black py-3 rounded font-semibold hover:bg-yellow-300">
            <?= $firstRun ? 'Crea e accedi' : 'Accedi' ?>
        </button>
    </form>
</body>
</html>
