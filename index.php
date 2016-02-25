<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
use vlucas\phpdotenv;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

date_default_timezone_set('UTC');

$no_update = [
    '0 upgraded, 0 newly installed, 0 to remove and 0 not upgraded.', // English
    '0 actualizados, 0 nuevos se instalarán, 0 para eliminar y 0 no actualizados.', // Spanish
];

// Execute the apt-get update and the apt-get upgrade (simulation)
$output = shell_exec('apt-get update && apt-get upgrade -s');

// Check if the command exists.
if (!($output)) die('Command not found.');

// Check if the last output line appears in the $no_update array
$lines = explode("\n", $output);
$lines = array_slice($lines, 0, count($lines)-1);
$last_line = (string)$lines[count($lines)-1];
$has_update = !(in_array($last_line, $no_update));
// Send the email
if ($has_update) {
    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    $mail->isSMTP();                                    // Set mailer to use SMTP
    $mail->Host = getenv('MAIL_HOST');                  // Specify SMTP server
    $mail->SMTPAuth = getenv('SMTP_AUTH');              // Enable SMTP authentication
    $mail->Username = getenv('MAIL_USERNAME');          // SMTP username
    $mail->Password = getenv('MAIL_PASSWORD');          // SMTP password
    $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');      // Enable TLS or SSL encryption
    $mail->Port = getenv('MAIL_PORT');                  // TCP port to connect to
    $mail->From = getenv('MAIL_FROM_ADDRESS');          // Set the mail from address
    $mail->FromName = getenv('MAIL_FROM_NAME');         // Set the mail from name
    $mail->addAddress(getenv('MAIL_TO_ADDRESS'));       // Add a recipient
    $mail->Subject = 'New update in the ' . getenv('SERVER_NAME') . ' server';
    $mail->Body    = 'Hello <br /><br />
    There are some updates in the <b>' . getenv('SERVER_NAME') . '</b> server.<br />
    Please update it ASAP.<br /><br />
    <b><u>Updates</u></b> <br /><br /><i>'.
    $output
    . ' </i><br /><br />This mail has been send by the Updater Checker at ' . date("Y-m-d H:i:s") . ' UTC';
    $mail->AltBody = 'There are some updates in the ' . getenv('SERVER_NAME') . ' server';
    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
        echo date("Y-m-d H:i:s");
    } else {
        echo 'Message has been sent' . date("Y-m-d H:i:s");
    }
} else {
    echo 'No Update.' . date("Y-m-d H:i:s");
}
