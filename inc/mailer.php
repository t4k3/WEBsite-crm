<?php
// ============================================================
//  Invio email centralizzato.
//  - Se esiste config/smtp.php → invia via SMTP autenticato (consigliato:
//    deliverability affidabile, firma DKIM, niente blocchi Gmail).
//    Funziona con QUALSIASI provider (easyname, Brevo, SendGrid, …):
//    basta mettere host/porta/utente/password nel config.
//  - Altrimenti ripiega sulla mail() di PHP.
//
//  Uso:  send_mail($a, $oggetto, $testo, $replyTo = null);
// ============================================================

function send_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool
{
    $cfgFile = __DIR__ . '/../config/smtp.php';
    $cfg = is_file($cfgFile) ? require $cfgFile : null;

    if (is_array($cfg) && !empty($cfg['host']) && !empty($cfg['user'])) {
        return smtp_send($cfg, $to, $subject, $body, $replyTo);
    }

    // Fallback: mail() di PHP (mittente reale + envelope sender)
    $from = 'info@takeoff.pro';
    $headers = "From: Takeoff.pro <$from>\r\n";
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers, '-f' . $from);
}

function smtp_send(array $cfg, string $to, string $subject, string $body, ?string $replyTo): bool
{
    $host     = $cfg['host'];
    $port     = (int) ($cfg['port'] ?? 587);
    $user     = $cfg['user'];
    $pass     = (string) ($cfg['pass'] ?? '');
    $from     = $cfg['from'] ?? 'info@takeoff.pro';
    $fromName = $cfg['from_name'] ?? 'Takeoff.pro';

    $transport = ($port === 465) ? 'ssl://' : 'tcp://';
    $fp = @stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20);
    if (!$fp) {
        return false;
    }
    stream_set_timeout($fp, 20);

    $get = function () use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $put = function (string $cmd) use ($fp, $get) {
        fwrite($fp, $cmd . "\r\n");
        return $get();
    };
    $code = fn(string $r): int => (int) substr($r, 0, 3);

    $get();                       // saluto 220
    $put('EHLO takeoff.pro');

    if ($port !== 465) {          // STARTTLS su 587
        $put('STARTTLS');
        if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($fp);
            return false;
        }
        $put('EHLO takeoff.pro');
    }

    $put('AUTH LOGIN');
    $put(base64_encode($user));
    if ($code($put(base64_encode($pass))) !== 235) {   // autenticazione fallita
        fclose($fp);
        return false;
    }

    // Supporta più destinatari separati da virgola
    $recipients = array_filter(array_map('trim', explode(',', $to)));
    if ($code($put('MAIL FROM:<' . $from . '>')) >= 400) { fclose($fp); return false; }
    foreach ($recipients as $rcpt) {
        if ($code($put('RCPT TO:<' . $rcpt . '>')) >= 400) { fclose($fp); return false; }
    }
    if ($code($put('DATA')) !== 354)                  { fclose($fp); return false; }

    $subjEnc = preg_match('/[\x80-\xFF]/', $subject)
        ? '=?UTF-8?B?' . base64_encode($subject) . '?='
        : $subject;

    $toHeader = implode(', ', array_map(fn($r) => '<' . $r . '>', $recipients));
    $headers = "From: $fromName <$from>\r\n"
        . "To: $toHeader\r\n"
        . "Subject: $subjEnc\r\n"
        . ($replyTo ? "Reply-To: <$replyTo>\r\n" : '')
        . 'Date: ' . date('r') . "\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n";

    // Normalizza a CRLF + dot-stuffing
    $bodyN = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));
    $bodyN = preg_replace('/^\./m', '..', $bodyN);

    fwrite($fp, $headers . "\r\n" . $bodyN . "\r\n.\r\n");
    $ok = $code($get()) === 250;
    $put('QUIT');
    fclose($fp);
    return $ok;
}
