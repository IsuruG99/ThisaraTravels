<?php
session_start(); // Start the session

// MongoDB connection
require 'vendor/autoload.php'; // Include MongoDB client

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
try {
    $client = new MongoDB\Client($uri);
    $db = $client->ThisaraTravels;
    $usersCollection = $db->users;
    $settingsCollection = $db->settings;
    $bookingsCollection = $db->bookings; // New bookings collection

    // Fetch maintenance mode setting
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

// Handle session messages
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$success = isset($_SESSION['success']) ? $_SESSION['success'] : [];

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
            position: relative;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .nav-links a:hover {
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

        .nav-links a:hover::after {
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

        .dropdown:hover .dropdown-menu {
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

        .dropdown-menu a:hover {
            background: #5AB896;
            color: #F8F1E9;
            transform: scale(1.02);
        }

        .dropdown-icon {
            color: #F8F1E9;
            transition: transform 0.3s ease, scale 0.3s ease;
        }

        .dropdown:hover .dropdown-icon {
            transform: rotate(180deg);
            scale: 1.1;
            filter: drop-shadow(0 0 5px #6CC4A1);
        }

        /* Booking Modal */
        .booking-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .booking-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .booking-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(34, 83, 73, 0.95);
            color: #F8F1E9;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            z-index: 2000;
            max-width: 600px;
            width: 90%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            max-height: 80vh;
            overflow-y: auto;
        }

        .booking-modal.show {
            opacity: 1;
            visibility: visible;
            animation: fadeIn 0.3s ease;
        }

        .booking-modal h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }

        .booking-modal .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #224B41;
            color: #F8F1E9;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .booking-modal .close-btn:hover {
            background: #6CC4A1;
            transform: translateY(-2px);
        }

        .booking-modal .form-control, .booking-modal .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E6F0EA;
            border-radius: 10px;
            font-size: 1rem;
            background: #F8F1E9;
            color: #224B41;
            transition: all 0.3s ease;
        }

        .booking-modal .form-control:focus, .booking-modal .form-select:focus {
            outline: none;
            border-color: #2F6DA3;
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(47, 109, 163, 0.1);
        }

        .booking-modal .form-control.custom-location {
            margin-top: 0.5rem;
        }

        .custom-location {
            display: none;
        }

        .booking-modal textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .booking-modal .form-group {
            margin-bottom: 1rem;
        }

        .booking-modal .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #F8F1E9;
        }

        .booking-modal .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .booking-modal .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 109, 163, 0.3);
        }

        .booking-modal .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 5px;
            font-size: 1rem;
            text-align: center;
            display: none;
        }

        .booking-modal .message.success {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        .booking-modal .message.error {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .booking-modal .message.show {
            display: block;
        }

        /* Confirmation View */
        .confirmation-view {
            display: none;
            color: #F8F1E9;
        }

        .confirmation-view.show {
            display: block;
        }

        .confirmation-view h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .confirmation-view .booking-details {
            margin-bottom: 1.5rem;
        }

        .confirmation-view .booking-details p {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .confirmation-view .confirmation-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .confirmation-view .btn-confirm, .confirmation-view .btn-cancel {
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .confirmation-view .btn-confirm {
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
        }

        .confirmation-view .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 109, 163, 0.3);
        }

        .confirmation-view .btn-cancel {
            background: #dc3545;
            color: #F8F1E9;
        }

        .confirmation-view .btn-cancel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.3);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            text-align: center;
            color: #F8F1E9;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        .page-title h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Intro Section */
        .intro-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            position: relative;
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1rem;
            align-items: center;
            overflow: hidden;
        }

        .intro-section::before {
            content: '';
            position: absolute;
oul
            top: 0;
            left: -20%;
            width: 60%;
            height: 100%;
            background: linear-gradient(135deg, rgba(34, 83, 73, 0.5), transparent);
            border-radius: 50% 20% / 20% 50%;
            z-index: 0;
        }

        .intro-section .content {
            padding: 1rem;
            color: #224B41;
            position: relative;
            z-index: 1;
        }

        .intro-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            animation: slideInUp 0.8s ease 0.4s both;
        }

        .intro-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            animation: slideInUp 0.8s ease 0.6s both;
        }

        .intro-section .whatsapp-btn {
            animation: slideInUp 0.8s ease 0.8s both;
            position: relative;
            z-index: 1;
        }

        .intro-section .decorative-icon {
            font-size: 8rem;
            color: #2F6DA3;
            opacity: 0.8;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease 1.0s both;
            z-index: 1;
        }

        .intro-section .decorative-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px #6CC4A1);
        }

        /* Call-to-Action Section */
        .cta-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            position: relative;
            display: grid;
            grid-template-columns: 2fr 3fr;
            gap: 1rem;
            align-items: center;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: -20%;
            width: 60%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(34, 83, 73, 0.5));
            border-radius: 20% 50% / 50% 20%;
            z-index: 0;
        }

        .cta-section .content {
            padding: 1rem;
            color: #224B41;
            position: relative;
            z-index: 1;
        }

        .cta-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            animation: slideInUp 0.8s ease 0.6s both;
        }

        .cta-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            animation: slideInUp 0.8s ease 0.8s both;
        }

        .cta-section .whatsapp-btn {
            animation: slideInUp 0.8s ease 1.0s both;
            position: relative;
            z-index: 1;
        }

        .cta-section .decorative-icon {
            font-size: 8rem;
            color: #2F6DA3;
            opacity: 0.8;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease 1.2s both;
            z-index: 1;
        }

        .cta-section .decorative-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px #6CC4A1);
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 30px;
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(47, 109, 163, 0.3);
        }

        .whatsapp-btn::after {
            content: 'Contact us on WhatsApp';
            position: absolute;
            top: -2.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: #224B41;
            color: #F8F1E9;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .whatsapp-btn:hover::after {
            opacity: 1;
        }

        /* Filter Section */
        .filter-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: slideInUp 0.8s ease;
        }

        .filter-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .filter-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: #224B41;
        }

        .filter-title i {
            margin-right: 0.5rem;
            color: #2F6DA3;
        }

        .filter-grid {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: nowrap;
        }

        .filter-group {
            position: relative;
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E6F0EA;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #F8F1E9;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #2F6DA3;
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(47, 109, 163, 0.1);
        }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(47, 109, 163, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #2F6DA3;
            border: 2px solid #2F6DA3;
        }

        .btn-secondary:hover {
            background: #2F6DA3;
            color: #F8F1E9;
            transform: translateY(-2px);
        }

        /* Vehicles Grid */
        .vehicles-section {
            animation: fadeInUp 1s ease 0.3s both;
            margin-bottom: 2rem;
        }

        .section-title {
            color: #F8F1E9;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
        }

        .vehicle-card {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            transform: translateY(0);
            position: relative;
        }

        .vehicle-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }

        .vehicle-image {
            height: 120px;
            background: linear-gradient(45deg, #2F6DA3, #174038);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F8F1E9;
            font-size: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .vehicle-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(230,240,234,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .vehicle-card:hover .vehicle-image::before {
            animation: shine 0.6s ease;
        }

        .vehicle-info {
            padding: 0.8rem;
        }

        .vehicle-type {
            display: inline-block;
            background: linear-gradient(135deg, #2F6DA3, #174038);
            color: #F8F1E9;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .vehicle-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #224B41;
            margin-bottom: 0.4rem;
        }

        .vehicle-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 0.8rem 0;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            color: #666;
        }

        .feature i {
            color: #2F6DA3;
        }

        .vehicle-price {
            font-size: 0.9rem;
            font-weight: bold;
            color: #2F6DA3;
            margin: 0.8rem 0;
        }

        .availability-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
            font-size: 0.7rem;
        }

        .status-available {
            color: #28a745;
        }

        .status-unavailable {
            color: #dc3545;
        }

        .book-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .book-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 109, 163, 0.3);
        }

        .book-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            color: #F8F1E9;
            font-size: 1.2rem;
            margin: 2rem 0;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(230,240,234,0.3);
            border-radius: 50%;
            border-top-color: #F8F1E9;
            animation: spin 1s ease-in-out infinite;
            margin-right: 1rem;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
                margin-top: 1rem;
            }

            .nav-links li {
                width: 100%;
            }

            .dropdown-menu {
                position: static;
                box-shadow: none;
                background: rgba(230, 240, 234, 0.8);
                border: none;
                border-radius: 5px;
            }

            .dropdown:hover .dropdown-menu {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            .booking-modal {
                padding: 1.5rem;
                max-width: 90%;
            }

            .booking-modal h2 {
                font-size: 1.5rem;
            }

            .booking-modal .form-control, .booking-modal .form-select {
                font-size: 0.9rem;
                padding: 10px;
            }

            .booking-modal .btn-submit {
                font-size: 0.9rem;
                padding: 10px;
            }

            .booking-modal .close-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .booking-modal .message {
                font-size: 0.9rem;
                padding: 0.8rem;
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .intro-section, .cta-section {
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .intro-section .content, .cta-section .content {
                width: 100%;
            }

            .intro-section .decorative-icon, .cta-section .decorative-icon {
                font-size: 5rem;
                margin-bottom: 1rem;
            }

            .intro-section h2, .cta-section h2 {
                font-size: 1.5rem;
            }

            .intro-section p, .cta-section p {
                font-size: 1rem;
            }

            .whatsapp-btn {
                width: 100%;
                justify-content: center;
            }

            .intro-section::before, .cta-section::before {
                display: none;
            }

            .filter-grid {
                flex-direction: column;
                gap: 1rem;
            }

            .date-range {
                grid-template-columns: 1fr;
            }

            .vehicles-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .confirmation-view .confirmation-buttons {
                flex-direction: column;
            }
        }

        .whatsapp-header {
            text-align: center;
            margin-bottom: 30px;
            color: #25D366;
        }

        .whatsapp-header i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .btn-whatsapp {
            background: #25D366;
            border: none;
            border-radius: 0.375rem;
            padding: 12px 20px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-whatsapp:hover {
            background: #128C7E;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
            color: white;
        }

        .validation-message {
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .valid {
            color: #28a745;
        }

        .invalid {
            color: #dc3545;
        }

        .phone-info {
            background: white;
            padding: 15px;
            border-radius: 0.375rem;
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }

        .phone-info h6 {
            color: #495057;
            margin-bottom: 10px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
        }

        .info-value {
            color: #495057;
        }

        .search-hint {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Container wrapper for the phone input */
        .iti {
            width: 100%;
        }

        #phone {
            padding-left: 68px !important;
            height: 45px;
            font-size: 1rem;
            width: 100%;
            border: 2px solid #E6F0EA;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #F8F1E9;
            color: #224B41;
        }

        #phone:focus {
            border-color: #2F6DA3;
            outline: none;
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(47, 109, 163, 0.1);
        }

        .iti__flag-container {
            background-color: #F8F1E9;
            border: 2px solid #E6F0EA;
            border-right: none;
            border-radius: 10px 0 0 10px;
            height: 45px;
            z-index: 2001;
            margin-right: 10px;
        }

        .iti__country-list {
            position: absolute;
            top: 100%;
            left: 0;
            width: 300px;
            max-height: 220px;
            overflow-y: auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 15px;
            z-index: 2002 !important;
            background: #F8F1E9;
            border: 2px solid #E6F0EA;
        }

        .iti__country {
            padding: 8px 12px;
            transition: background 0.3s;
            color: #224B41;
        }

        .iti__country:hover {
            background-color: #E6F0EA;
        }

        .iti__country.iti__highlight {
            background-color: #2F6DA3;
            color: #F8F1E9;
        }

        .iti__selected-flag {
            height: 45px;
            display: flex;
            align-items: center;
        }

        .iti--separate-dial-code.iti--show-flags .iti__selected-dial-code {
            margin-left: 0 !important;
        }

        .iti__selected-flag {
            padding: 0px !important;
        }

        .iti__search-input {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #E6F0EA;
            margin: 5px 10px;
            width: calc(100% - 20px);
            background: #F8F1E9;
            color: #224B41;
        }

        .dropdown-toggle::after {
    display: none !important;
}

    </style>
</head>
<body>
    <!-- Header (Nav Bar) -->
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i> Thisara Travels
            </a>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="service.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-file-alt"></i> Page
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="testimonial.php"><i class="fas fa-star"></i> Reviews</a></li>
                        <li><a href="booking.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    </ul>
                </li>
                <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
                <li>
                    <?php
                    if (!isset($_SESSION['username'])) {
                        echo '<a href="auth.php"><i class="fas fa-user"></i> Login</a>';
                    } else {
                        $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                        $user_role = $_SESSION['role'] ?? 'user';
                        $redirect_url = ($user_role === 'admin') ? 'admindashboard.php' : 'profile-page.php';
                        echo '<a href="' . $redirect_url . '"><i class="fas fa-user"></i> Profile</a>';
                    }
                    ?>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Booking Modal -->
    <div id="booking-modal-overlay" class="booking-modal-overlay"></div>
    <div id="booking-modal" class="booking-modal">
        <button class="close-btn" onclick="hideBookingModal()">Close</button>
        <h2 id="booking-modal-title">Book Your Vehicle</h2>
        <div id="booking-message" class="message"></div>
        <form id="booking-form" method="post">
            <input type="hidden" name="vehicle_type" id="vehicle-type">
            <input type="hidden" name="vehicle_name" id="vehicle-name">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Your Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
            </div>
            <div class="form-group">
                <label for="phone"><i class="fab fa-whatsapp"></i> WhatsApp Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="Enter your WhatsApp number" required>
                <div class="search-hint">
                    <i class="fas fa-search"></i> You can search countries by name or dial code
                </div>
                <div id="validation-message" class="validation-message"></div>
            </div>
            <div class="form-group">
                <label for="pickup-location-select"><i class="fas fa-map-marker-alt"></i> Pick-Up Location</label>
                <select class="form-select" id="pickup-location-select" name="pickup_location" required>
                    <option value="" disabled selected>PICK-UP LOCATION</option>
                    <option value="Colombo Airport">Colombo Airport</option>
                    <option value="Colombo City">Colombo City</option>
                    <option value="Mattala Airport">Mattala Airport</option>
                    <option value="Tissamaharama">Tissamaharama</option>
                    <option value="Yala">Yala</option>
                    <option value="Ranna">Ranna</option>
                    <option value="Tangalle">Tangalle</option>
                    <option value="Matara">Matara</option>
                    <option value="Mirissa">Mirissa</option>
                    <option value="Weligama">Weligama</option>
                    <option value="Galle">Galle</option>
                    <option value="Unawatuna">Unawatuna</option>
                    <option value="Hikkaduwa">Hikkaduwa</option>
                    <option value="Sigiriyaa">Sigiriyaa</option>
                    <option value="Kalpitiya">Kalpitiya</option>
                    <option value="Bentota">Bentota</option>
                    <option value="Arugam bay">Arugam bay</option>
                    <option value="Ella">Ella</option>
                    <option value="Haputale">Haputale</option>
                    <option value="Trincomalee">Trincomalee</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Kataragama">Kataragama</option>
                    <option value="Pasikuda/Kalkuda">Pasikuda/Kalkuda</option>
                    <option value="Udawalawe">Udawalawe</option>
                    <option value="Nuwara Eliya">Nuwara Eliya</option>
                    <option value="Negombo">Negombo</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" class="form-control custom-location" id="custom-pickup-location" name="custom_pickup_location" placeholder="Custom Pick-Up Location">
            </div>
            <div class="form-group">
                <label for="dropoff-location-select"><i class="fas fa-map-marker-alt"></i> Drop-Off Location</label>
                <select class="form-select" id="dropoff-location-select" name="dropoff_location" required>
                    <option value="" disabled selected>DROP-OFF LOCATION</option>
                    <option value="Colombo Airport">Colombo Airport</option>
                    <option value="Colombo City">Colombo City</option>
                    <option value="Mattala Airport">Mattala Airport</option>
                    <option value="Tissamaharama">Tissamaharama</option>
                    <option value="Yala">Yala</option>
                    <option value="Ranna">Ranna</option>
                    <option value="Tangalle">Tangalle</option>
                    <option value="Matara">Matara</option>
                    <option value="Mirissa">Mirissa</option>
                    <option value="Weligama">Weligama</option>
                    <option value="Galle">Galle</option>
                    <option value="Unawatuna">Unawatuna</option>
                    <option value="Hikkaduwa">Hikkaduwa</option>
                    <option value="Sigiriyaa">Sigiriyaa</option>
                    <option value="Kalpitiya">Kalpitiya</option>
                    <option value="Bentota">Bentota</option>
                    <option value="Arugam bay">Arugam bay</option>
                    <option value="Ella">Ella</option>
                    <option value="Haputale">Haputale</option>
                    <option value="Trincomalee">Trincomalee</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Kataragama">Kataragama</option>
                    <option value="Pasikuda/Kalkuda">Pasikuda/Kalkuda</option>
                    <option value="Udawalawe">Udawalawe</option>
                    <option value="Nuwara Eliya">Nuwara Eliya</option>
                    <option value="Negombo">Negombo</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" class="form-control custom-location" id="custom-dropoff-location" name="custom_dropoff_location" placeholder="Custom Drop-Off Location">
            </div>
            <div class="form-group">
                <label for="pickup-date"><i class="fas fa-calendar"></i> Pickup Date</label>
                <input type="date" class="form-control" id="pickup-date" name="pickup_date" required>
            </div>
            <div class="form-group">
                <label for="dropoff-date"><i class="fas fa-calendar"></i> Drop-Off Date</label>
                <input type="date" class="form-control" id="dropoff-date" name="dropoff_date" required>
            </div>
            <div class="form-group">
                <label for="pickup-time"><i class="fas fa-clock"></i> Pickup Time</label>
                <input type="time" class="form-control" id="pickup-time" name="pickup_time" required>
            </div>
            <div class="form-group">
                <label for="special-request"><i class="fas fa-comment"></i> Special Request</label>
                <textarea class="form-control" id="special-request" name="Special_Request" placeholder="Special Request"></textarea>
            </div>
            <div class="form-group">
                <div class="g-recaptcha" data-sitekey="YOUR_GOOGLE_RECAPTCHA_SITE_KEY"></div>
            </div>
            <button class="btn-submit" type="submit">Book Now</button>
        </form>
        <div id="confirmation-view" class="confirmation-view">
            <h3>Confirm Your Booking</h3>
            <div id="booking-details" class="booking-details"></div>
            <div class="confirmation-buttons">
                <button class="btn-confirm" onclick="confirmBooking()">Confirm</button>
                <button class="btn-cancel" onclick="cancelBooking()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-calendar-alt"></i> Book Your Journey</h1>
            <p>Choose your perfect vehicle and travel dates</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h2 class="filter-title">
                <i class="fas fa-filter"></i>
                Search & Filter Options
            </h2>
            
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="vehicleType"><i class="fas fa-car"></i> Vehicle Type</label>
                    <select id="vehicleType" class="filter-select">
                        <option value="">All Vehicles</option>
                        <option value="kdh">KDH Van</option>
                        <option value="dolphin">Dolphin</option>
                        <option value="car">Car</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="capacity"><i class="fas fa-users"></i> Passenger Capacity</label>
                    <select id="capacity" class="filter-select">
                        <option value="">Any Capacity</option>
                        <option value="1-4">1-4 Passengers</option>
                        <option value="5-8">5-8 Passengers</option>
                        <option value="9-15">9-15 Passengers</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Date Range</label>
                    <div class="date-range">
                        <input type="date" id="startDate" class="filter-input">
                        <input type="date" id="endDate" class="filter-input">
                    </div>
                </div>
            </div>

            <div class="filter-buttons">
                <button class="btn btn-primary" onclick="searchVehicles()">
                    <i class="fas fa-search"></i> Search Vehicles
                </button>
                <button class="btn btn-secondary" onclick="clearFilters()">
                    <i class="fas fa-undo"></i> Clear Filters
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            Searching available vehicles...
        </div>

        <!-- Vehicles Section -->
        <div class="vehicles-section">
            <h2 class="section-title">Available Vehicles</h2>
            
            <div class="vehicles-grid" id="vehiclesGrid">
                <!-- KDH Van -->
                <div class="vehicle-card" data-type="kdh" data-capacity="15" data-price="80">
                    <div class="vehicle-image">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">KDH VAN</span>
                        <h3 class="vehicle-name">Toyota KDH Van</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 15 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-wifi"></i> WiFi</span>
                            <span class="feature"><i class="fas fa-music"></i> Music System</span>
                        </div>
                        <div class="vehicle-price">$80 / Day</div>
                        <div class="availability-status status-available">
                            <i class="fas fa-check-circle"></i> Available
                        </div>
                        <button class="book-btn" onclick="bookVehicle('kdh', 'Toyota KDH Van')">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </button>
                    </div>
                </div>

                <!-- Dolphin -->
                <div class="vehicle-card" data-type="dolphin" data-capacity="8" data-price="60">
                    <div class="vehicle-image">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">DOLPHIN</span>
                        <h3 class="vehicle-name">Nissan Dolphin</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 8 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-suitcase"></i> Luggage Space</span>
                            <span class="feature"><i class="fas fa-shield-alt"></i> Safety Features</span>
                        </div>
                        <div class="vehicle-price">$60 / Day</div>
                        <div class="availability-status status-available">
                            <i class="fas fa-check-circle"></i> Available
                        </div>
                        <button class="book-btn" onclick="bookVehicle('dolphin', 'Nissan Dolphin')">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </button>
                    </div>
                </div>

                <!-- Car -->
                <div class="vehicle-card" data-type="car" data-capacity="4" data-price="40">
                    <div class="vehicle-image">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">CAR</span>
                        <h3 class="vehicle-name">Toyota Sedan</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 4 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-gas-pump"></i> Fuel Efficient</span>
                            <span class="feature"><i class="fas fa-road"></i> City Travel</span>
                        </div>
                        <div class="vehicle-price">$40 / Day</div>
                        <div class="availability-status status-unavailable">
                            <i class="fas fa-times-circle"></i> Unavailable
                        </div>
                        <button class="book-btn" disabled>
                            <i class="fas fa-ban"></i> Not Available
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Intro Section -->
        <section class="intro-section">
            <div class="content">
                <h2>Seamless Travel Awaits: Book Your Adventure Today!</h2>
                <p>At Thisara Travels and Tours, we make it easy for you to embark on your journey through the enchanting landscapes of Sri Lanka. Our user-friendly online booking system allows you to reserve your travel arrangements with just a few clicks. Choose from a wide selection of vehicles tailored to your needs, and enjoy the flexibility of customizing your pick-up and drop-off locations. Start your unforgettable experience today—your Sri Lankan adventure is just a click away!</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i> Contact Us (+94702180024)
                </a>
            </div>
            <div class="decorative-icon">
                <i class="fas fa-map"></i>
            </div>
        </section>

        <!-- Call-to-Action Section -->
        <section class="cta-section">
            <div class="decorative-icon">
                <i class="fas fa-route"></i>
            </div>
            <div class="content">
                <h2>Have Any Pre Booking Question?</h2>
                <p>Experience luxurious comfort and convenience with Thisara Travels & Tours's top-tier car booking service. Our spacious vehicles, accommodating up to 6 passengers without a driver, ensure a smooth journey to and from your desired destinations. Travel in style while enjoying the scenic beauty of Srilanka, all in the company of Texi's reliable and professional team.</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i> Contact Us (+94702180024)
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
            const validationMessage = document.getElementById('validation-message');
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
        let bookingData = null;

        function showBookingModal(title, type, name) {
            const modal = document.getElementById('booking-modal');
            const overlay = document.getElementById('booking-modal-overlay');
            const modalTitle = document.getElementById('booking-modal-title');
            const vehicleTypeInput = document.getElementById('vehicle-type');
            const vehicleNameInput = document.getElementById('vehicle-name');
            const message = document.getElementById('booking-message');
            const form = document.getElementById('booking-form');
            const confirmationView = document.getElementById('confirmation-view');
            const pickupSelect = document.getElementById('pickup-location-select');
            const dropoffSelect = document.getElementById('dropoff-location-select');
            const customPickup = document.getElementById('custom-pickup-location');
            const customDropoff = document.getElementById('custom-dropoff-location');

            modalTitle.textContent = title;
            vehicleTypeInput.value = type;
            vehicleNameInput.value = name;
            message.style.display = 'none';
            message.className = 'message';
            form.style.display = 'block';
            confirmationView.classList.remove('show');

            // Reset form
            form.reset();
            pickupSelect.value = '';
            dropoffSelect.value = '';
            customPickup.style.display = 'none';
            customPickup.required = false;
            customPickup.value = '';
            customDropoff.style.display = 'none';
            customDropoff.required = false;
            customDropoff.value = '';

            // Pre-fill dates
            const startDate = document.getElementById('startDate')?.value;
            const endDate = document.getElementById('endDate')?.value;
            if (startDate) document.getElementById('pickup-date').value = startDate;
            if (endDate) document.getElementById('dropoff-date').value = endDate;

            modal.classList.add('show');
            overlay.classList.add('show');
        }

        function hideBookingModal() {
            document.getElementById('booking-modal').classList.remove('show');
            document.getElementById('booking-modal-overlay').classList.remove('show');
            const msg = document.getElementById('booking-message');
            msg.style.display = 'none';
            msg.className = 'message';
            document.getElementById('booking-form').style.display = 'block';
            document.getElementById('confirmation-view').classList.remove('show');
            bookingData = null;
        }

        function showModalMessage(text, type) {
            const message = document.getElementById('booking-message');
            const form = document.getElementById('booking-form');
            const confirmationView = document.getElementById('confirmation-view');
            message.textContent = text;
            message.className = `message ${type} show`;
            form.style.display = 'none';
            confirmationView.classList.remove('show');
            if (type === 'success') setTimeout(hideBookingModal, 3000);
        }

        function showConfirmationView(data) {
            const form = document.getElementById('booking-form');
            const confirmationView = document.getElementById('confirmation-view');
            const bookingDetails = document.getElementById('booking-details');
            const modalTitle = document.getElementById('booking-modal-title');

            modalTitle.textContent = 'Confirm Your Booking';
            bookingDetails.innerHTML = `
                <p><strong>Vehicle:</strong> ${data.vehicle_name}</p>
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>WhatsApp Number:</strong> ${data.phone}</p>
                <p><strong>Pick-Up Location:</strong> ${data.pickup_location}${data.custom_pickup_location ? ` (${data.custom_pickup_location})` : ''}</p>
                <p><strong>Drop-Off Location:</strong> ${data.dropoff_location}${data.custom_dropoff_location ? ` (${data.custom_dropoff_location})` : ''}</p>
                <p><strong>Pickup Date:</strong> ${data.pickup_date}</p>
                <p><strong>Drop-Off Date:</strong> ${data.dropoff_date}</p>
                <p><strong>Pickup Time:</strong> ${data.pickup_time}</p>
                <p><strong>Special Request:</strong> ${data.Special_Request || 'None'}</p>
            `;
            form.style.display = 'none';
            confirmationView.classList.add('show');
        }

        // --- Custom location toggle ---
        document.getElementById('pickup-location-select')?.addEventListener('change', function () {
            const custom = document.getElementById('custom-pickup-location');
            if (this.value === 'Other') {
                custom.style.display = 'block';
                custom.required = true;
            } else {
                custom.style.display = 'none';
                custom.required = false;
                custom.value = '';
            }
        });

        document.getElementById('dropoff-location-select')?.addEventListener('change', function () {
            const custom = document.getElementById('custom-dropoff-location');
            if (this.value === 'Other') {
                custom.style.display = 'block';
                custom.required = true;
            } else {
                custom.style.display = 'none';
                custom.required = false;
                custom.value = '';
            }
        });

        // --- Booking Form Submit ---
        document.getElementById('booking-form')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value.trim();
            const phone = iti.getNumber();
            const pickup = document.getElementById('pickup-location-select').value;
            const dropoff = document.getElementById('dropoff-location-select').value;
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

            // Prepare data for confirmation
            bookingData = {
                vehicle_type: vehicleType,
                vehicle_name: vehicleName,
                name: name,
                phone: phone,
                pickup_location: pickup,
                dropoff_location: dropoff,
                custom_pickup_location: customPickup,
                custom_dropoff_location: customDropoff,
                pickup_date: pickupDate,
                dropoff_date: dropoffDate,
                pickup_time: pickupTime,
                Special_Request: specialRequest
            };

            try {
                const response = await fetch('process_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                });
                const result = await response.json();

                if (result.success) {
                    showConfirmationView(bookingData);
                } else {
                    showModalMessage(result.error || 'Failed to process booking.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showModalMessage('An error occurred while processing your booking.', 'error');
            }
        });

        async function confirmBooking() {
            if (!bookingData) {
                showModalMessage('No booking data to confirm.', 'error');
                return;
            }

            try {
                const response = await fetch('process_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ...bookingData, confirm: true })
                });
                const result = await response.json();

                if (result.success) {
                    showModalMessage(`Booking for ${bookingData.vehicle_name} confirmed successfully!`, 'success');
                    bookingData = null;
                } else {
                    showModalMessage(result.error || 'Failed to confirm booking.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showModalMessage('An error occurred while confirming your booking.', 'error');
            }
        }

        function cancelBooking() {
            const form = document.getElementById('booking-form');
            const confirmationView = document.getElementById('confirmation-view');
            form.style.display = 'block';
            confirmationView.classList.remove('show');
            bookingData = null;
        }

        // --- Overlay click to close ---
        document.getElementById('booking-modal-overlay')?.addEventListener('click', hideBookingModal);

        // --- Smooth Scroll ---
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // --- Booking Filter/Vehicle Logic ---
        function searchVehicles() {
            const loading = document.getElementById('loading');
            const grid = document.getElementById('vehiclesGrid');
            loading.style.display = 'block';
            grid.style.opacity = '0.5';

            const type = document.getElementById('vehicleType').value;
            const capacity = document.getElementById('capacity').value;
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            setTimeout(() => {
                filterVehicles(type, capacity);
                checkAvailability(start, end);
                loading.style.display = 'none';
                grid.style.opacity = '1';
            }, 1500);
        }

        function filterVehicles(type, capacity) {
            const cards = document.querySelectorAll('.vehicle-card');
            cards.forEach(card => {
                let show = true;
                if (type && card.dataset.type !== type) show = false;
                if (capacity) {
                    const cap = parseInt(card.dataset.capacity);
                    const [min, max] = capacity.split('-').map(n => parseInt(n.replace('+', '')));
                    if (capacity.includes('+')) {
                        if (cap < min) show = false;
                    } else {
                        if (cap < min || cap > max) show = false;
                    }
                }
                card.style.display = show ? 'block' : 'none';
                if (show) card.style.animation = 'fadeInUp 0.5s ease';
            });
        }

        function checkAvailability(startDate, endDate) {
            const cards = document.querySelectorAll('.vehicle-card');
            cards.forEach(card => {
                const status = card.querySelector('.availability-status');
                const btn = card.querySelector('.book-btn');
                const available = Math.random() > 0.3;
                status.className = available ? 'availability-status status-available' : 'availability-status status-unavailable';
                status.innerHTML = available
                    ? '<i class="fas fa-check-circle"></i> Available'
                    : '<i class="fas fa-times-circle"></i> Unavailable';
                btn.disabled = !available;
                btn.innerHTML = available
                    ? '<i class="fas fa-calendar-plus"></i> Book Now'
                    : '<i class="fas fa-ban"></i> Not Available';
            });
        }

        function clearFilters() {
            ['vehicleType', 'capacity', 'startDate', 'endDate'].forEach(id => document.getElementById(id).value = '');
            document.querySelectorAll('.vehicle-card').forEach(card => {
                card.style.display = 'block';
                card.style.animation = 'fadeInUp 0.5s ease';
            });
        }

        function bookVehicle(type, name) {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            if (!start || !end) {
                showBookingModal('Booking Form', type, name);
                showModalMessage('Please select your travel dates first!', 'error');
                return;
            }
            showBookingModal(`Book ${name}`, type, name);
        }
    </script>
</body>
</html>