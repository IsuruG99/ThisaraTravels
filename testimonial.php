<?php
require 'auth-config.php';

try {
    $db = getMongoDB();
    $reviewsCollection = $db->selectCollection(collectionName: 'reviews');
    $usersCollection = $db->selectCollection(collectionName: 'users');

    $filter = ['vehicleType' => ['$exists' => true]];
    if (!empty($_GET['vehicle'])) {
        $filter['vehicleType'] = $_GET['vehicle'];
    }
    if (!empty($_GET['stars'])) {
        $filter['starCount'] = (string) $_GET['stars'];
    }

    $documents = $reviewsCollection->find(filter: $filter)->toArray();
    $totalRating = 0;
    $starCount = count(value: $documents);

    // Prepare user data
    $userData = [];
    foreach ($documents as $doc) {
        $totalRating += (int) $doc['starCount'];
        if (isset($doc['userId'])) {
            $userId = (string) $doc['userId']; // Convert ObjectId to string
            if (!isset($userData[$userId])) {
                $user = $usersCollection->findOne(filter: ['_id' => $doc['userId']]);
                $userData[$userId] = [
                    'userName' => $user['UserName'] ?? 'Unknown User',
                    'profileImage' => $user['profile_image'] ?? 'img/default_profile.png'
                ];
            }
        }
    }
    $avgRating = $starCount ? round(num: $totalRating / $starCount, precision: 1) : 0;
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Testimonials - View All</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/testimonial.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark custom-navbar py-2">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="img/Logo.png" alt="Logo" class="logo-img me-2" style="height:32px;">
                    <span class="fw-bold">Thisara Travels & Tours</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="service.php">Services</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle active" href="#" id="pagesDropdown" role="button"
                                data-bs-toggle="dropdown">Pages</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="booking.php">Booking</a></li>
                                <li><a class="dropdown-item active" href="testimonial.php">Testimonial</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                        <li class="navbar-login-item ms-2">
                            <?php
                            if (!isset($_SESSION['username'])) {
                                echo '<a href="auth.php" class="navbar-login-button"><img src="img/user.png" alt="Login" class="navbar-login-icon rounded-circle" style="width:32px;height:32px;"></a>';
                            } else {
                                $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                                $user_role = $_SESSION['role'] ?? 'user';
                                $redirect_url = ($user_role === 'admin') ? 'admindashboard.php' : 'profile-page.php';
                                echo '<a href="' . $redirect_url . '" class="navbar-profile-button"><img src="' . $profile_image . '" alt="Profile" class="navbar-profile-icon rounded-circle" style="width:32px;height:32px;"></a>';
                            }
                            ?>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Header Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1>Testimonials</h1>
            <p>What our clients say about us</p>
        </div>
    </div>

    <!-- Vehicle Filter -->
    <div class="container my-4">
        <div class="filter-form">
            <form method="GET" class="text-center">
                <label for="vehicle">Filter by Vehicle Type:</label>
                <select name="vehicle" id="vehicle" class="form-select d-inline-block w-auto mx-2">
                    <option value="">All</option>
                    <option value="Car" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Car') ? 'selected' : '' ?>>Car
                    </option>
                    <option value="KDH Van" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'KDH Van') ? 'selected' : '' ?>>KDH Van</option>
                    <option value="Dolphin" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Dolphin') ? 'selected' : '' ?>>Dolphin</option>
                </select>

                <label for="stars" class="ms-3">Filter by Rating:</label>
                <select name="stars" id="stars" class="form-select d-inline-block w-auto mx-2">
                    <option value="">All</option>
                    <option value="5" <?= (isset($_GET['stars']) && $_GET['stars'] == '5') ? 'selected' : '' ?>>5 Stars
                    </option>
                    <option value="4" <?= (isset($_GET['stars']) && $_GET['stars'] == '4') ? 'selected' : '' ?>>4 Stars
                    </option>
                    <option value="3" <?= (isset($_GET['stars']) && $_GET['stars'] == '3') ? 'selected' : '' ?>>3 Stars
                    </option>
                    <option value="2" <?= (isset($_GET['stars']) && $_GET['stars'] == '2') ? 'selected' : '' ?>>2 Stars
                    </option>
                    <option value="1" <?= (isset($_GET['stars']) && $_GET['stars'] == '1') ? 'selected' : '' ?>>1 Star
                    </option>
                </select>

                <button type="submit" class="btn btn-dark">Apply</button>
                <?php if (!empty($_GET['vehicle']) || !empty($_GET['stars'])): ?>
                    <a href="testimonial.php" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Average Rating Display -->
    <?php if ($starCount > 0): ?>
        <div class="text-center mb-4">
            <h5 class="text-secondary">Average Rating:</h5>
            <div class="star-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star<?= $i <= round(num: $avgRating) ? ' filled' : '' ?>">&#9733;</span>
                <?php endfor; ?>
                <span class="ms-2">(<?= $avgRating ?> out of 5 from <?= $starCount ?> reviews)</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Testimonials -->
    <div class="container">
        <div class="row justify-content-center">
            <?php foreach ($documents as $doc):
                $userId = isset($doc['userId']) ? (string) $doc['userId'] : null;
                $userName = $userId ? ($userData[$userId]['userName'] ?? 'Unknown User') : 'Unknown User';
                $userImage = $userId ? ($userData[$userId]['profileImage'] ?? 'img/default_profile.png') : 'img/default_profile.png';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="review-card">
                        <img src="<?= htmlspecialchars(string: $userImage) ?>" alt="User" class="review-avatar">
                        <h5><?= htmlspecialchars(string: $userName) ?></h5>
                        <p class="review-date">
                            <?php
                            if ($doc['date'] instanceof MongoDB\BSON\UTCDateTime) {
                                echo htmlspecialchars(string: $doc['date']->toDateTime()->format('Y-m-d h:i A'));
                            } else {
                                echo htmlspecialchars(string: $doc['date']);
                            }
                            ?>
                        </p>
                        <p class="vehicle-info">
                            <strong><?= htmlspecialchars(string: $doc['vehicleName'] ?? 'N/A') ?></strong>
                            (<?= htmlspecialchars(string: $doc['vehicleType'] ?? 'N/A') ?>)
                        </p>
                        <div class="rating">
                            <?php for ($i = 0; $i < $doc['starCount']; $i++)
                                echo '<span class="star filled">&#9733;</span>'; ?>
                        </div>
                        <p class="review-comment">"<?= htmlspecialchars(string: $doc['comment']) ?>"</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Post Review -->
    <?php if (isset($_SESSION['username'])): ?>
        <div class="container my-5">
            <div class="review-form-container">
                <h4>Leave a Review</h4>
                <form action="submit_review.php" method="POST" class="p-4">
                    <input type="hidden" name="userId" value="<?= $_SESSION['user_id'] ?>">

                    <!-- Hidden fields for actual submission -->
                    <input type="hidden" name="vehicleType" id="vehicleType">
                    <input type="hidden" name="vehicleName" id="vehicleName">

                    <!-- Booking Selection -->
                    <div class="mb-3">
                        <label for="orderId" class="form-label">Booking</label>
                        <select name="orderId" id="orderId" class="form-select" required
                            onchange="fillVehicleDetails(this)">
                            <?php
                            try {
                                $bookingsCollection = $db->selectCollection(collectionName: 'bookings');
                                $userBookings = $bookingsCollection->find(filter: [
                                    'username' => $_SESSION['username'],
                                    'status' => 'pending'
                                ])->toArray();

                                if (empty($userBookings)) {
                                    echo '<option value="" disabled selected>No pending bookings available</option>';
                                } else {
                                    echo '<option value="" disabled selected>Select your booking</option>';
                                    foreach ($userBookings as $booking) {
                                        $bookingId = (string) $booking['_id'];
                                        $dateRange = htmlspecialchars(string: $booking['pickup_date'] ?? '') . ' - ' .
                                            htmlspecialchars(string: $booking['dropoff_date'] ?? '');
                                        echo '<option value="' . $bookingId . '" 
                              data-vehicle="' . htmlspecialchars(string: $booking['vehicle_type'] ?? '') . ' - ' .
                                            htmlspecialchars(string: $booking['vehicle_name'] ?? '') . '"
                              data-dates="' . $dateRange . '">' .
                                            $dateRange . '</option>';
                                    }
                                }
                            } catch (Exception $e) {
                                echo '<option value="" disabled selected>Error loading bookings</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Vehicle Display (readonly) -->
                    <div class="mb-3">
                        <label for="vehicleDisplay" class="form-label">Vehicle</label>
                        <input type="text" id="vehicleDisplay" class="form-control" readonly>
                    </div>

                    <!-- Date Range (autofilled from booking) -->
                    <div class="mb-3">
                        <label class="form-label">Booking Dates</label>
                        <input type="text" id="dateRange" class="form-control" readonly>
                    </div>

                    <!-- Rating -->
                    <div class="mb-3 text-center">
                        <label class="form-label d-block">Rating</label>
                        <div class="rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Comment -->
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success px-4" <?= empty($userBookings) ? 'disabled' : '' ?>>Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="container d-flex justify-content-center my-5">
            <div class="card shadow-lg border-0"
                style="background: linear-gradient(90deg, #0d4f4b 0%, #128377 100%); color: #fff; border-radius: 18px; max-width: 400px;">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-lock fa-2x" style="color:#e8f5f1;"></i>
                    </div>
                    <h5 class="card-title mb-2">Login Required</h5>
                    <p class="card-text mb-3">Please <a href="auth.php"
                            style="color:#ffe082; text-decoration:underline;">login</a> to leave a review.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php include 'components/footer.php'; ?>
    <script>
                    function fillVehicleDetails(select) {
                        const selectedOption = select.options[select.selectedIndex];
                        if (selectedOption && selectedOption.dataset.vehicle) {
                            // Split the vehicle data into type and name
                            const vehicleParts = selectedOption.dataset.vehicle.split(' - ');
                            document.getElementById('vehicleType').value = vehicleParts[0] || '';
                            document.getElementById('vehicleName').value = vehicleParts[1] || '';
                            document.getElementById('vehicleDisplay').value = selectedOption.dataset.vehicle;
                            document.getElementById('dateRange').value = selectedOption.dataset.dates;
                        }
                    }
                </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>