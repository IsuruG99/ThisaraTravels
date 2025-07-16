<?php
require 'vendor/autoload.php';
use MongoDB\Client;
use MongoDB\Database;
use \PHPMailer\PHPMailer\PHPMailer;


// =============================================
// 1. BOOTSTRAP & ENVIRONMENT
// =============================================
$dotenv = Dotenv\Dotenv::createImmutable(paths: __DIR__);
$dotenv->load();

// =============================================
// 2. SESSION MANAGEMENT
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// 3. DATABASE CONFIGURATION
// =============================================
function getMongoDB(): Database {
    static $db = null;
    if ($db === null) {
        try {
            $client = new Client(uri: $_ENV['MONGODB_URI']);
            $db = $client->selectDatabase(databaseName: "ThisaraTravels");
        } catch (Exception $e) {
            die("MongoDB Connection Error: " . $e->getMessage());
        }
    }
    return $db;
}

// =============================================
// 4. EMAIL CONFIGURATION
// =============================================
function getMailer(): PHPMailer {
    $mail = new PHPMailer(exceptions: true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(address: $_ENV['SMTP_USER'], name: 'Thisara Travels & Tours');
        return $mail;
    } catch (Exception $e) {
        die("Mailer configuration error: " . $e->getMessage());
    }
}

// =============================================
// 5. UTILITY FUNCTIONS
// =============================================
function redirectWithError(string $location, string $message = ""): never {
    $_SESSION['error'] = $message;
    header("Location: $location");
    exit;
}

function redirectWithSuccess(string $location, string $message): never {
    $_SESSION['success_message'] = $message;
    header(header: "Location: $location");
    exit;
}