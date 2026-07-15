<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

if (file_exists(__DIR__ . '/mail.local.php')) {
    require __DIR__ . '/mail.local.php';
}

$mailConfig = array_merge([
    'smtp_host' => env('SMTP_HOST', ''),
    'smtp_port' => (int) env('SMTP_PORT', '587'),
    'smtp_user' => env('SMTP_USER', ''),
    'smtp_pass' => env('SMTP_PASS', ''),
    'from_email' => env('MAIL_FROM', 'contact@vite-et-gourmand.fr'),
    'from_name' => env('MAIL_FROM_NAME', 'Vite & Gourmand'),
], $mailConfig ?? []);

function envoyerMail(string $destinataire, string $sujet, string $corps, ?string $replyTo = null): bool
{
    global $mailConfig;

    if ($mailConfig['smtp_host'] !== '') {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $mailConfig['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['smtp_user'];
            $mail->Password = $mailConfig['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $mailConfig['smtp_port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
            $mail->addAddress($destinataire);

            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            $mail->isHTML(false);
            $mail->Subject = $sujet;
            $mail->Body = $corps;

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log('PHPMailer: ' . $mail->ErrorInfo);

            return false;
        }
    }

    $headers = 'From: ' . $mailConfig['from_name'] . ' <' . $mailConfig['from_email'] . ">\r\n";

    if ($replyTo) {
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    }

    $headers .= "Content-Type: text/plain; charset=UTF-8";

    return mail($destinataire, $sujet, $corps, $headers);
}
