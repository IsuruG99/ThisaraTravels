<?php
session_start(); // Start the session

// MongoDB connection
require 'vendor/autoload.php'; // Include MongoDB client
use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
try {
    $client = new MongoDB\Client($uri);
    $db = $client->ThisaraTravels;
    $bookingsCollection = $db->bookings;
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $_SESSION['booking_message'] = ['text' => 'Database connection failed.', 'type' => 'error'];
    header('Location: booking.php');
    exit;
}

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['booking_message'] = ['text' => 'Invalid CSRF token.', 'type' => 'error'];
        header('Location: booking.php');
        exit;
    }

    if (isset($_SESSION['pending_booking'])) {
        try {
            $result = $bookingsCollection->insertOne($_SESSION['pending_booking']);
            
            if ($result->getInsertedCount() > 0) {
                $_SESSION['booking_message'] = [
                    'text' => 'Booking confirmed successfully! Redirecting...',
                    'type' => 'success'
                ];
                unset($_SESSION['pending_booking']);
            } else {
                $_SESSION['booking_message'] = [
                    'text' => 'Failed to confirm booking.',
                    'type' => 'error'
                ];
            }
        } catch (Exception $e) {
            error_log("Error confirming booking: " . $e->getMessage());
            $_SESSION['booking_message'] = [
                'text' => 'Error confirming booking: ' . $e->getMessage(),
                'type' => 'error'
            ];
        }
    } else {
        $_SESSION['booking_message'] = [
            'text' => 'No booking data to confirm.',
            'type' => 'error'
        ];
    }
}

// Handle booking cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['booking_message'] = ['text' => 'Invalid CSRF token.', 'type' => 'error'];
        header('Location: booking.php');
        exit;
    }

    unset($_SESSION['pending_booking']);
    $_SESSION['booking_message'] = [
        'text' => 'Booking cancelled. Redirecting...',
        'type' => 'info'
    ];
}

// Check if there is pending booking data
if (!isset($_SESSION['pending_booking']) && !isset($_SESSION['booking_message'])) {
    $_SESSION['booking_message'] = [
        'text' => 'No booking data to confirm.',
        'type' => 'error'
    ];
    header('Location: booking.php');
    exit;
}

