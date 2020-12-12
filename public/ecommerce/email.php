<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once './public/ecommerce/dependencies/PHPMailer/src/Exception.php';
require_once './public/ecommerce/dependencies/PHPMailer/src/PHPMailer.php';
require_once './public/ecommerce/dependencies/PHPMailer/src/SMTP.php';

require_once './public/ecommerce/config.php';
function sendEmail($email, $username, $text)
{

  // Instantiation and passing `true` enables exceptions
  $mail = new PHPMailer(true);
  try {
    $mail->SMTPDebug = 0;
    //Server settings    
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = EMAIL;                     // SMTP username
    $mail->Password   = PASSWORD;                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

    //Recipients
    $mail->setFrom(EMAIL, 'Ecommerce');
    $mail->addAddress($email, $username);     // Add a recipient    

    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    // Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Welcome to Ecommerce!';
    $mail->Body    = $text;
    $mail->AltBody = $text;
    $mail->send();
    return true;
  } catch (Exception $e) {
    return false;
  }
}
