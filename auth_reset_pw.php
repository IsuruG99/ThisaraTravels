<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use MongoDB\Client;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

try {
    $client = new Client($uri);
    $db = $client->$databaseName;
} catch (Exception $e) {
    die("MongoDB Connection Error: " . $e->getMessage());
}

$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];

    // Find the reset token in the database
    $resetRequest = $db->password_resets->findOne(['token' => $token]);

    // Check if token is valid and not expired
    if ($resetRequest && new DateTime() < new DateTime($resetRequest['expires_at'])) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the user's password
        $db->users->updateOne(
            ['Email' => $resetRequest['Email']],
            ['$set' => ['Password' => $hashedPassword]]
        );

        // Delete the reset token to prevent reuse
        $db->password_resets->deleteOne(['token' => $token]);

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($_ENV['SMTP_USER'], 'Thisara Travels & Tours');
            $mail->addAddress($resetRequest['Email']);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Successful';
            $mail->Body = 'Your password has been successfully reset.';

            $mail->send();
            $message = "<p class='success'>Your password has been successfully reset. You will be redirected to the login page shortly.</p>";
            $redirect = true;

        } catch (Exception $e) {
            $message = "<p class='error'>Confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
        }

    } else {
        $message = "<p class='error'>Invalid or expired token!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .otp-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            margin-bottom: 15px;
            font-size: 24px;
            color: #333;
        }

        p {
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            width: 100%;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .success {
            color: #4CAF50;
            margin-top: 15px;
        }

        .error {
            color: #f44336;
            margin-top: 15px;
        }

        @media (max-width: 600px) {
            .otp-container {
                padding: 15px;
                max-width: 100%;
            }

            h2 {
                font-size: 20px;
            }

            input[type="email"],
            input[type="password"],
            button {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="otp-container">
        <h2>Reset Password</h2>
        <p>Enter your new password below.</p>
        <form action="auth-reset-pw.php" method="POST" class="reset-password-form">
            <!-- Token is passed via hidden input for security -->
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES); ?>">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Reset Password</button>
        </form>
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>
    </div>
    <script>
        // Redirect to login page after successful password reset
        <?php if ($redirect): ?>
            setTimeout(function () {
                window.location.href = 'auth.php';
            }, 4000);
        <?php endif; ?>
    </script>
</body>