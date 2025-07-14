<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

// Clear previous error messages on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_SESSION['error_username'], $_SESSION['error_email'], $_SESSION['error_password'],
        $_SESSION['error_login_username'], $_SESSION['error_login_password'], $_SESSION['otp_error']);
}

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $collection = $database->users;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['signup'])) {
            // --- Signup Logic ---
            $username = trim($_POST["username"]);
            $email = trim($_POST["email"]);
            $password = $_POST["password"];

            // Validate input and check for duplicates
            if (empty($username)) {
                $_SESSION['error_username'] = "Username is required.";
            } elseif ($collection->findOne(['UserName' => $username])) {
                $_SESSION['error_username'] = "Username already taken.";
            }
            if (empty($email)) {
                $_SESSION['error_email'] = "Email is required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_email'] = "Invalid email format.";
            } elseif ($collection->findOne(['Email' => $email])) {
                $_SESSION['error_email'] = "Email already registered.";
            }
            if (empty($password)) {
                $_SESSION['error_password'] = "Password is required.";
            } elseif (strlen($password) < 8) {
                $_SESSION['error_password'] = "Password must be at least 8 characters.";
            }

            // If no errors, proceed with signup and send OTP
            if (empty($_SESSION['error_username']) && empty($_SESSION['error_email']) && empty($_SESSION['error_password'])) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $otp = rand(100000, 999999);

                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $_ENV['SMTP_USER'];
                $mail->Password = $_ENV['SMTP_PASS'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom($_ENV['SMTP_USER'], 'Suraj');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "Your OTP code is: <strong>$otp</strong>";

                if (!$mail->send()) {
                    $_SESSION['error'] = 'Mailer Error: ' . $mail->ErrorInfo;
                    header("Location: auth.php");
                } else {
                    // Insert new unverified user with OTP
                    $document = [
                        'UserName' => $username,
                        'Email' => $email,
                        'Password' => $hashedPassword,
                        'OTP' => $otp,
                        'Verified' => false,
                        'role' => 'user',
                        'date' => date("Y-m-d")
                    ];
                    $insertResult = $collection->insertOne($document);

                    if ($insertResult->getInsertedCount() === 1) {
                        $_SESSION['otp'] = $otp;
                        $_SESSION['email'] = $email;
                        header("Location: auth-otp.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "There was an error inserting the user into the database.";
                    }
                }
            }
            header("Location: auth.php");
            exit;
        } elseif (isset($_POST['login'])) {
            // --- Login Logic ---
            $username = trim($_POST["username"]);
            $password = $_POST["password"];
            if (empty($username)) {
                $_SESSION['error_login_username'] = "Username is required.";
            }
            if (empty($password)) {
                $_SESSION['error_login_password'] = "Password is required.";
            }
            if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                $user = $collection->findOne(['UserName' => $username]);
                if (!$user) {
                    $_SESSION['error_login_username'] = "Username not found.";
                } elseif (!password_verify($password, $user['Password'])) {
                    $_SESSION['error_login_password'] = "Incorrect password.";
                }
                if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                    if (!$user['Verified']) {
                        $_SESSION['otp_error'] = "Account not verified. Please verify your OTP.";
                        header("Location: auth-otp.php");
                        exit;
                    }
                    $_SESSION['username'] = $user['UserName'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = (string) $user['_id'];
                    $_SESSION['profile_image'] = isset($user['ProfilePhoto']) ? $user['ProfilePhoto'] : 'img/default_profile.png';
                    $_SESSION['success_message'] = "Login successful!";
                    header("Location: index.php");
                    exit;
                }
            }
            header("Location: auth.php");
            exit;
        }
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}