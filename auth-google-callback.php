<?php
require 'auth-config.php';

$client = new Google_Client();
$client->setClientId(clientId: $_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret(clientSecret: $_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri(redirectUri: 'http://localhost:8000/auth-google-callback.php');

if (!isset($_GET['code'])) {
    header(header: 'Location: auth.php');
    exit;
}

try {
    $token = $client->fetchAccessTokenWithAuthCode(code: $_GET['code']);

    if (isset($token['error'])) {
        redirectWithError(location: 'auth.php', message: 'Google authentication failed: ' . htmlspecialchars($token['error_description'] ?? 'Unknown error'));
    }

    $client->setAccessToken(token: $token['access_token']);

    $oauth = new Google_Service_Oauth2(clientOrConfig: $client);
    $googleUser = $oauth->userinfo->get();

    $db = getMongoDB();
    $collection = $db->users;
    $user = $collection->findOne(filter: ['Email' => $googleUser->email]);

    if (!$user) {
        $newUser = [
            'UserName' => $googleUser->name ?? $googleUser->email,
            'Email' => $googleUser->email,
            'GoogleID' => $googleUser->id,
            'Verified' => true,
            'role' => 'user',
            'date' => date(format: "Y-m-d"),
            'ProfilePhoto' => $googleUser->picture ?? 'img/default_profile.png',
        ];

        $insertResult = $collection->insertOne(document: $newUser);

        if ($insertResult->getInsertedCount() !== 1) {
            redirectWithError(location: 'auth.php', message: "Failed to create user account.");
        }

        $user = $collection->findOne(filter: ['Email' => $googleUser->email]);
    }
    $_SESSION['username'] = $user['UserName'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_id'] = (string) $user['_id'];
    $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? 'img/default_profile.png';
    header(header: 'Location: index.php');
    exit;

} catch (Exception $e) {
    redirectWithError(location: 'auth.php', message: "Authentication error: " . $e->getMessage());
}