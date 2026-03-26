<?php
/**
 * Sanjeevani — backend/shared/mailer.php
 * Updated to use PHPMailer with SMTP support.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust path as needed based on your structure
require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail($to, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // --- SMTP Settings ---
        $host   = $_ENV['SMTP_HOST']   ?? '';
        $user   = $_ENV['SMTP_USER']   ?? '';
        $pass   = $_ENV['SMTP_PASS']   ?? '';
        $port   = $_ENV['SMTP_PORT']   ?? 587;
        $secure = $_ENV['SMTP_SECURE'] ?? 'tls';

        if (empty($host) || empty($user) || empty($pass)) {
            throw new Exception("SMTP credentials (SMTP_HOST, SMTP_USER, SMTP_PASS) are missing from your .env file.");
        }

        // --- DEV ENVIRONMENT BYPASS ---
        // If the user hasn't set real credentials yet, mock the email delivery
        if ($user === 'your-email@gmail.com' || $pass === 'your-app-password') {
            $logFile = __DIR__ . '/../../otp_mock_log.txt';
            $logData = "[" . date('Y-m-d H:i:s') . "] MOCK EMAIL SENT TO: $to\nSUBJECT: $subject\nBODY: $htmlBody\n---------------------------------------\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
            return true; // Pretend it sent successfully so the UI advances
        }

        if (!empty($host) && !empty($user) && !empty($pass)) {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$port;
        }

        // --- Sender & Recipient ---
        $fromEmail = $_ENV['MAIL_FROM']      ?? 'noreply@Sanjeevani.local';
        $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'Sanjeevani Hospital';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Branded wrapper
        $wrappedBody = "
        <div style=\"font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;\">
            <div style=\"background: #0f172a; padding: 24px; text-align: center;\">
                <h1 style=\"color: #ffffff; margin: 0; font-size: 24px;\">Sanjeevani Hospital</h1>
            </div>
            <div style=\"padding: 32px; background: #ffffff; color: #1e293b; line-height: 1.6;\">
                $htmlBody
            </div>
            <div style=\"background: #f8fafc; padding: 16px; text-align: center; color: #64748b; font-size: 12px;\">
                &copy; " . date('Y') . " Sanjeevani Hospital Management System. All rights reserved.
            </div>
        </div>";

        $mail->Body = $wrappedBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo ?: $e->getMessage();
        error_log("PHPMailer Error: {$errorMsg}");
        throw new Exception("Email delivery failed: " . $errorMsg . " (Please verify your Gmail App Password and SMTP settings in .env)");
    }
}
