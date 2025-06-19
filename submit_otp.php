<?php
require 'vendor/autoload.php'; // Include Composer's autoload


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels"; 
$databaseName = "ThisaraTravels"; 

session_start(); // Start the session to access OTP, email, and track OTP attempts

// Initialize OTP attempts count if not set
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

// Handle OTP submission
try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $collection = $database->users;

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["otp"])) {
        $otp = trim($_POST["otp"]); // Get the OTP from the form
        $email = $_SESSION['email']; // Get the email from the session
        
        // Check if user has exceeded 3 OTP attempts
        if ($_SESSION['otp_attempts'] < 3) {
            // Find the user by email and OTP
            $user = $collection->findOne(['Email' => $email, 'OTP' => intval($otp)]);
            
            if ($user) {
                // OTP is correct, update the user's verified status
                $updateResult = $collection->updateOne(
                    ['Email' => $email],
                    ['$set' => ['Verified' => true]] // Set Verified to true
                );

                if ($updateResult->getModifiedCount() === 1) {
                    // Set success message and reset attempt count
                    $_SESSION['success_message'] = "Registration successfully...";
                    $_SESSION['otp_verified'] = true;  // Flag to show success message
                    $_SESSION['otp_attempts'] = 0; // Reset OTP attempts
                    unset($_SESSION['otp']);
                    
                    // Use JavaScript to redirect after a delay
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000); // 2 second delay
                    </script>";
                    
                    // Sleep for 3 seconds to allow user to see the success message
                    sleep(3); 
                } else {
                    $_SESSION['otp_error'] = "There was an error verifying your account. Please try again.";
                }
            } else {
                // Invalid OTP
                $_SESSION['otp_attempts']++; // Increment OTP attempts
                $_SESSION['otp_error'] = "Invalid OTP. You have " . (3 - $_SESSION['otp_attempts']) . " attempts left.";
            }
        }
        
        // Check if the user has exceeded the maximum attempts
        if ($_SESSION['otp_attempts'] >= 3) {
            $_SESSION['otp_error'] = "You have exceeded the maximum attempts. Please resend OTP.";
        }
    }

    // Handle resend OTP
if (isset($_POST['resend'])) {
    // Generate a new OTP
    $newOtp = rand(100000, 999999);

    // Update the OTP in the database
    $collection->updateOne(
        ['Email' => $_SESSION['email']],
        ['$set' => ['OTP' => $newOtp]]
    );

    // Set up PHPMailer
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
    $mail->addAddress($_SESSION['email']); // Recipient's email from the session

    // Set email format and content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Your New OTP Code';
    $mail->Body = "Your new OTP code is: <strong>$newOtp</strong>";

    // Send the email and handle the result
    if (!$mail->send()) {
        $_SESSION['otp_error'] = 'Mailer Error: ' . $mail->ErrorInfo; // Capture any errors
    } else {
        $_SESSION['otp_attempts'] = 0; // Reset attempts for new OTP
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

</head>
<body>

<div class="otp-container">
    <h2>OTP Verification</h2>

    <!-- Display the form -->
    <form method="POST" action="submit_otp.php" >
        <input type="text" name="otp" placeholder="Enter your OTP" required>
        <button type="submit">Submit OTP</button>
    </form>

    <form method="POST" action="submit_otp.php">
        <button type="submit" name="resend" class="resend-btn" 
            <?php echo ($_SESSION['otp_attempts'] >= 3) ? '' : 'disabled'; ?>>Resend OTP</button>
    </form>
    
    <!-- Display success message if OTP is correct -->
    <?php if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true): ?>
        <p class="success-message"><?php echo $_SESSION['success_message']; ?></p>
        <?php unset($_SESSION['otp_verified']); // Remove flag after showing message ?>
    <?php endif; ?>

    <!-- Display error message if OTP is incorrect -->
    <?php if (isset($_SESSION['otp_error'])): ?>
        <p class="error-message"><?php echo $_SESSION['otp_error']; ?></p>
        <?php unset($_SESSION['otp_error']); // Clear error after displaying ?>
    <?php endif; ?>
</div>
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
            background-color: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 400px;
            width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
        .resend-btn {
            background-color: #ffc107;
            transition: opacity 0.3s; /* Smooth transition for opacity */
        }
        .resend-btn:hover {
            background-color: #e0a800;
        }
        /* Styles for disabled state */
        .resend-btn:disabled {
            opacity: 0.5; /* Reduced opacity when disabled */
            cursor: not-allowed; /* Change cursor to indicate unclickable */
        }
        .success-message, .error-message {
            text-align: center;
            margin-top: 10px;
            font-size: 16px;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
		
		/* Responsive adjustments */
        @media (max-width: 480px) {
            .otp-container {
                padding: 15px; /* Less padding on small screens */
            }
            input[type="text"],
            button {
                font-size: 14px; /* Smaller font size */
            }
        }
    </style>
	
	
	<script>
    function preventSubmit() {
        if (<?php echo isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true ? 'true' : 'false'; ?>) {
            document.getElementById('otpForm').onsubmit = function(e) {
                e.preventDefault();
            };
        }
    }
</script>
</body>
</html>
