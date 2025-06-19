<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer and MongoDB dependencies

// MongoDB Connection Setup
$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels"; 
$databaseName = "ThisaraTravels";

try {
    // Create MongoDB client
    $client = new MongoDB\Client($uri);
    $db = $client->$databaseName; // Select the database
} catch (Exception $e) {
    die("MongoDB Connection Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $user = $db->users->findOne(['Email' => $email]);

    if ($user) {
        // Generate a unique reset token and expiration time
        $token = bin2hex(random_bytes(32)); // Random 64-character string
        $expires = new DateTime('+1 hour'); // Token expires in 1 hour

        // Save token and expiration time to the database
        $db->password_resets->insertOne([
            'Email' => $email,
            'token' => $token,
            'expires_at' => $expires->format('Y-m-d H:i:s')
        ]);

        // Create reset link
        $resetLink = "http://localhost/Thisara%20travel/reset_password.php?token=$token";

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = "suraj.kavishka22@gmail.com"; // Your email
    $mail->Password = "dkywqyapmhlmmnzk"; // Your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

            // Recipients
            $mail->setFrom('suraj.kavishka22@gmail.com', 'Thisara Travels & Tours');
            $mail->addAddress($email); // Add the user's email

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click <a href='$resetLink'>here</a> to reset your password.<br>If you did not request this, please ignore this email.";

            $mail->send();
            $message = "<p class='success'>Password reset link has been sent to your email.</p>";
			$redirect = true; // Set redirect flag to true
			
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
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS -->
</head>
<body>
    <div class="otp-container">
        <h2>Forgot Password</h2>
        <p>Enter your email address to receive a password reset link.</p>
        <form action="forgot_password.php" method="POST" class="forgot-password-form">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Reset Link</button>
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
    width: 95%;
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
        }, 3000); 
        <?php endif; ?>
    </script>	
</body>
	
</html>
