<?php
require 'auth-config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    jsonResponse(data: ['success' => false, 'error' => 'Unauthorized'], statusCode: 401);
}

$userId = $_SESSION['user_id'];
$userCollection = getUsersCollection();

try {
    switch (true) {
        // Profile photo upload
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto'])):
            handleProfilePhotoUpload(userId: $userId, userCollection: $userCollection);
            break;
        // Field update (username/email)
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field'])):
            handleFieldUpdate(userId: $userId, userCollection: $userCollection);
            break;
        // Password change
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])):
            handlePasswordChange(userId: $userId, userCollection: $userCollection);
            break;
        // Review deletion
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])):
            $reviewsCollection = getReviewsCollection();
            handleReviewDeletion(
                userId: $userId,
                reviewId: $_POST['review_id'],
                reviewsCollection: $reviewsCollection
            );
            break;
        // Booking cancellation
        case ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])):
            handleBookingCancellation(userId: $userId, bookingId: $_POST['booking_id']);
            break;
        // Default case - get user data
        default:
            fetchUserData(userId: $userId, userCollection: $userCollection);
    }
} catch (Exception $e) {
    jsonResponse(data: ['success' => false, 'error' => 'Server error'], statusCode: 500);
}

function handleProfilePhotoUpload(string $userId, MongoDB\Collection $userCollection): never
{
    $targetDirectory = "uploads/profile_photo/";
    if (!file_exists(filename: $targetDirectory)) {
        mkdir(directory: $targetDirectory, permissions: 0777, recursive: true);
    }
    $fileExtension = pathinfo(path: $_FILES['profilePhoto']['name'], flags: PATHINFO_EXTENSION);
    $targetFilePath = $targetDirectory . "profile_{$userId}_" . time() . ".$fileExtension";
    if (!move_uploaded_file(from: $_FILES['profilePhoto']['tmp_name'], to: $targetFilePath)) {
        jsonResponse(data: ['success' => false, 'error' => 'Failed to upload photo']);
    }
    $updateResult = $userCollection->updateOne(
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

function handleFieldUpdate(string $userId, MongoDB\Collection $userCollection): never
{
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
    if ($userCollection->findOne(filter: [$field => $value, '_id' => ['$ne' => new MongoDB\BSON\ObjectID($userId)]])) {
        jsonResponse(data: ['success' => false, 'error' => "$field already in use"]);
    }
    // Update the email & set Verified to false to force OTP verification
    $updateResult = $userCollection->updateOne(
        filter: ['_id' => new MongoDB\BSON\ObjectID($userId)],
        update: ['$set' => [$field === 'username' ? 'UserName' : 'Email' => $value, 'Verified' => false]]
    );
    if ($updateResult->getModifiedCount() === 0) {
        jsonResponse(data: ['success' => false, 'error' => 'No changes made']);
    }
    if ($field === 'username') {
        $_SESSION['username'] = $value;
    }
    jsonResponse(data: ['success' => true]);
}

function handleBookingCancellation(string $userId, string $bookingId): never
{
    $bookingsCollection = getBookingsCollection();
    try {
        $bookingOID = new MongoDB\BSON\ObjectId($bookingId);
        $userOID = new MongoDB\BSON\ObjectID($userId);
        // convert userId to UserName
        $user = getCurrentUser(userId: $userId);
        $username = $user['UserName'] ?? 'Unknown User';
        // Verify the booking belongs to the current user before updating
        $booking = $bookingsCollection->findOne(filter: [
            '_id' => $bookingOID,
            'user_id' => $userOID
        ]);
        if (!$booking) {
            jsonResponse(data: ['success' => false, 'error' => 'Booking not found or unauthorized'], statusCode: 404);
        }
        // Only allow cancellation if booking isn't already completed
        if (($booking['status'] ?? '') === 'completed') {
            jsonResponse(data: ['success' => false, 'error' => 'Completed bookings cannot be cancelled']);
        }
        $updateResult = $bookingsCollection->updateOne(
            filter: ['_id' => $bookingOID],
            update: ['$set' => ['status' => 'cancelled']]
        );
        // call function to mail admin about cancellation
        mailAdmin(bookingId: $bookingOID, username: $username);
        if ($updateResult->getModifiedCount() === 0) {
            jsonResponse(data: ['success' => false, 'error' => 'No changes made']);
        }
        jsonResponse(data: ['success' => true, 'message' => 'Booking cancelled successfully']);
    } catch (Exception $e) {
        jsonResponse(data: ['success' => false, 'error' => 'Server error'], statusCode: 500);
    }
}

function handlePasswordChange(string $userId, MongoDB\Collection $userCollection): never
{
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
    $updateResult = $userCollection->updateOne(
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

function handleReviewDeletion(string $userId, string $reviewId, MongoDB\Collection $reviewsCollection): never
{
    $objectId = new MongoDB\BSON\ObjectId($reviewId);
    // Verify the review belongs to the current user before deletion
    $review = $reviewsCollection->findOne(filter: [
        '_id' => $objectId,
        'userId' => new MongoDB\BSON\ObjectId($userId)
    ]);
    if (!$review) {
        jsonResponse(data: ['success' => false, 'error' => 'Review not found or unauthorized'], statusCode: 404);
    }
    $deleteResult = $reviewsCollection->deleteOne(filter: ['_id' => $objectId]);
    if ($deleteResult->getDeletedCount() === 0) {
        jsonResponse(data: ['success' => false, 'error' => 'Failed to delete review'], statusCode: 500);
    }
    jsonResponse(data: ['success' => true, 'message' => 'Review deleted successfully']);
}

function mailAdmin(string $bookingId, string $username): void
{
    $mail = getMailer();
    $bookingsCollection = getBookingsCollection();
    try {
        // Get booking details
        $booking = $bookingsCollection->findOne(filter: ['_id' => new MongoDB\BSON\ObjectId($bookingId)]);
        if (!$booking) {
            throw new Exception(message: "Booking not found.");
        }
        $pickupDate = $booking['pickup_date'] ?? 'N/A';
        $pickupTime = (isset($booking['pickup_time']) && strtotime(datetime: $booking['pickup_time']) !== false)
            ? date(format: 'h:i A', timestamp: strtotime(datetime: $booking['pickup_time'])) : 'N/A';
        $pickupLocation = $booking['pickup_location'] ?? 'N/A';
        $dropoffLocation = $booking['dropoff_location'] ?? 'N/A';
        $vehicle = $booking['vehicle_name'] ?? 'N/A';

        // Send mail to admin
        $mail->addAddress(address: $_ENV['SMTP_ADMIN']);
        $mail->Subject = "Booking Cancelled by {$username}";
        $mail->isHTML(isHtml: true);
        $mail->Body = "
            <h3 style='margin-bottom:10px;'>Booking Cancelled</h3>
            <p><strong>User:</strong> {$username}</p>
            <p><strong>Booking ID:</strong> {$bookingId}</p>
            <p><strong>Vehicle:</strong> {$vehicle}</p>
            <p><strong>Pickup:</strong> {$pickupDate} at {$pickupTime}</p>
            <p><strong>Route:</strong> {$pickupLocation} → {$dropoffLocation}</p>
            <p style='margin-top:10px;'>This booking was cancelled by the user.</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log(message: "mailAdmin error: " . $e->getMessage());
    }
}

function fetchUserData(string $userId, MongoDB\Collection $userCollection): never
{
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

