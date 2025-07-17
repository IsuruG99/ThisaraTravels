<?php
require 'auth-config.php';
$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim(string: $_POST['email']);
    $db = getMongoDB();
    // Check if the email exists in the database
    $user = $db->users->findOne(filter: ['Email' => $email]);
    if ($user) {
        // Generate a unique reset token and expiration time
        $token = bin2hex(string: random_bytes(length: 32));
        $expires = new DateTime(datetime: '+1 hour');
        // Save token and expiration time to the database
        $db->password_resets->insertOne(document: [
            'Email' => $email,
            'token' => $token,
            'expires_at' => $expires->format(format: 'Y-m-d H:i:s')
        ]);
        // Create reset link
        $resetLink = "http://localhost/Thisara%20travel/auth-reset-pw.php?token=$token";

        // Send email using PHPMailer
        try {
            $mail = getMailer();
            $mail->addAddress(address: $email);
            $mail->isHTML(isHtml: true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click <a href='$resetLink'>here</a> to reset your password.<br>If you did not request this, please ignore this email.";
            $mail->send();
            $message = "<p class='success'>Password reset link has been sent to your email.</p>";
            $redirect = true;
        } catch (Exception $e) {
            $message = "<p class='error'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
        }
    } else {
        $message = "<p class='error'>Email not found!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="css/auth/forgot-pw.css">
</head>

<body>
    <div class="otp-container">
        <h2>Forgot Password</h2>
        <p>Enter your email address to receive a password reset link.</p>
        <form action="auth-forgot-pw.php" method="POST" class="forgot-password-form">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
        </form>
        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>
    </div>
    <script>
        // Redirect to login page after successful email sent
        <?php if ($redirect): ?>
            setTimeout(function () {
                window.location.href = 'auth.php';
            }, 3000);
        <?php endif; ?>
    </script>
</body>

</html>