<?php
// WeHear sign-up mailer: receives the form from index.html and emails it to signup@wehear.eu.
// Requires PHP with a working mail() setup on the web host.

header('Content-Type: application/json; charset=utf-8');

$TO      = 'signup@wehear.eu';
$SUBJECT = 'participant';
// Sender address on your own domain (many hosts reject mail "from" other domains).
$FROM    = 'noreply@wehear.eu';

function reply($ok, $code = 200) {
    http_response_code($code);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(false, 405);
}

// Honeypot: real visitors never fill this hidden field.
if (!empty($_POST['website'])) {
    reply(true);
}

$email    = trim($_POST['email'] ?? '');
$comments = trim($_POST['comments'] ?? '');

if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    reply(false, 400);
}
$comments = mb_substr($comments, 0, 2000);

$body  = "New WeHear participant sign-up\r\n\r\n";
$body .= "Email: " . $email . "\r\n\r\n";
$body .= "Comments:\r\n" . ($comments !== '' ? $comments : '(none)') . "\r\n";

// $email is validated above, so it is safe to use in Reply-To (no header injection).
$headers  = "From: WeHear sign-up <" . $FROM . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";

$sent = mail($TO, $SUBJECT, $body, $headers);
reply($sent, $sent ? 200 : 500);
