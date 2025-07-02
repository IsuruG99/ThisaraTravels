<?php
require 'vendor/autoload.php';

session_start();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use MongoDB\Client;
use Google_Client;
use Google_Service_Oauth2;

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri('http://localhost:8000/auth-google-callback.php');

if (!isset($_GET['code'])) {
    header('Location: auth.php');
    exit;
}

try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        $_SESSION['error'] = 'Google authentication failed: ' . htmlspecialchars($token['error_description'] ?? 'Unknown error');
        header('Location: auth.php');
        exit;
    }

    $client->setAccessToken($token['access_token']);

    $oauth = new Google_Service_Oauth2($client);
    $googleUser = $oauth->userinfo->get();

    // Connect to MongoDB and check if user exists
    $mongoClient = new Client($_ENV['MONGODB_URI']);
    $collection = $mongoClient->ThisaraTravels->users;
    $user = $collection->findOne(['Email' => $googleUser->email]);

    if (!$user) {
        // Register new user with Google account
        $newUser = [
            'UserName' => $googleUser->name ?? $googleUser->email,
            'Email' => $googleUser->email,
            'GoogleID' => $googleUser->id,
            'Verified' => true,
            'role' => 'user',
            'date' => date("Y-m-d"),
            'ProfilePhoto' => $googleUser->picture ?? 'img/default_profile.png',
        ];

        $insertResult = $collection->insertOne($newUser);

        if ($insertResult->getInsertedCount() !== 1) {
            $_SESSION['error'] = "Failed to create user account.";
            header('Location: auth.php');
            exit;
        }

        $user = $collection->findOne(['Email' => $googleUser->email]);
    }

    // Log in the user
    $_SESSION['username'] = $user['UserName'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_id'] = (string) $user['_id'];
    $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? 'img/default_profile.png';

    header('Location: index.php');
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Authentication error: " . $e->getMessage();
    header('Location: auth.php');
    exit;
}
