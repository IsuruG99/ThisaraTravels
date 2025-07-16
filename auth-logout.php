<?php
session_start(); // Start or resume the session

// Unset all session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Clear the session cookie (optional, but recommended)
if (ini_get(option: "session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        name: session_name(),
        value: '',
        expires_or_options: time() - 42000,
        path: $params["path"],
        domain: $params["domain"],
        secure: $params["secure"],
        httponly: $params["httponly"]
    );
}

// Redirect to index.php after logging out
header(header: "Location: index.php");
exit();
