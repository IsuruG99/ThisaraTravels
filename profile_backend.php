<?php
session_start();
require 'vendor/autoload.php';
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $client = new MongoDB\Client($_ENV['MONGODB_URI']);
    $collection = $client->ThisaraTravels->users;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Handle profile photo upload (no password check needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto'])) {
    $targetDirectory = "uploads/profile_photo/";
    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION);
    $newFileName = "profile_{$userId}_" . time() . ".$fileExtension";
    $targetFilePath = $targetDirectory . $newFileName;

    if (move_uploaded_file($_FILES['profilePhoto']['tmp_name'], $targetFilePath)) {
        $updateResult = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($userId)],
            ['$set' => ['ProfilePhoto' => $targetFilePath]]
        );

        if ($updateResult->getModifiedCount() > 0) {
            $_SESSION['profile_image'] = $targetFilePath;
            echo json_encode([
                'success' => true,
                'message' => 'Profile photo updated successfully',
                'newPhotoPath' => $targetFilePath
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload photo']);
    }
    exit;
}

// Handle username/email updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field'])) {
    $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    $field = $_POST['field'];
    $value = $_POST[$field] ?? '';

    // Verify password for non-OAuth users
    if (!empty($user['Password'])) {
        $passwordField = $field . '_current_password';
        if (empty($_POST[$passwordField]) || !password_verify($_POST[$passwordField], $user['Password'])) {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            exit;
        }
    }

    // Validate email format
    if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }

    // Check for duplicates
    $existing = $collection->findOne([$field => $value, '_id' => ['$ne' => new MongoDB\BSON\ObjectID($userId)]]);
    if ($existing) {
        echo json_encode(['success' => false, 'error' => "{$field} already in use"]);
        exit;
    }

    $updateResult = $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectID($userId)],
        ['$set' => [$field === 'username' ? 'UserName' : 'Email' => $value]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        if ($field === 'UserName') {
            $_SESSION['username'] = $value;
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No changes made']);
    }
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit;
    }

    // Verify current password for non-OAuth users
    if (!empty($user['Password'])) {
        $passwordField = 'current_password';
        if (empty($_POST[$passwordField]) || !password_verify($_POST[$passwordField], $user['Password'])) {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            exit;
        }
    }

    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        exit;
    }

    $updateResult = $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectID($userId)],
        ['$set' => ['Password' => password_hash($newPassword, PASSWORD_DEFAULT)]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'No changes made']);
    }
    exit;
}

// Fetch current settings (GET request)
$user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);
if ($user) {
    $_SESSION['profile_image'] = $user['ProfilePhoto'] ?? null;
    $_SESSION['has_password'] = !empty($user['Password']);

    echo json_encode([
        'success' => true,
        'username' => $user['UserName'],
        'email' => $user['Email'],
        'profilePhoto' => $_SESSION['profile_image'],
        'hasPassword' => $_SESSION['has_password']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
?>