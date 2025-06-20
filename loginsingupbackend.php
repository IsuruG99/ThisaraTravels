<?php
require 'vendor/autoload.php'; // Ensure Composer's autoload is included

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels";
$databaseName = "ThisaraTravels";

// Enable error reporting to display PHP errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start(); // Start the session to store error messages

// Clear previous error messages
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    unset($_SESSION['error_username']);
    unset($_SESSION['error_email']);
    unset($_SESSION['error_password']);
    unset($_SESSION['error_login_username']);
    unset($_SESSION['error_login_password']);
    unset($_SESSION['otp_error']); // For OTP verification errors
}

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $collection = $database->users;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['signup'])) {
            // Handle signup
            $username = trim($_POST["username"]);
            $email = trim($_POST["email"]);
            $password = $_POST["password"];

            // Validate input
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

            // If no errors, proceed with signup
            if (empty($_SESSION['error_username']) && empty($_SESSION['error_email']) && empty($_SESSION['error_password'])) {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                error_log("Hashed Password: " . $hashedPassword); // Log the hashed password for debugging

                // Generate OTP (6-digit random number)
                $otp = rand(100000, 999999);

                // Set up PHPMailer for sending the OTP
                $mail = new PHPMailer();

                // Set up SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = "suraj.kavishka22@gmail.com"; // Your email
                $mail->Password = "dkywqyapmhlmmnzk"; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Set email sender and recipient
                $mail->setFrom('suraj.kavishka22@gmail.com', 'Suraj'); // Sender's email and name
                $mail->addAddress($email); // Recipient's email from signup form

                // Set email format and content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "Your OTP code is: <strong>$otp</strong>";

                // Send the email and check for success
                if (!$mail->send()) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                    $_SESSION['error'] = 'Mailer Error: ' . $mail->ErrorInfo; // Capture any errors
                    error_log("MAIL SEND RESULT: " . $mail->ErrorInfo);
                    header("Location: login.php");
                } else {
                    // Insert the new user but mark them as unverified
                    $document = [
                        'UserName' => $username,
                        'Email' => $email,
                        'Password' => $hashedPassword,
                        'OTP' => $otp, // Store the OTP for verification
                        'Verified' => false, // Set as unverified
                        'role' => 'user', // Default role is 'user'
                        'date' => date("Y-m-d")
                    ];

                    // Attempt to insert the document and check for success
                    $insertResult = $collection->insertOne($document);
                    error_log("Insert Result: " . print_r($insertResult, true));

                    // Check if the insertion was successful
                    if ($insertResult->getInsertedCount() === 1) {
                        // Save OTP and email in session for verification
                        $_SESSION['otp'] = $otp;
                        $_SESSION['email'] = $email; // Store email in session

                        // Redirect to the OTP verification page
                        error_log("Redirecting to submit_otp.php for OTP verification.");
                        header("Location: submit_otp.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "There was an error inserting the user into the database.";
                    }
                }
            }

            // Redirect back to signup if there were errors
            header("Location: login.php");
            exit;
        } elseif (isset($_POST['login'])) {
            // Handle login
            $username = trim($_POST["username"]);
            $password = $_POST["password"];

            // Validate input
            if (empty($username)) {
                $_SESSION['error_login_username'] = "Username is required.";
            }
            if (empty($password)) {
                $_SESSION['error_login_password'] = "Password is required.";
            }

            // Fetch user data from database
            if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                $user = $collection->findOne(['UserName' => $username]);

                // Check if user exists and password is correct
                if (!$user) {
                    $_SESSION['error_login_username'] = "Username not found.";
                } elseif (!password_verify($password, $user['Password'])) {
                    $_SESSION['error_login_password'] = "Incorrect password.";
                }

                // If no errors, proceed with login
                if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                    // Check if user is verified
                    if (!$user['Verified']) {
                        $_SESSION['otp_error'] = "Account not verified. Please verify your OTP.";
                        header("Location: submit_otp.php");
                        exit;
                    }

                    // Store username, user ID, and profile photo in the session after successful login
                    $_SESSION['username'] = $user['UserName'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = (string) $user['_id'];
                    $_SESSION['profile_image'] = isset($user['ProfilePhoto']) ? $user['ProfilePhoto'] : 'img/default_profile.png'; // Set profile photo or default

                    // Redirect based on role
                    $_SESSION['success_message'] = "Login successful!";
                    if ($user['role'] === 'admin') {
                        header("Location: index.php"); // Redirect to admin dashboard
                    } else {
                        header("Location: index.php"); // Redirect to user homepage
                    }
                    exit;
                }
            }

            // Redirect back to the form if errors
            header("Location: login.php");
            exit;
        }
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}