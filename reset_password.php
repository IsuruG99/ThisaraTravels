<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use MongoDB\Client;

require 'vendor/autoload.php'; // Load PHPMailer and MongoDB dependencies

// MongoDB Connection Setup
$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels"; 
$databaseName = "ThisaraTravels";

try {
    // Create MongoDB client
    $client = new Client($uri);
    $db = $client->$databaseName; // Select the database
} catch (Exception $e) {
    die("MongoDB Connection Error: " . $e->getMessage());
}

$message = ''; // Variable to store success or error messages
$redirect = false; // Flag for redirecting to login

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];

    // Find the reset token in the database
    $resetRequest = $db->password_resets->findOne(['token' => $token]);

    if ($resetRequest && new DateTime() < new DateTime($resetRequest['expires_at'])) {
        // Token is valid and not expired
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the user's password in the database
        $db->users->updateOne(
            ['Email' => $resetRequest['Email']],
            ['$set' => ['Password' => $hashedPassword]]
        );

        // Delete the reset token (so it can't be used again)
        $db->password_resets->deleteOne(['token' => $token]);

        // Send confirmation email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // SMTP Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Using Gmail SMTP
            $mail->SMTPAuth = true;
            $mail->Username = "suraj.kavishka22@gmail.com"; // Your email
            $mail->Password = "dkywqyapmhlmmnzk"; // Your app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('suraj.kavishka22@gmail.com', 'Thisara Travels & Tours');
            $mail->addAddress($resetRequest['Email']); // Add the user's email from the reset request

            // Email Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Password Reset Successful';
            $mail->Body    = 'Your password has been successfully reset.';

            $mail->send();
            // Set success message and redirect flag
            $message = "<p class='success'>Your password has been successfully reset. You will be redirected to the login page shortly.</p>";
            $redirect = true; // Set redirect flag to true

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
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS -->

</head>
<body>
    <div class="otp-container">
        <h2>Reset Password</h2>
        <p>Enter your new password below.</p>
        <form action="reset_password.php" method="POST" class="reset-password-form">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES); ?>"> <!-- Hidden input to capture token -->

            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit">Reset Password</button>
        </form>
        <?php if (!empty($message)): ?> <!-- Display message if exists -->
            <?php echo $message; ?> <!-- Display success or error message -->
        <?php endif; ?>
    </div>
    
    <style>
    /* styles.css */
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
        color: #4CAF50; /* Green color for success messages */
        margin-top: 15px;
    }

    .error {
        color: #f44336; /* Red color for error messages */
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
        
        input[type="email"], input[type="password"], button {
            font-size: 14px;
        }
    }
    </style>
	
	
	    <script>
        
        <?php if ($redirect): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 4000); 
        <?php endif; ?>
    </script>
</body>
</html>
