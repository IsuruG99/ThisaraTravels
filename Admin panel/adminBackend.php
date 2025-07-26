<?php
// Backend logic for admin panel dashboard
session_start();
require '../auth-config.php';

// Fetch statistics from MongoDB
$totalVehicles = getVehiclesCollection()->countDocuments();
$totalBookings = getBookingsCollection()->countDocuments();
$totalUsers = getUsersCollection()->countDocuments();

// Fetch recent vehicles (last 3 added)
$recentVehiclesCursor = getVehiclesCollection()->find([], [
    'sort' => ['updated_at' => -1],
    'limit' => 3
]);
$recentVehicles = iterator_to_array($recentVehiclesCursor);

function getRecentBookings(): array {
    $recentBookingsCursor = getBookingsCollection()->find([], [
        'sort' => ['_id' => -1],
        'limit' => 5
    ]);
    return iterator_to_array($recentBookingsCursor);
} 