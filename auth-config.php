<?php
require 'vendor/autoload.php';
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;
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
// 4. MAILER CONFIGURATION
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
// 5. HELPER FUNCTIONS
// =============================================
function redirectWithError(string $location, string $message = ""): never {
    $_SESSION['error'] = $message;
    header(header: "Location: $location");
    exit;
}

function validateCsrfToken(): void {
    if (empty($_POST['csrf_token']) || !hash_equals(known_string: $_SESSION['csrf_token'], user_string: $_POST['csrf_token'])) {
        redirectWithError(location: 'auth.php', message: "CSRF token validation failed");
    }
}

function clearAuthErrors(): void {
    $errors = [
        'error_username', 'error_email', 'error_password',
        'error_login_username', 'error_login_password', 'otp_error'
    ];
    foreach ($errors as $error) unset($_SESSION[$error]);
}

function isFieldTaken(string $field, string $value, MongoDB\Collection $collection): bool {
    return $collection->findOne(filter: [$field => $value]) !== null;
}

// =============================================
// 6. USER MANAGEMENT FUNCTIONS
// =============================================
function getUsersCollection(): Collection {
    return getMongoDB()->selectCollection(collectionName: 'users');
}
function getBookingsCollection(): Collection {
    return getMongoDB()->selectCollection(collectionName: 'bookings');
}
function getReviewsCollection(): Collection {
    return getMongoDB()->selectCollection(collectionName: 'reviews');
}
function getCurrentUser(string $userId): ?array {
    try {
        $doc = getUsersCollection()->findOne(filter: ['_id' => new MongoDB\BSON\ObjectID($userId)]);
        return $doc ? $doc->getArrayCopy() : null;
    } catch (Exception $e) {
        return null;
    }
}
function validatePassword(array $user, string $password): bool {
    return !empty($user['Password']) && password_verify(password: $password, hash: $user['Password']);
}
function jsonResponse(array $data, int $statusCode = 200): never {
    http_response_code(response_code: $statusCode);
    header(header: 'Content-Type: application/json');
    echo json_encode(value: $data);
    exit;
}
function getVehiclesCollection(): MongoDB\Collection {
    return getMongoDB()->selectCollection('vehicles');
}

function insertVehicle(array $vehicle): bool {
    try {
        $result = getVehiclesCollection()->insertOne($vehicle);
        return $result->getInsertedCount() > 0;
    } catch (Exception $e) {
        // Optionally log the error or handle it as needed
        return false;
    }
}

