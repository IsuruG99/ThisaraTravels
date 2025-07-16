<?php
require 'auth-config.php';

// Initialize Google Client
$client = new Google_Client();
$client->setClientId(clientId: $_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret(clientSecret: $_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri(redirectUri: 'http://localhost:8000/auth-google-callback.php');
$client->addScope(scope_or_scopes: 'email');
$client->addScope(scope_or_scopes: 'profile');

// Generate and redirect to authentication URL
$auth_url = $client->createAuthUrl();
header(header: 'Location: ' . filter_var(value: $auth_url, filter: FILTER_SANITIZE_URL));
exit;