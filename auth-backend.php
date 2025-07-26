<?php
require 'auth-config.php';
try {
    $db = getMongoDB();
    $collection = $db->users;

    // Clear previous error messages on POST
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        clearAuthErrors();      // Clear previous error messages
        if (isset($_POST['signup'])) {
            validateCsrfToken();    // CSRF Check
            $username = trim($_POST["username"]);
            $email = trim($_POST["email"]);
            $password = $_POST["password"];

            // Validate input
            if (empty($username)) {
                $_SESSION['error_username'] = "Username is required.";
            } elseif (isFieldTaken(field: 'UserName', value: $username, collection: $collection)) {
                $_SESSION['error_username'] = "Username already taken.";
            }
            if (empty($email)) {
                $_SESSION['error_email'] = "Email is required.";
            } elseif (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_email'] = "Invalid email format.";
            } elseif (isFieldTaken(field: 'Email', value: $email, collection: $collection)) {
                $_SESSION['error_email'] = "Email already registered.";
            }
            if (empty($password)) {
                $_SESSION['error_password'] = "Password is required.";
            } elseif (strlen(string: $password) < 8) {
                $_SESSION['error_password'] = "Password must be at least 8 characters.";
            }

            // If no errors, proceed with signup and send OTP
            if (empty($_SESSION['error_username']) && empty($_SESSION['error_email']) && empty($_SESSION['error_password'])) {
                $hashedPassword = password_hash(password: $password, algo: PASSWORD_BCRYPT);
                $otp = rand(min: 100000, max: 999999);
                $mail = getMailer();
                $mail->addAddress(address: $email);
                $mail->isHTML(isHtml: true);
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "Your OTP code is: <strong>$otp</strong>";
                if (!$mail->send()) {
                    redirectWithError(location: 'auth.php', message: 'Mailer Error: ' . $mail->ErrorInfo);
                }
                // Insert new unverified user with OTP
                $document = [
                    'UserName' => $username,
                    'Email' => $email,
                    'Password' => $hashedPassword,
                    'OTP' => $otp,
                    'Verified' => false,
                    'role' => 'user',
                    'date' => date(format: "Y-m-d")
                ];
                $insertResult = $collection->insertOne(document: $document);
                if ($insertResult->getInsertedCount() === 1) {
                    $_SESSION['otp'] = $otp;
                    $_SESSION['email'] = $email;
                    header(header: "Location: auth-otp.php");
                    exit;
                } else {
                    redirectWithError(location: 'auth.php', message: "There was an error inserting the user into the database.");
                }
            }
            header(header: "Location: auth.php");
            exit;
        } elseif (isset($_POST['login'])) {
            validateCsrfToken();    // CSRF Check
            $username = trim(string: $_POST["username"]);
            $password = $_POST["password"];

            // Validate input
            if (empty($username)) {
                $_SESSION['error_login_username'] = "Username is required.";
            }
            if (empty($password)) {
                $_SESSION['error_login_password'] = "Password is required.";
            }
            if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                $user = $collection->findOne(filter: ['UserName' => $username]);
                if (!$user) {
                    $_SESSION['error_login_username'] = "Username not found.";
                } elseif (!password_verify(password: $password, hash: $user['Password'])) {
                    $_SESSION['error_login_password'] = "Incorrect password.";
                }
                if (empty($_SESSION['error_login_username']) && empty($_SESSION['error_login_password'])) {
                    if (!$user['Verified']) {
                        $_SESSION['otp_error'] = "Account not verified. Please verify your OTP.";
                        header(header: "Location: auth-otp.php");
                        exit;
                    }
                    $_SESSION['username'] = $user['UserName'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['user_id'] = (string) $user['_id'];
                    $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? 'img/default_profile.png';
                    $_SESSION['success_message'] = "Login successful!";
                    // redirect to index page if user
                    if ($_SESSION['role'] === 'admin') {
                        header(header: "Location: Admin%20panel/adIndex.php");
                    } else {
                        $_SESSION['user_id'] = (string) $user['_id'];
                        header(header: "Location: booking.php");
                    }
                    exit;
                }
            }
            header(header: "Location: auth.php");
            exit;
        }
    }
} catch (Exception $e) {
    redirectWithError(location: 'auth.php', message: "Connection error: " . $e->getMessage());
}