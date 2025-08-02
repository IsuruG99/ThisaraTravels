<?php
session_start();

// MongoDB connection
require 'vendor/autoload.php';
use MongoDB\Client;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
try {
    $client = new MongoDB\Client($uri);
    $db = $client->ThisaraTravels;
    $usersCollection = $db->users;
    $settingsCollection = $db->settings;
    $bookingsCollection = $db->bookings;
    $vehiclesCollection = $db->vehicles;

    // Simulated index creation for performance
    // $bookingsCollection->createIndex(['pickup_date' => 1, 'dropoff_date' => 1]);
    // $usersCollection->createIndex(['UserName' => 1]);
    // $vehiclesCollection->createIndex(['vehicle_name' => 1]);

    // Fetch maintenance mode
    $settings = $settingsCollection->findOne(['name' => 'maintenance_mode']);
    $maintenance_mode = isset($settings['status']) && strtolower($settings['status']) === 'on';
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Debugging: Log session username
if (isset($_SESSION['username'])) {
    error_log("Session Username: " . $_SESSION['username']);
}

// Maintenance mode logic
if ($maintenance_mode) {
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $user = $usersCollection->findOne(['UserName' => $username]);
        if ($user && isset($user['role']) && strtolower($user['role']) === 'admin') {
            error_log("Admin user logged in, access granted.");
        } else {
            error_log("Redirecting user to maintenance.php (Not admin).");
            header('Location: maintenance.php');
            exit();
        }
    } else {
        error_log("Redirecting guest to maintenance.php.");
        header('Location: maintenance.php');
        exit();
    }
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_booking') {
    // --- Rate limiting: prevent booking spam ---
    $rate_limit_seconds = 60; // Allow one booking per minute
    $now = time();
    // --- Rate limiting: prevent booking spam ---
    $rate_limit_seconds = 60; // Allow one booking per minute
    $now = time();
    if (isset($_SESSION['last_booking_time'])) {
        $last_booking_time = $_SESSION['last_booking_time'];
        if (($now - $last_booking_time) < $rate_limit_seconds) {
            $_SESSION['booking_message'] = [
                'text' => 'You are submitting bookings too quickly. Please wait a minute before trying again.',
                'type' => 'error'
            ];
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // --- Duplicate booking prevention ---
    $username = $_SESSION['username'] ?? 'Guest';
    $vehicle_type = filter_var($_POST['vehicle_type'] ?? '', FILTER_SANITIZE_STRING);
    $vehicle_name = filter_var($_POST['vehicle_name'] ?? '', FILTER_SANITIZE_STRING);
    $pickup_location = filter_var($_POST['pickup_location'] ?? '', FILTER_SANITIZE_STRING);
    $dropoff_location = filter_var($_POST['dropoff_location'] ?? '', FILTER_SANITIZE_STRING);
    $pickup_date = filter_var($_POST['pickup_date'] ?? '', FILTER_SANITIZE_STRING);
    $dropoff_date = filter_var($_POST['dropoff_date'] ?? '', FILTER_SANITIZE_STRING);
    $pickup_time = filter_var($_POST['pickup_time'] ?? '', FILTER_SANITIZE_STRING);

    $duplicateQuery = [
        'username' => $username,
        'vehicle_type' => $vehicle_type,
        'vehicle_name' => $vehicle_name,
        'pickup_location' => $pickup_location,
        'dropoff_location' => $dropoff_location,
        'pickup_date' => $pickup_date,
        'dropoff_date' => $dropoff_date,
        'pickup_time' => $pickup_time
    ];
    $existingBooking = $bookingsCollection->findOne($duplicateQuery);
    if ($existingBooking) {
        $_SESSION['booking_message'] = [
            'text' => 'You have already made a booking with the same details for these dates.',
            'type' => 'error'
        ];
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['booking_message'] = ['text' => 'Invalid CSRF token. Please refresh the page and try again.', 'type' => 'error'];
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $bookingData = [
        'vehicle_type' => $vehicle_type,
        'vehicle_name' => $vehicle_name,
        'name' => filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING),
        'phone' => filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_STRING),
        'pickup_location' => $pickup_location,
        'dropoff_location' => $dropoff_location,
        'custom_pickup_location' => filter_var($_POST['custom_pickup_location'] ?? '', FILTER_SANITIZE_STRING),
        'custom_dropoff_location' => filter_var($_POST['custom_dropoff_location'] ?? '', FILTER_SANITIZE_STRING),
        'pickup_date' => $pickup_date,
        'dropoff_date' => $dropoff_date,
        'pickup_time' => $pickup_time,
        'Special_Request' => filter_var($_POST['Special_Request'] ?? '', FILTER_SANITIZE_STRING),
        'status' => 'pending',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'username' => $_SESSION['username'] ?? 'Guest',
        'email' => filter_var($_SESSION['email'] ?? '', FILTER_SANITIZE_EMAIL)
    ];

    // Validate required fields
    $required_fields = ['vehicle_type', 'vehicle_name', 'name', 'phone', 'pickup_location', 
                       'dropoff_location', 'pickup_date', 'dropoff_date', 'pickup_time'];
    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($bookingData[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    if ($bookingData['pickup_location'] === 'Other' && empty($bookingData['custom_pickup_location'])) {
        $errors[] = "Custom pickup location is required when 'Other' is selected.";
    }
    if ($bookingData['dropoff_location'] === 'Other' && empty($bookingData['custom_dropoff_location'])) {
        $errors[] = "Custom dropoff location is required when 'Other' is selected.";
    }
    if (strlen($bookingData['name']) < 2) {
        $errors[] = "Name must be at least 2 characters long.";
    }
    if (!preg_match('/^\+?\d{9,15}$/', $bookingData['phone'])) {
        $errors[] = "Invalid phone number format. Please enter a valid WhatsApp number.";
    }
    if (strtotime($bookingData['pickup_date']) > strtotime($bookingData['dropoff_date'])) {
        $errors[] = "Drop-off date cannot be before pick-up date.";
    }

    if (empty($errors)) {
        try {
            // Save last booking time for rate limiting
            $_SESSION['last_booking_time'] = time();
            // ...existing code for booking insertion...
            exit;
        } catch (Exception $e) {
            $_SESSION['booking_message'] = [
                'text' => 'An error occurred while processing your booking. Please try again later.',
                'type' => 'error'
            ];
            $_SESSION['form_data'] = $bookingData;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['booking_message'] = ['text' => implode('<br>', $errors), 'type' => 'error'];
        $_SESSION['form_data'] = $bookingData;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['booking_message'] = ['text' => 'Invalid CSRF token.', 'type' => 'error'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_SESSION['pending_booking'])) {
        try {
            $result = $bookingsCollection->insertOne($_SESSION['pending_booking']);
            if ($result->getInsertedCount() > 0) {
                $_SESSION['booking_message'] = ['text' => 'Booking confirmed successfully!', 'success-message' => 'success_message'];
                unset($_SESSION['pending_booking']);
            } else {
                $_SESSION['booking_message'] = ['text' => 'Failed to confirm booking.', 'type' => 'error'];
            }
        } catch (Exception $e) {
            error_log("Error: confirming booking: " . $e->getMessage());
            $_SESSION['booking_message'] = ['text' => 'Error confirming booking: ' . $e->getMessage(), 'type' => 'error'];
        }
    } else {
        $_SESSION['booking_message'] = ['text' => 'No booking data to confirm.', 'type' => 'error'];
        return;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['booking_message'] = ['text' => 'Invalid CSRF token.', 'type' => 'error'];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    unset($_SESSION['pending_booking']);
    $_SESSION['booking_message'] = ['text' => 'Booking cancelled.', 'type' => 'info'];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Retrieve session messages
$booking_message = $_SESSION['booking_message'] ?? null;
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['booking_message']);
unset($_SESSION['form_data']);

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Journey - Thisara Travels & Tours</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/css/intlTelInput.css" rel="stylesheet">
    <link rel="stylesheet" href="bookingstyle.css">
</head>
<body>

    <!-- Header (Nav Bar) -->
    <header class="header" role="banner">
        <nav class="nav-container" aria-label="Main navigation">
            <a href="index.php" class="logo" aria-label="Thisara Travels Home">
                <i class="fas fa-car" aria-hidden="true"></i> Thisara Travels
            </a>
            <ul class="nav-links">
                <li><a href="index.php" aria-label="Home"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <li><a href="about.php" aria-label="About"><i class="fas fa-info-circle" aria-hidden="true"></i> About</a></li>
                <li><a href="service.php" aria-label="Services"><i class="fas fa-concierge-bell" aria-hidden="true"></i> Services</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-alt" aria-hidden="true"></i> Page
                        <i class="fas fa-chevron-down dropdown-icon" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu" aria-label="Page submenu">
                        <li><a href="testimonial.php" aria-label="Reviews"><i class="fas fa-star" aria-hidden="true"></i> Reviews</a></li>
                        <li><a href="booking.php" aria-label="Bookings"><i class="fas fa-calendar-check" aria-hidden="true"></i> Bookings</a></li>
                    </ul>
                </li>
                <li><a href="contact.php" aria-label="Contact"><i class="fas fa-envelope" aria-hidden="true"></i> Contact</a></li>
                <li>
                    <?php
                    if (!isset($_SESSION['username'])) {
                        echo '<a href="auth.php" aria-label="Login"><i class="fas fa-user" aria-hidden="true"></i> Login</a>';
                    } else {
                        $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                        $user_role = $_SESSION['role'] ?? 'user';
                        $redirect_url = ($user_role === 'admin') ? 'Admin%20panel/adIndex.php' : 'profile-page.php';
                        echo '<a href="' . $redirect_url . '" aria-label="User Profile"><i class="fas fa-user" aria-hidden="true"></i> Profile</a>';
                    }
                    ?>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Booking Modal -->
    <div id="booking-modal-overlay" class="booking-modal-overlay" role="presentation"></div>
    <div id="booking-modal" class="booking-modal" role="dialog" aria-labelledby="booking-modal-title" aria-modal="true">
        <button class="close-btn" onclick="hideBookingModal()" aria-label="Close booking modal">Close</button>
        <h2 id="booking-modal-title">Book Your Vehicle</h2>
        <div id="booking-message" class="message <?php echo isset($booking_message) ? $booking_message['type'] . ' show' : ''; ?>" aria-live="polite">
            <?php echo isset($booking_message) ? htmlspecialchars($booking_message['text']) : ''; ?>
        </div>
        <form id="booking-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="action" value="submit_booking">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="vehicle_type" id="vehicle-type" value="<?php echo htmlspecialchars($form_data['vehicle_type'] ?? ''); ?>">
            <input type="hidden" name="vehicle_name" id="vehicle-name" value="<?php echo htmlspecialchars($form_data['vehicle_name'] ?? ''); ?>">
            <div class="form-group">
                <label for="name"><i class="fas fa-user" aria-hidden="true"></i> Your Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" 
                       value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required aria-describedby="name-error">
                <div id="name-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="phone"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       placeholder="Enter your WhatsApp number" 
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" required aria-describedby="phone-error">
                <div class="search-hint">
                    <i class="fas fa-search" aria-hidden="true"></i> You can search countries by name or dial code
                </div>
                <div id="phone-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="pickup-location-input"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Pick-Up Location</label>
                <div class="autocomplete-container">
                    <input type="text" class="form-control autocomplete-input" id="pickup-location-input" 
                           placeholder="Type to search pickup location..." 
                           value="<?php echo htmlspecialchars($form_data['pickup_location'] ?? ''); ?>" 
                           autocomplete="off" aria-describedby="pickup-error" required>
                    <input type="hidden" name="pickup_location" id="pickup-location-hidden" 
                           value="<?php echo htmlspecialchars($form_data['pickup_location'] ?? ''); ?>" required>
                    <div class="autocomplete-dropdown" id="pickup-dropdown"></div>
                </div>
                <input type="text" class="form-control custom-location" id="custom-pickup-location" 
                       name="custom_pickup_location" placeholder="Custom Pick-Up Location" 
                       value="<?php echo htmlspecialchars($form_data['custom_pickup_location'] ?? ''); ?>" aria-describedby="pickup-error">
                <div id="pickup-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="dropoff-location-input"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Drop-Off Location</label>
                <div class="autocomplete-container">
                    <input type="text" class="form-control autocomplete-input" id="dropoff-location-input" 
                           placeholder="Type to search dropoff location..." 
                           value="<?php echo htmlspecialchars($form_data['dropoff_location'] ?? ''); ?>" 
                           autocomplete="off" aria-describedby="dropoff-error" required>
                    <input type="hidden" name="dropoff_location" id="dropoff-location-hidden" 
                           value="<?php echo htmlspecialchars($form_data['dropoff_location'] ?? ''); ?>" required>
                    <div class="autocomplete-dropdown" id="dropoff-dropdown"></div>
                </div>
                <input type="text" class="form-control custom-location" id="custom-dropoff-location" 
                       name="custom_dropoff_location" placeholder="Custom Drop-Off Location" 
                       value="<?php echo htmlspecialchars($form_data['custom_dropoff_location'] ?? ''); ?>" aria-describedby="dropoff-error">
                <div id="dropoff-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="pickup-date"><i class="fas fa-calendar" aria-hidden="true"></i> Pickup Date</label>
                <input type="date" class="form-control" id="pickup-date" name="pickup_date" 
                       value="<?php echo htmlspecialchars($form_data['pickup_date'] ?? ''); ?>" required aria-describedby="pickup-date-error">
                <div id="pickup-date-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="dropoff-date"><i class="fas fa-calendar" aria-hidden="true"></i> Drop-Off Date</label>
                <input type="date" class="form-control" id="dropoff-date" name="dropoff_date" 
                       value="<?php echo htmlspecialchars($form_data['dropoff_date'] ?? ''); ?>" required aria-describedby="dropoff-date-error">
                <div id="dropoff-date-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="pickup-time"><i class="fas fa-clock" aria-hidden="true"></i> Pickup Time</label>
                <input type="time" class="form-control" id="pickup-time" name="pickup_time" 
                       value="<?php echo htmlspecialchars($form_data['pickup_time'] ?? ''); ?>" required aria-describedby="pickup-time-error">
                <div id="pickup-time-error" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="special-request"><i class="fas fa-comment" aria-hidden="true"></i> Special Request</label>
                <textarea class="form-control" id="special-request" name="Special_Request" 
                          placeholder="Special Request"><?php echo htmlspecialchars($form_data['Special_Request'] ?? ''); ?></textarea>
            </div>
            <button class="btn-submit" type="submit" aria-label="Submit booking">Book Now</button>
        </form>
        <div id="confirmation-view" class="confirmation-view" role="region" aria-labelledby="confirmation-title">
            <h3 id="confirmation-title">Confirm Your Booking</h3>
            <div class="booking-details" id="booking-details"></div>
            <div class="confirmation-buttons">
                <form id="confirm-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="confirm_booking">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button class="btn-confirm" type="submit" aria-label="Confirm booking">Confirm</button>
                </form>
                <form id="cancel-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <input type="hidden" name="action" value="cancel_booking">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button class="btn-cancel" type="submit" aria-label="Cancel booking">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container" role="main">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-calendar-alt" aria-hidden="true"></i> Book Your Journey</h1>
            <p>Choose your perfect vehicle and travel dates</p>
        </div>

        <!-- Filter Section -->
        <?php
        // Get all vehicle types and seat counts for dropdowns
        $allVehicles = $vehiclesCollection->find();
        $vehicleTypes = [];
        $seatCounts = [];
        foreach ($allVehicles as $v) {
            $type = $v['vehicle_name'] ?? '';
            if ($type && !in_array($type, $vehicleTypes)) $vehicleTypes[] = $type;
            if (isset($v['seat_count']) && !in_array((int)$v['seat_count'], $seatCounts)) $seatCounts[] = (int)$v['seat_count'];
        }
        sort($vehicleTypes);
        sort($seatCounts);

        // Build capacity ranges for dropdown
        // Only show specific Passenger Capacity ranges
        $capacityRanges = ["1-5", "5-9", "5-12"];
        $selectedType = $_GET['vehicleType'] ?? '';
        $selectedCapacity = $_GET['capacity'] ?? '';
        $selectedStartDate = $_GET['startDate'] ?? '';
        $selectedEndDate = $_GET['endDate'] ?? '';
        $query = [];
        $filterApplied = false;
        if ($selectedType) {
            $query['vehicle_name'] = $selectedType;
            $filterApplied = true;
        }
        if ($selectedCapacity) {
            if (preg_match('/^(\d+)-(\d+)$/', $selectedCapacity, $matches)) {
                $min = (int)$matches[1];
                $max = (int)$matches[2];
                $query['seat_count'] = ['$gte' => $min, '$lte' => $max];
                $filterApplied = true;
            }
        }

        // --- Exclude vehicles booked for selected date range ---
        $bookedVehicleNames = [];
        if ($selectedStartDate && $selectedEndDate) {
            $bookingQuery = [
                // Overlapping bookings: pickup_date <= selectedEndDate AND dropoff_date >= selectedStartDate
                '$and' => [
                    ['pickup_date' => ['$lte' => $selectedEndDate]],
                    ['dropoff_date' => ['$gte' => $selectedStartDate]]
                ]
            ];
            $bookedBookings = $bookingsCollection->find($bookingQuery);
            foreach ($bookedBookings as $booking) {
                if (!empty($booking['vehicle_name'])) {
                    $bookedVehicleNames[] = $booking['vehicle_name'];
                }
            }
            if (!empty($bookedVehicleNames)) {
                if (isset($query['vehicle_name']) && is_string($query['vehicle_name'])) {
                    // If already filtering by vehicle_name, convert to $nin array
                    $query['vehicle_name'] = ['$nin' => $bookedVehicleNames, '$eq' => $query['vehicle_name']];
                } else {
                    $query['vehicle_name'] = ['$nin' => $bookedVehicleNames];
                }
                $filterApplied = true;
            }
        }
        $vehicles = !$filterApplied ? $vehiclesCollection->find() : $vehiclesCollection->find($query);
        ?>
        <div class="filter-section">
            <h2 class="filter-title">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Search & Filter Options
            </h2>
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="vehicleType"><i class="fas fa-car" aria-hidden="true"></i> Vehicle Type</label>
                    <select id="vehicleType" name="vehicleType" class="filter-select" aria-label="Select vehicle type">
                        <option value="">All Vehicles</option>
                        <?php if (!empty($vehicleTypes)) {
                            foreach ($vehicleTypes as $type) {
                                echo '<option value="' . htmlspecialchars($type) . '"' . ($selectedType == $type ? ' selected' : '') . '>' . ucfirst(htmlspecialchars($type)) . '</option>';
                            }
                        } ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="capacity"><i class="fas fa-users" aria-hidden="true"></i> Passenger Capacity</label>
                    <select id="capacity" class="filter-select" aria-label="Select passenger capacity">
                        <option value="">Any Capacity</option>
                        <?php foreach ($capacityRanges as $range): ?>
                            <option value="<?php echo $range; ?>" <?php if($selectedCapacity==$range)echo 'selected';?>><?php echo $range; ?> Passengers</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar" aria-hidden="true"></i> Date Range</label>
                    <div class="date-range">
                        <input type="date" id="startDate" class="filter-input" aria-label="Start date">
                        <input type="date" id="endDate" class="filter-input" aria-label="End date">
                    </div>
                </div>
            </div>
            <div class="filter-buttons">
                <button class="btn btn-primary" onclick="searchVehicles()" aria-label="Search vehicles">
                    <i class="fas fa-search" aria-hidden="true"></i> Search Vehicles
                </button>
                <button class="btn btn-secondary" onclick="clearFilters()" aria-label="Clear filters">
                    <i class="fas fa-undo" aria-hidden="true"></i> Clear Filters
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading" aria-live="polite">
            <div class="spinner"></div>
            Searching available vehicles...
        </div>

        <!-- Vehicles Section -->
        <div class="vehicles-section">
            <h2 class="section-title">Available Vehicles</h2>
            <div class="vehicles-grid" id="vehiclesGrid">
                <?php
                $vehicleCount = 0;
                foreach ($vehicles as $vehicle):
                    $vehicleCount++;
                    $photo = !empty($vehicle['vehiclePhoto']) ? htmlspecialchars($vehicle['vehiclePhoto']) : 'img/default-vehicle.png';
                ?>
                    <div class="vehicle-card" 
                         data-type="<?php echo htmlspecialchars($vehicle['vehicle_name'] ?? ''); ?>" 
                         data-capacity="<?php echo htmlspecialchars($vehicle['seat_count'] ?? ''); ?>" 
                         role="article" 
                         aria-label="<?php echo htmlspecialchars($vehicle['vehicle_name'] ?? ''); ?>">
                        <div class="vehicle-image">
                            <img src="<?php echo $photo; ?>" 
                                 alt="<?php echo htmlspecialchars($vehicle['vehicle_name'] ?? 'Vehicle'); ?>" 
                                 style="max-width:100%;max-height:100px;object-fit:cover;">
                        </div>
                        <div class="vehicle-info">
                            <span class="vehicle-type">Type: <?php echo ucfirst(htmlspecialchars($vehicle['vehicle_name'] ?? '')); ?></span>
                            <div class="vehicle-name">Name: <?php echo ucfirst(htmlspecialchars($vehicle['vehicle_name'] ?? '')); ?></div>
                            <div class="vehicle-features">
                                <span class="feature"><i class="fas fa-users"></i> <?php echo htmlspecialchars($vehicle['seat_count'] ?? ''); ?> Seats</span>
                                <span class="feature"><i class="fas fa-snowflake"></i> <?php echo htmlspecialchars($vehicle['ac_nac'] ?? ''); ?></span>
                                <?php if (!empty($vehicle['features'])):
                                    $featuresArr = explode(',', $vehicle['features']);
                                    foreach ($featuresArr as $f): ?>
                                        <span class="feature"><i class="fas fa-check"></i> <?php echo trim(htmlspecialchars($f)); ?></span>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                            <button class="book-btn" 
                                    onclick="bookVehicle('<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>', '<?php echo htmlspecialchars($vehicle['vehicle_name']); ?>')"
                                    aria-label="Book <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>">
                                <i class="fas fa-calendar-plus"></i> Book Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($vehicleCount === 0): ?>
                    <div style="grid-column:1/-1;text-align:center;color:#dc3545;font-size:1.2rem;padding:2rem;">No vehicles found for selected filters.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Intro Section -->
        <section class="intro-section" aria-labelledby="intro-heading">
            <div class="content">
                <h2 id="intro-heading">Seamless Travel Awaits: Book Your Adventure Today!</h2>
                <p>At Thisara Travels and Tours, we make it easy for you to embark on your journey through the enchanting landscapes of Sri Lanka. Our user-friendly online booking system allows you to reserve your travel arrangements with just a few clicks. Choose from a wide selection of vehicles tailored to your needs, and enjoy the flexibility of customizing your pick-up and drop-off locations. Start your unforgettable experience today—your Sri Lankan adventure is just a click away!</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank" aria-label="Contact us on WhatsApp">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i> Contact Us (+94702180024)
                </a>
            </div>
            <div class="decorative-icon">
                <i class="fas fa-map" aria-hidden="true"></i>
            </div>
        </section>

        <!-- Call-to-Action Section -->
        <section class="cta-section" aria-labelledby="cta-heading">
            <div class="decorative-icon">
                <i class="fas fa-route" aria-hidden="true"></i>
            </div>
            <div class="content">
                <h2 id="cta-heading">Have Any Pre Booking Question?</h2>
                <p>Experience luxurious comfort and convenience with Thisara Travels & Tours's top-tier car booking service. Our spacious vehicles, accommodating up to 6 passengers without a driver, ensure a smooth journey to and from your desired destinations. Travel in style while enjoying the scenic beauty of Srilanka, all in the company of Texi's reliable and professional team.</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank" aria-label="Contact us on WhatsApp">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i> Contact Us (+94702180024)
                </a>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/intlTelInput.min.js"></script>

    <script>
    // --- Set min date ---
    const today = new Date().toISOString().split('T')[0];
    ['startDate', 'endDate', 'pickup-date', 'dropoff-date'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.min = today;
    });

    document.getElementById('startDate')?.addEventListener('change', function () {
        const endDateEl = document.getElementById('endDate');
        const dropoffEl = document.getElementById('dropoff-date');
        if (endDateEl) endDateEl.min = this.value;
        if (dropoffEl) dropoffEl.min = this.value;
    });

    // --- Initialize phone input with intl-tel-input ---
    const phoneInput = document.getElementById('phone');
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "auto",
        geoIpLookup: async function (success, failure) {
            try {
                const res = await fetch('https://ipapi.co/json/');
                const data = await res.json();
                success(data.country_code || 'lk');
            } catch (error) {
                console.error('GeoIP lookup failed:', error);
                success('lk');
            }
        },
        separateDialCode: true,
        preferredCountries: ["lk", "in", "gb", "us"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js",
        formatOnDisplay: true,
        nationalMode: false,
        autoPlaceholder: "aggressive",
        customPlaceholder: function (placeholder, data) {
            return "e.g. " + placeholder;
        },
        allowDropdown: true
    });

    // Check if flag sprite is loaded
    const flagSprite = new Image();
    flagSprite.src = 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/img/flags.png';
    flagSprite.onerror = () => {
        console.error('Failed to load flag sprite.');
    };

    // --- Validate phone number ---
    phoneInput.addEventListener('input', validatePhone);
    phoneInput.addEventListener('blur', validatePhone);
    phoneInput.addEventListener('countrychange', validatePhone);

    function validatePhone() {
        const validationMessage = document.getElementById('phone-error');
        if (iti.isValidNumber()) {
            validationMessage.textContent = 'Valid phone number';
            validationMessage.className = 'validation-message valid';
        } else {
            const errorCode = iti.getValidationError();
            let message = 'Please enter a valid phone number';
            if (errorCode === intlTelInputUtils.validationError.TOO_SHORT) message = 'Phone number is too short';
            else if (errorCode === intlTelInputUtils.validationError.TOO_LONG) message = 'Phone number is too long';
            else if (errorCode === intlTelInputUtils.validationError.INVALID_COUNTRY_CODE) message = 'Invalid country code';
            validationMessage.textContent = message;
            validationMessage.className = 'validation-message invalid';
        }
    }

    // --- Booking Modal Functions ---
    function showBookingModal(title, type, name) {
        const modal = document.getElementById('booking-modal');
        const overlay = document.getElementById('booking-modal-overlay');
        const modalTitle = document.getElementById('booking-modal-title');
        const vehicleTypeInput = document.getElementById('vehicle-type');
        const vehicleNameInput = document.getElementById('vehicle-name');
        const message = document.getElementById('booking-message');
        const form = document.getElementById('booking-form');
        const pickupInput = document.getElementById('pickup-location-input');
        const dropoffInput = document.getElementById('dropoff-location-input');
        const pickupHidden = document.getElementById('pickup-location-hidden');
        const dropoffHidden = document.getElementById('dropoff-location-hidden');
        const customPickup = document.getElementById('custom-pickup-location');
        const customDropoff = document.getElementById('custom-dropoff-location');

        modalTitle.textContent = title;
        vehicleTypeInput.value = type;
        vehicleNameInput.value = name;
        
        // Check if there's already an error message from server-side
        const hasExistingError = message.classList.contains('show') && message.classList.contains('error');
        
        if (!hasExistingError) {
            // Only reset message and show form if there's no existing error
            message.style.display = 'none';
            message.className = 'message';
            form.style.display = 'block';
            
            // Reset form but retain form_data if available
            form.reset();
            pickupInput.value = '<?php echo htmlspecialchars($form_data['pickup_location'] ?? ''); ?>';
            dropoffInput.value = '<?php echo htmlspecialchars($form_data['dropoff_location'] ?? ''); ?>';
            pickupHidden.value = '<?php echo htmlspecialchars($form_data['pickup_location'] ?? ''); ?>';
            dropoffHidden.value = '<?php echo htmlspecialchars($form_data['dropoff_location'] ?? ''); ?>';
            customPickup.style.display = pickupHidden.value === 'Other' ? 'block' : 'none';
            customPickup.value = '<?php echo htmlspecialchars($form_data['custom_pickup_location'] ?? ''); ?>';
            customDropoff.style.display = dropoffHidden.value === 'Other' ? 'block' : 'none';
            customDropoff.value = '<?php echo htmlspecialchars($form_data['custom_dropoff_location'] ?? ''); ?>';

            // Pre-fill other fields
            document.getElementById('name').value = '<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>';
            document.getElementById('phone').value = '<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>';
            document.getElementById('pickup-date').value = '<?php echo htmlspecialchars($form_data['pickup_date'] ?? ''); ?>';
            document.getElementById('dropoff-date').value = '<?php echo htmlspecialchars($form_data['dropoff_date'] ?? ''); ?>';
            document.getElementById('pickup-time').value = '<?php echo htmlspecialchars($form_data['pickup_time'] ?? ''); ?>';
            document.getElementById('special-request').value = '<?php echo htmlspecialchars($form_data['Special_Request'] ?? ''); ?>';

            // Pre-fill dates from filter
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            if (startDate && !document.getElementById('pickup-date').value) document.getElementById('pickup-date').value = startDate;
            if (endDate && !document.getElementById('dropoff-date').value) document.getElementById('dropoff-date').value = endDate;
        } else {
            // If there's an existing error, keep form hidden
            form.style.display = 'none';
        }

        modal.classList.add('show');
        overlay.classList.add('show');
    }

    function hideBookingModal() {
        document.getElementById('booking-modal').classList.remove('show');
        document.getElementById('booking-modal-overlay').classList.remove('show');
        const msg = document.getElementById('booking-message');
        const form = document.getElementById('booking-form');
        
        // Reset message and form display
        msg.style.display = 'none';
        msg.className = 'message';
        form.style.display = 'block';
    }

    function showModalMessage(text, type) {
        const message = document.getElementById('booking-message');
        const form = document.getElementById('booking-form');
        
        // Create message content with try again button for errors
        if (type === 'error') {
            message.innerHTML = `
                <div style="margin-bottom: 10px;">${text}</div>
                <button type="button" onclick="retryBookingForm()" class="try-again-btn">Try Again</button>
            `;
        } else {
            message.textContent = text;
        }
        
        message.className = `message ${type} show`;
        message.style.display = 'block';
        form.style.display = 'none';
        
        if (type === 'success') setTimeout(hideBookingModal, 3000);
    }

    // Function to retry booking form after error
    function retryBookingForm() {
        const message = document.getElementById('booking-message');
        const form = document.getElementById('booking-form');
        
        // Hide message and show form
        message.style.display = 'none';
        message.className = 'message';
        form.style.display = 'block';
    }

    // Function to show date selection message without opening booking modal
    function showDateSelectionMessage(text) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(220, 53, 69, 0.95);
            color: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            font-size: 1rem;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid #dc3545;
            min-width: 300px;
            text-align: center;
            cursor: pointer;
        `;
        
        notification.innerHTML = `
            <div style="margin-bottom: 10px;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                ${text}
            </div>
            <div style="font-size: 0.9rem; color: #ffc107; margin-top: 8px;">
                Please select dates in the filter section above
            </div>
        `;
        
        notification.onclick = function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        };
        
        highlightFilterSection();
        
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
                @keyframes pulse {
                    0% {
                        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
                    }
                    70% {
                        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
                    }
                    100% {
                        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }

    function highlightFilterSection() {
        const filterSection = document.querySelector('.filter-section');
        const dateInputs = document.querySelectorAll('#startDate, #endDate');
        
        if (filterSection) {
            filterSection.style.animation = 'pulse 2s infinite';
            filterSection.style.border = '2px solid #dc3545';
            
            setTimeout(() => {
                filterSection.style.animation = '';
                filterSection.style.border = '';
            }, 6000);
        }
        
        dateInputs.forEach(input => {
            input.style.borderColor = '#dc3545';
            input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';
            
            setTimeout(() => {
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }, 6000);
        });
        
        filterSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function showLoginMessage(text) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 193, 7, 0.95);
            color: #333;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            font-size: 1rem;
            font-weight: 600;
            animation: slideIn 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid #FF8F00;
            min-width: 300px;
            text-align: center;
            cursor: pointer;
        `;
        
        notification.innerHTML = `
            <div style="margin-bottom: 10px;">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                ${text}
            </div>
            <div style="font-size: 0.9rem; color: #666;">
                <a href="auth.php" style="color: #FF8F00; text-decoration: none; font-weight: bold;">
                    Click here to login
                </a>
            </div>
        `;
        
        notification.onclick = function() {
            window.location.href = 'auth.php';
        };
        
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 6000);
    }

    document.getElementById('pickup-date')?.addEventListener('change', function () {
        const dropoffDate = document.getElementById('dropoff-date');
        if (dropoffDate.value && this.value > dropoffDate.value) {
            dropoffDate.value = this.value;
        }
    });

    document.getElementById('dropoff-date')?.addEventListener('change', function () {
        const pickupDate = document.getElementById('pickup-date');
        if (pickupDate.value && this.value < pickupDate.value) {
            showModalMessage('Drop-off date cannot be before pick-up date.', 'error');
            this.value = pickupDate.value;
        }
    });

    document.getElementById('booking-form')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const name = document.getElementById('name').value.trim();
        const phone = iti.getNumber();
        const pickup = document.getElementById('pickup-location-hidden').value;
        const dropoff = document.getElementById('dropoff-location-hidden').value;
        const customPickup = document.getElementById('custom-pickup-location').value.trim();
        const customDropoff = document.getElementById('custom-dropoff-location').value.trim();
        const pickupDate = document.getElementById('pickup-date').value;
        const dropoffDate = document.getElementById('dropoff-date').value;
        const pickupTime = document.getElementById('pickup-time').value;
        const vehicleType = document.getElementById('vehicle-type').value;
        const vehicleName = document.getElementById('vehicle-name').value;
        const specialRequest = document.getElementById('special-request').value.trim();

        if (!name || !phone || !pickup || !dropoff || !pickupDate || !dropoffDate || !pickupTime) {
            showModalMessage('Please fill in all required fields.', 'error');
            return;
        }
        if (pickup === 'Other' && !customPickup) {
            showModalMessage('Please specify a custom pick-up location.', 'error');
            return;
        }
        if (dropoff === 'Other' && !customDropoff) {
            showModalMessage('Please specify a custom drop-off location.', 'error');
            return;
        }
        if (!iti.isValidNumber()) {
            showModalMessage('Please enter a valid phone number.', 'error');
            return;
        }
        if (new Date(pickupDate) > new Date(dropoffDate)) {
            showModalMessage('Drop-off date cannot be before pick-up date.', 'error');
            return;
        }

        this.submit();
    });

    document.getElementById('booking-modal-overlay')?.addEventListener('click', hideBookingModal);

    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function searchVehicles() {
        const loading = document.getElementById('loading');
        const grid = document.getElementById('vehiclesGrid');
        loading.style.display = 'flex';
        grid.style.opacity = '0.5';

        const type = document.getElementById('vehicleType').value;
        const capacity = document.getElementById('capacity').value;
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;

        if (start && end && new Date(start) > new Date(end)) {
            alert('End date cannot be before start date.');
            document.getElementById('endDate').value = '';
            loading.style.display = 'none';
            grid.style.opacity = '1';
            return;
        }

        const params = new URLSearchParams();
        if (type) params.set('vehicleType', type);
        if (capacity) params.set('capacity', capacity);
        if (start) params.set('startDate', start);
        if (end) params.set('endDate', end);
        window.location.search = params.toString();
    }

    function filterVehicles(type, capacity) {
        const cards = document.querySelectorAll('.vehicle-card');
        cards.forEach(card => {
            let show = true;
            if (type && card.dataset.type !== type) show = false;
            if (capacity) {
                const cap = parseInt(card.dataset.capacity);
                const [min, max] = capacity.split('-').map(Number);
                if (cap < min || cap > max) show = false;
            }
            card.style.display = show ? 'block' : 'none';
            if (show) card.style.animation = 'fadeInUp 0.5s ease';
        });
    }

    function checkAvailability(startDate, endDate) {
        const cards = document.querySelectorAll('.vehicle-card');
        cards.forEach(card => {
            const status = card.querySelector('.availability-status') || document.createElement('span');
            status.className = 'availability-status';
            card.appendChild(status);
            const available = Math.random() > 0.3; // Placeholder: replace with actual logic
            status.className = `availability-status ${available ? 'status-available' : 'status-unavailable'}`;
            status.innerHTML = available
                ? '<i class="fas fa-check-circle"></i> Available'
                : '<i class="fas fa-times-circle"></i> Unavailable';
            const btn = card.querySelector('.book-btn');
            btn.disabled = !available;
            btn.textContent = available ? 'Book Now' : 'Not Available';
        });
    }

    function clearFilters() {
        ['vehicleType', 'capacity', 'startDate', 'endDate'].forEach(id => document.getElementById(id).value = '');
        window.location.search = '';
    }

    function bookVehicle(type, name) {
        <?php if (!isset($_SESSION['username']) || empty($_SESSION['username'])): ?>
            showLoginMessage('Please log in to create booking.');
            return;
        <?php endif; ?>
        // Always get the current values from the filter section
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const start = startDateInput ? startDateInput.value : '';
        const end = endDateInput ? endDateInput.value : '';

        if (!start || !end) {
            showDateSelectionMessage('Please select your travel dates first!');
            return;
        }

        // Set modal date fields before showing modal
        const pickupDateInput = document.getElementById('pickup-date');
        const dropoffDateInput = document.getElementById('dropoff-date');
        if (pickupDateInput) pickupDateInput.value = start;
        if (dropoffDateInput) dropoffDateInput.value = end;

        showBookingModal(`Book ${name}`, type, name);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Restore filter dates from URL params if present
        const urlParams = new URLSearchParams(window.location.search);
        const startDate = urlParams.get('startDate');
        const endDate = urlParams.get('endDate');
        if (startDate) document.getElementById('startDate').value = startDate;
        if (endDate) document.getElementById('endDate').value = endDate;

        initializeAutocomplete();
    });

    function initializeAutocomplete() {
        const locations = [
            'Colombo Airport',
            'Colombo City',
            'Mattala Airport',
            'Tissamaharama',
            'Yala',
            'Ranna',
            'Tangalle',
            'Matara',
            'Mirissa',
            'Weligama',
            'Galle',
            'Unawatuna',
            'Hikkaduwa',
            'Sigiriya',
            'Kalpitiya',
            'Bentota',
            'Arugam bay',
            'Ella',
            'Haputale',
            'Trincomalee',
            'Kandy',
            'Kataragama',
            'Pasikuda/Kalkuda',
            'Udawalawe',
            'Nuwara Eliya',
            'Negombo',
            'Other'
        ];

        setupAutocomplete('pickup-location-input', 'pickup-dropdown', 'pickup-location-hidden', 'custom-pickup-location', locations);
        setupAutocomplete('dropoff-location-input', 'dropoff-dropdown', 'dropoff-location-hidden', 'custom-dropoff-location', locations);
    }

    function setupAutocomplete(inputId, dropdownId, hiddenInputId, customLocationId, locations) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenInputId);
        const customLocation = document.getElementById(customLocationId);
        
        if (!input || !dropdown || !hiddenInput || !customLocation) return;

        let selectedIndex = -1;
        let filteredLocations = [];

        input.addEventListener('input', function() {
            const value = this.value.trim();
            hiddenInput.value = '';
            
            if (value.length === 0) {
                hideDropdown();
                customLocation.style.display = 'none';
                return;
            }

            filteredLocations = locations.filter(location => 
                location.toLowerCase().includes(value.toLowerCase())
            );

            if (filteredLocations.length === 0) {
                dropdown.innerHTML = '<div class="autocomplete-no-results">No locations found</div>';
                dropdown.classList.add('show');
            } else {
                displaySuggestions(filteredLocations);
            }
            
            selectedIndex = -1;
        });

        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.autocomplete-item');
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    updateSelection(items);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection(items);
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && items[selectedIndex]) {
                        selectLocation(items[selectedIndex].textContent);
                    }
                    break;
                case 'Escape':
                    hideDropdown();
                    break;
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(() => {
                hideDropdown();
            }, 200);
        });

        input.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                const value = this.value.trim();
                filteredLocations = locations.filter(location => 
                    location.toLowerCase().includes(value.toLowerCase())
                );
                if (filteredLocations.length > 0) {
                    displaySuggestions(filteredLocations);
                }
            }
        });

        function displaySuggestions(suggestions) {
            dropdown.innerHTML = '';
            suggestions.forEach((location, index) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.textContent = location;
                
                item.addEventListener('click', function() {
                    selectLocation(location);
                });
                
                dropdown.appendChild(item);
            });
            dropdown.classList.add('show');
        }

        function selectLocation(location) {
            input.value = location;
            hiddenInput.value = location;
            hideDropdown();
            
            if (location === 'Other') {
                customLocation.style.display = 'block';
                customLocation.focus();
            } else {
                customLocation.style.display = 'none';
                customLocation.value = '';
            }
            
            selectedIndex = -1;
        }

        function updateSelection(items) {
            items.forEach((item, index) => {
                item.classList.toggle('highlighted', index === selectedIndex);
            });
        }

        function hideDropdown() {
            dropdown.classList.remove('show');
            selectedIndex = -1;
        }

        customLocation.addEventListener('input', function() {
            if (this.value.trim()) {
                hiddenInput.value = 'Other';
            }
        });
    }
    </script>
</body>
</html>
