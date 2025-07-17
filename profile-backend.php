<?php
require 'auth-config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    jsonResponse(data: ['success' => false, 'error' => 'Unauthorized'], statusCode: 401);
}

$userId = $_SESSION['user_id'];
$collection = getUsersCollection();

try {
    switch (true) {
        // Profile photo upload
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto'])):
            handleProfilePhotoUpload(userId: $userId, collection: $collection);
            break;
        // Field update (username/email)
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field'])):
            handleFieldUpdate(userId: $userId, collection: $collection);
            break;       
        // Password change
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])):
            handlePasswordChange(userId: $userId, collection: $collection);
            break;
        // Default case - get user data
        default:
            fetchUserData(userId: $userId, collection: $collection);
    }
} catch (Exception $e) {
    jsonResponse(data: ['success' => false, 'error' => 'Server error'], statusCode: 500);
}

function handleProfilePhotoUpload(string $userId, MongoDB\Collection $collection): never {
    $targetDirectory = "uploads/profile_photo/";
    if (!file_exists(filename: $targetDirectory)) {
        mkdir(directory: $targetDirectory, permissions: 0777, recursive: true);
    }
    $fileExtension = pathinfo(path: $_FILES['profilePhoto']['name'], flags: PATHINFO_EXTENSION);
    $targetFilePath = $targetDirectory . "profile_{$userId}_" . time() . ".$fileExtension";
    if (!move_uploaded_file(from: $_FILES['profilePhoto']['tmp_name'], to: $targetFilePath)) {
        jsonResponse(data: ['success' => false, 'error' => 'Failed to upload photo']);
    }
    $updateResult = $collection->updateOne(
        filter: ['_id' => new MongoDB\BSON\ObjectID($userId)],
        update: ['$set' => ['ProfilePhoto' => $targetFilePath]]
    );
    if ($updateResult->getModifiedCount() === 0) {
        jsonResponse(data: ['success' => false, 'error' => 'No changes made']);
    }
    $_SESSION['profile_image'] = $targetFilePath;
    jsonResponse(data: [
        'success' => true,
        'message' => 'Profile photo updated successfully',
        'newPhotoPath' => $targetFilePath
    ]);
}

function handleFieldUpdate(string $userId, MongoDB\Collection $collection): never {
    $user = getCurrentUser(userId: $userId);
    if (!$user) {
        jsonResponse(data: ['success' => false, 'error' => 'User not found'], statusCode: 404);
    }
    $field = $_POST['field'];
    $value = $_POST[$field] ?? '';
    // Password verification for non-OAuth users
    if (!empty($user['Password'])) {
        $passwordField = $field . '_current_password';
        if (empty($_POST[$passwordField]) || !validatePassword(user: $user, password: $_POST[$passwordField])) {
            jsonResponse(data: ['success' => false, 'error' => 'Current password is incorrect']);
        }
    }
    // Email validation
    if ($field === 'email' && !filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL)) {
        jsonResponse(data: ['success' => false, 'error' => 'Invalid email format']);
    }
    // Check for duplicates
    if ($collection->findOne(filter: [$field => $value, '_id' => ['$ne' => new MongoDB\BSON\ObjectID($userId)]])) {
        jsonResponse(data: ['success' => false, 'error' => "$field already in use"]);
    }
    $updateResult = $collection->updateOne(
        filter: ['_id' => new MongoDB\BSON\ObjectID($userId)],
        update: ['$set' => [$field === 'username' ? 'UserName' : 'Email' => $value]]
    );
    if ($updateResult->getModifiedCount() === 0) {
        jsonResponse(data: ['success' => false, 'error' => 'No changes made']);
    }
    if ($field === 'username') {
        $_SESSION['username'] = $value;
    }
    jsonResponse(data: ['success' => true]);
}

function handlePasswordChange(string $userId, MongoDB\Collection $collection): never {
    $user = getCurrentUser(userId: $userId);
    if (!$user) {
        jsonResponse(data: ['success' => false, 'error' => 'User not found'], statusCode: 404);
    }
    // Verify current password for non-OAuth users
    if (!empty($user['Password'])) {
        if (empty($_POST['current_password']) || !validatePassword(user: $user, password: $_POST['current_password'])) {
            jsonResponse(data: ['success' => false, 'error' => 'Current password is incorrect']);
        }
    }
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        jsonResponse(data: ['success' => false, 'error' => 'Passwords do not match']);
    }
    $updateResult = $collection->updateOne(
        filter: ['_id' => new MongoDB\BSON\ObjectID($userId)],
        update: ['$set' => ['Password' => password_hash(password: $_POST['new_password'], algo: PASSWORD_DEFAULT)]]
    );
    jsonResponse(data: [
        'success' => $updateResult->getModifiedCount() > 0,
        'message' => $updateResult->getModifiedCount() > 0 
            ? 'Password updated successfully' 
            : 'No changes made'
    ]);
}

function fetchUserData(string $userId, MongoDB\Collection $collection): never {
    $user = getCurrentUser(userId: $userId);
    if (!$user) {
        jsonResponse(data: ['success' => false, 'error' => 'User not found'], statusCode: 404);
    }
    $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? null;
    $_SESSION['has_password'] = !empty($user['Password']);
    jsonResponse(data: [
        'success' => true,
        'username' => $user['UserName'],
        'email' => $user['Email'],
        'profilePhoto' => $_SESSION['profile_image'],
        'hasPassword' => $_SESSION['has_password']
    ]);
}