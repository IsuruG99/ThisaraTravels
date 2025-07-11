<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');

// Include MongoDB PHP driver
require 'vendor/autoload.php'; // Ensure MongoDB PHP library is installed via Composer

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];

try {
    $client = new MongoDB\Client($uri);
    $client = new Client($mongoUri);
    $database = $client->selectDatabase('ThisaraTours');
    $bookingsCollection = $database->selectCollection('bookings');

    // Get form data
    $vehicleType = filter_input(INPUT_POST, 'vehicle_type', FILTER_SANITIZE_STRING);
    $vehicleName = filter_input(INPUT_POST, 'vehicle_name', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $pickupLocation = filter_input(INPUT_POST, 'pickup_location', FILTER_SANITIZE_STRING);
    $customPickup = filter_input(INPUT_POST, 'custom_pickup_location', FILTER_SANITIZE_STRING);
    $dropoffLocation = filter_input(INPUT_POST, 'dropoff_location', FILTER_SANITIZE_STRING);
    $customDropoff = filter_input(INPUT_POST, 'custom_dropoff_location', FILTER_SANITIZE_STRING);
    $pickupDate = filter_input(INPUT_POST, 'pickup_date', FILTER_SANITIZE_STRING);
    $dropoffDate = filter_input(INPUT_POST, 'dropoff_date', FILTER_SANITIZE_STRING);
    $pickupTime = filter_input(INPUT_POST, 'pickup_time', FILTER_SANITIZE_STRING);
    $specialRequest = filter_input(INPUT_POST, 'Special_Request', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (empty($vehicleType) || empty($vehicleName) || empty($name) || empty($phone) || 
        empty($pickupLocation) || empty($dropoffLocation) || empty($pickupDate) || 
        empty($dropoffDate) || empty($pickupTime)) {
        throw new Exception('All required fields must be filled.');
    }

    // Validate custom locations if "Other" is selected
    if ($pickupLocation === 'Other' && empty($customPickup)) {
        throw new Exception('Custom pick-up location is required when "Other" is selected.');
    }
    if ($dropoffLocation === 'Other' && empty($customDropoff)) {
        throw new Exception('Custom drop-off location is required when "Other" is selected.');
    }

    // Combine locations (use custom if provided)
    $pickup = ($pickupLocation === 'Other') ? $customPickup : $pickupLocation;
    $dropoff = ($dropoffLocation === 'Other') ? $customDropoff : $dropoffLocation;

    // Convert dates to MongoDB UTCDateTime
    $pickupDateTime = new DateTime($pickupDate . ' ' . $pickupTime);
    $dropoffDateTime = new DateTime($dropoffDate . ' ' . $pickupTime); // Assuming same time for simplicity
    if ($dropoffDateTime <= $pickupDateTime) {
        throw new Exception('Drop-off date must be after pick-up date.');
    }

    // Check vehicle availability
    $startDate = new UTCDateTime($pickupDateTime->getTimestamp() * 1000);
    $endDate = new UTCDateTime($dropoffDateTime->getTimestamp() * 1000);

    $existingBookings = $bookingsCollection->countDocuments([
        'vehicle_type' => $vehicleType,
        '$or' => [
            [
                'pickup_date' => ['$lte' => $endDate],
                'dropoff_date' => ['$gte' => $startDate]
            ]
        ]
    ]);

    if ($existingBookings > 0) {
        throw new Exception('Vehicle is not available for the selected dates.');
    }

    // Prepare booking data
    $bookingData = [
        'vehicle_type' => $vehicleType,
        'vehicle_name' => $vehicleName,
        'name' => $name,
        'phone' => $phone,
        'pickup_location' => $pickup,
        'dropoff_location' => $dropoff,
        'pickup_date' => $startDate,
        'dropoff_date' => $endDate,
        'pickup_time' => $pickupTime,
        'special_request' => $specialRequest ?: '',
        'status' => 'pending',
        'created_at' => new UTCDateTime((new DateTime())->getTimestamp() * 1000)
    ];

    // Insert booking into database
    $result = $bookingsCollection->insertOne($bookingData);

    if ($result->getInsertedCount() === 1) {
        echo json_encode([
            'status' => 'success',
            'message' => "Booking request for $vehicleName submitted successfully! Dates: $pickupDate to $dropoffDate"
        ]);
    } else {
        throw new Exception('Failed to save booking.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>