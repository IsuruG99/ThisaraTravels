<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

ini_set(option: 'display_errors', value: 1);
error_reporting(error_level: E_ALL);

session_start();

// Initialize OTP attempts count
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $collection = $database->users;
    // Handle OTP submission
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["otp"])) {
        $otp = trim(string: $_POST["otp"]);
        $email = $_SESSION['email'];
        if ($_SESSION['otp_attempts'] < 3) {
            // Validate OTP for the user
            $user = $collection->findOne(['Email' => $email, 'OTP' => intval(value: $otp)]);
            if ($user) {
                // OTP is correct, verify user
                $updateResult = $collection->updateOne(
                    ['Email' => $email],
                    ['$set' => ['Verified' => true]]
                );
                if ($updateResult->getModifiedCount() === 1) {
                    $_SESSION['success_message'] = "Registration successful.";
                    $_SESSION['otp_verified'] = true;
                    $_SESSION['otp_attempts'] = 0;
                    unset($_SESSION['otp']);
                    // Redirect after short delay to show success message
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'auth.php';
                        }, 2000);
                    </script>";
                    sleep(seconds: 3);
                } else {
                    $_SESSION['otp_error'] = "Error verifying your account. Please try again.";
                }
            } else {
                // Invalid OTP
                $_SESSION['otp_attempts']++;
                $_SESSION['otp_error'] = "Invalid OTP. You have " . (3 - $_SESSION['otp_attempts']) . " attempts left.";
            }
        }
        if ($_SESSION['otp_attempts'] >= 3) {
            $_SESSION['otp_error'] = "You have exceeded the maximum attempts. Please resend OTP.";
        }
    }

    // Handle resend OTP
    if (isset($_POST['resend'])) {
        $newOtp = rand(min: 100000, max: 999999);

        // Update OTP in database
        $collection->updateOne(
            ['Email' => $_SESSION['email']],
            ['$set' => ['OTP' => $newOtp]]
        );

        // Send new OTP via email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV["SMTP_USER"];
        $mail->Password = $_ENV["SMTP_PASS"];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(address: $_ENV['SMTP_USER'], name: 'Suraj');
        $mail->addAddress(address: $_SESSION['email']);
        $mail->isHTML(isHtml: true);
        $mail->Subject = 'Your New OTP Code';
        $mail->Body = "Your new OTP code is: <strong>$newOtp</strong>";

        if (!$mail->send()) {
            $_SESSION['otp_error'] = 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $_SESSION['otp_attempts'] = 0;
            $_SESSION['otp_error'] = "A new OTP has been sent to your email.";
        }
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="css/auth/otp.css">
</head>

<body>
    <div class="otp-container">
        <h2>OTP Verification</h2>
        <!-- OTP Submission Form -->
        <form method="POST" action="auth-otp.php">
            <input type="text" name="otp" placeholder="Enter your OTP" required>
            <button type="submit">Submit OTP</button>
        </form>
        <!-- Resend OTP Form -->
        <form method="POST" action="auth-otp.php">
            <button type="submit" name="resend" class="resend-btn" <?php echo ($_SESSION['otp_attempts'] >= 3) ? '' : 'disabled'; ?>>Resend OTP</button>
        </form>
        <!-- Success Message -->
        <?php if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true): ?>
            <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
            <?php unset($_SESSION['otp_verified']); ?>
        <?php endif; ?>
        <!-- Error Message -->
        <?php if (isset($_SESSION['otp_error'])): ?>
            <p class="error-message"><?php echo $_SESSION['otp_error']; ?></p>
            <?php unset($_SESSION['otp_error']); ?>
        <?php endif; ?>
    </div>
</body>

</html>