$bookingData = $_SESSION['pending_booking'] ?? [];
$booking_message = $_SESSION['booking_message'] ?? null;
unset($_SESSION['booking_message']);

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
    <title>Confirm Your Booking - Thisara Travels & Tours</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #174038 0%, #2F6DA3 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header (Nav Bar) */
        .header {
            background: rgba(34, 83, 73, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }

        .logo {
            color: #F8F1E9;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links li {
            position: relative;
        }

        .nav-links a {
            color: #F8F1E9;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .nav-links a:hover, .nav-links a:focus {
            color: #6CC4A1;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #6CC4A1;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after, .nav-links a:focus::after {
            width: 100%;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #E6F0EA;
            border: 1px solid #224B41;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            min-width: 150px;
            z-index: 1000;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .dropdown:hover .dropdown-menu, .dropdown:focus-within .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-menu li {
            list-style: none;
        }

        .dropdown-menu a {
            color: #224B41;
            padding: 0.8rem 1rem;
            display: block;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .dropdown-menu a:hover, .dropdown-menu a:focus {
            background: #5AB896;
            color: #F8F1E9;
            transform: scale(1.02);
        }

        .dropdown-icon {
            color: #F8F1E9;
            transition: transform 0.3s ease, scale 0.3s ease;
        }

        .dropdown:hover .dropdown-icon, .dropdown:focus-within .dropdown-icon {
            transform: rotate(180deg);
            scale: 1.1;
            filter: drop-shadow(0 0 5px #6CC4A1);
        }

        /* Main Container */
        .container {
            max-width: 800px;
            margin: 1.5rem auto;
            padding: 1rem;
        }

        .page-title {
            text-align: center;
            color: #F8F1E9;
            margin-bottom: 1.5rem;
            animation: fadeInDown 0.8s ease;
        }

        .page-title h1 {
            font-size: 1.8rem;
            margin-bottom: 0.3rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .page-title p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Confirmation Section */
        .confirmation-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 12px;
            padding: 1.2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            backdrop-filter: blur(8px);
            color: #224B41;
            animation: fadeInUp 0.8s ease;
            max-width: 450px;
            margin: 0 auto;
        }

        .confirmation-section h2 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .booking-details {
            margin-bottom: 1.2rem;
        }

        .booking-details p {
            font-size: 0.85rem;
            margin: 0.3rem 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid rgba(34, 83, 73, 0.2);
            padding-bottom: 0.3rem;
            flex-wrap: wrap;
        }

        .booking-details p strong {
            font-weight: 600;
            color: #174038;
            flex: 1;
            min-width: 100px;
        }

        .booking-details p span {
            flex: 2;
            text-align: right;
        }

        .confirmation-buttons {
            display: flex;
            gap: 0.6rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            width: 100%;
            justify-content: center;
        }

        .btn-confirm {
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
        }

        .btn-confirm:hover, .btn-confirm:focus {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(47, 109, 163, 0.3);
        }

        .btn-cancel {
            background: #dc3545;
            color: #F8F1E9;
        }

        .btn-cancel:hover, .btn-cancel:focus {
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(220, 53, 69, 0.3);
        }

        .message {
            margin-bottom: 0.6rem;
            padding: 0.6rem;
            border-radius: 5px;
            font-size: 0.85rem;
            text-align: center;
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .message.success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .message.error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .message.info {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
            border-left: 4px solid #007bff;
        }

        .message.show {
            display: block;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1.5rem;
                flex-wrap: wrap;
            }

            .nav-links {
                gap: 1.5rem;
            }

            .nav-links li {
                width: auto;
            }

            .nav-links a {
                font-size: 0.95rem;
            }

            .dropdown-menu {
                position: absolute;
                min-width: 140px;
            }

            .container {
                padding: 0.8rem;
                margin: 1rem auto;
            }

            .page-title h1 {
                font-size: 1.6rem;
            }

            .page-title p {
                font-size: 0.95rem;
            }

            .confirmation-section {
                padding: 1rem;
                max-width: 90%;
            }

            .confirmation-section h2 {
                font-size: 1.2rem;
            }

            .booking-details p {
                font-size: 0.8rem;
                flex-direction: column;
                gap: 0.2rem;
                align-items: flex-start;
            }

            .booking-details p strong {
                min-width: 100%;
            }

            .booking-details p span {
                text-align: left;
            }

            .confirmation-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                padding: 7px 16px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .nav-container {
                padding: 0 1rem;
            }

            .logo {
                font-size: 1.6rem;
            }

            .nav-links {
                gap: 1.2rem;
            }

            .nav-links a {
                font-size: 0.9rem;
            }

            .page-title h1 {
                font-size: 1.4rem;
            }

            .page-title p {
                font-size: 0.85rem;
            }

            .confirmation-section {
                padding: 0.8rem;
            }

            .confirmation-section h2 {
                font-size: 1.1rem;
            }

            .booking-details p {
                font-size: 0.75rem;
            }

            .btn {
                padding: 6px 14px;
                font-size: 0.75rem;
            }
        }
    </style>
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

    <!-- Main Container -->
    <div class="container" role="main">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-calendar-check" aria-hidden="true"></i> Confirm Your Booking</h1>
            <p>Please review your booking details below</p>
        </div>

        <!-- Confirmation Section -->
        <div class="confirmation-section" role="region" aria-labelledby="confirmation-heading">
            <h2 id="confirmation-heading">Booking Details</h2>
            <?php if (isset($booking_message)): ?>
                <div class="message <?php echo htmlspecialchars($booking_message['type']); ?> show" aria-live="polite">
                    <?php echo htmlspecialchars($booking_message['text']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($bookingData)): ?>
                <div class="booking-details">
                    <p><strong>Username:</strong> <span><?php echo htmlspecialchars($bookingData['username'] ?? 'Guest'); ?></span></p>
                    <p><strong>Email:</strong> <span><?php echo htmlspecialchars($bookingData['email'] ?? 'Not provided'); ?></span></p>
                    <p><strong>Vehicle:</strong> <span><?php echo htmlspecialchars($bookingData['vehicle_name'] ?? ''); ?></span></p>
                    <p><strong>Name:</strong> <span><?php echo htmlspecialchars($bookingData['name'] ?? ''); ?></span></p>
                    <p><strong>WhatsApp Number:</strong> <span><?php echo htmlspecialchars($bookingData['phone'] ?? ''); ?></span></p>
                    <p><strong>Pick-Up Location:</strong> <span><?php echo htmlspecialchars($bookingData['pickup_location'] . ($bookingData['custom_pickup_location'] ? ' (' . $bookingData['custom_pickup_location'] . ')' : '')); ?></span></p>
                    <p><strong>Drop-Off Location:</strong> <span><?php echo htmlspecialchars($bookingData['dropoff_location'] . ($bookingData['custom_dropoff_location'] ? ' (' . $bookingData['custom_dropoff_location'] . ')' : '')); ?></span></p>
                    <p><strong>Pickup Date:</strong> <span><?php echo htmlspecialchars($bookingData['pickup_date'] ?? ''); ?></span></p>
                    <p><strong>Drop-Off Date:</strong> <span><?php echo htmlspecialchars($bookingData['dropoff_date'] ?? ''); ?></span></p>
                    <p><strong>Pickup Time:</strong> <span><?php echo htmlspecialchars($bookingData['pickup_time'] ?? ''); ?></span></p>
                    <p><strong>Special Request:</strong> <span><?php echo htmlspecialchars($bookingData['Special_Request'] ?? 'None'); ?></span></p>
                </div>
                <div class="confirmation-buttons">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="action" value="confirm_booking">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="btn btn-confirm" aria-label="Confirm booking"><i class="fas fa-check" aria-hidden="true"></i> Confirm Booking</button>
                    </form>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <input type="hidden" name="action" value="cancel_booking">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="btn btn-cancel" aria-label="Cancel booking"><i class="fas fa-times" aria-hidden="true"></i> Cancel Booking</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Auto-redirect after showing message
        document.addEventListener('DOMContentLoaded', function () {
            const message = document.querySelector('.message.show');
            if (message) {
                setTimeout(function () {
                    window.location.href = 'booking.php';
                }, 2000); // 2-second delay
            }
        });
    </script>
</body>
</html>