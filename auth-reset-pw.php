<?php
require 'auth-config.php';
$message = '';
$redirect = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];

    try {
        // Find the reset token in the database
        $db = getMongoDB();
        $resetRequest = $db->password_resets->findOne(filter: ['token' => $token]);
        // Check if token is valid and not expired
        if ($resetRequest && new DateTime() < new DateTime(datetime: $resetRequest['expires_at'])) {
            $hashedPassword = password_hash(password: $newPassword, algo: PASSWORD_BCRYPT);
            // Update the user's password
            $db->users->updateOne(
                filter: ['Email' => $resetRequest['Email']],
                update: ['$set' => ['Password' => $hashedPassword]]
            );
            // Delete the reset token to prevent reuse
            $db->password_resets->deleteOne(filter: ['token' => $token]);
            // Send confirmation email
            $mail = getMailer();
            try {
                $mail->addAddress(address: $resetRequest['Email']);
                $mail->isHTML(isHtml: true);
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
    } catch (Exception $e) {
        $message = "<p class='error'>Database error: " . htmlspecialchars(string: $e->getMessage()) . "</p>";
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
    <link rel="stylesheet" href="css/auth/reset-pw.css">
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

</html>