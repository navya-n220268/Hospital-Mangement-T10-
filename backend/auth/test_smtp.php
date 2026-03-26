<?php
/**
 * Test SMTP Configuration
 * Run this script to verify your .env settings are correct and PHPMailer is working.
 * URL: http://localhost/Hospital-Mangement-T10-/backend/auth/test_smtp.php
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../shared/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$testEmail = $_GET['email'] ?? '';

if (empty($testEmail)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide an email to test. Usage: ?email=your_email@example.com',
        'env_vars' => [
            'SMTP_HOST' => $_ENV['SMTP_HOST'] ?? 'NOT SET',
            'SMTP_USER' => $_ENV['SMTP_USER'] ?? 'NOT SET',
            'SMTP_PORT' => $_ENV['SMTP_PORT'] ?? 'NOT SET',
        ]
    ]);
    exit;
}

$subject = "Sanjeevani SMTP Test";
$htmlBody = "<h2>SMTP Configuration Successful!</h2><p>If you received this email, your .env settings for PHPMailer are correct.</p>";

// To capture PHPMailer output, we can override some logic or just rely on the return value of sendMail.
$result = sendMail($testEmail, $subject, $htmlBody);

if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => "Success! Test email sent to $testEmail via PHPMailer."
    ]);
} else {
    // Note: sendMail in mailer.php falls back to mail() if PHPMailer fails.
    // Check your PHP error log for exact PHPMailer errors.
    echo json_encode([
        'success' => false,
        'message' => "PHPMailer failed, fallback to native mail() returned: " . json_encode($result) . ". Check your PHP error_log for PHPMailer Exception details."
    ]);
}
