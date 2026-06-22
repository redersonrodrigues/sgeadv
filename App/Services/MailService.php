<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class MailService
    {
        public static function send($toEmail, $toName, $subject, $htmlBody)
        {
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = '0fa2294e1159c3';
            $mail->Password   = 'b5866d41ce3cde';
            $mail->Port       = 2525;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->CharSet = 'UTF-8';
            $mail->setFrom('nao-responda@procopioadvocacia.com', 'Procópio Advocacia');
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
        }
    }