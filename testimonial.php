<?php
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

try {
    $client = new MongoDB\Client($uri);
    $db = $client->$databaseName;
    $collection = $db->userdata;

    $filter = [];
    if (isset($_GET['vehicle']) && $_GET['vehicle'] !== '') {
        $filter['vehicle'] = $_GET['vehicle'];
    }

    $documents = $collection->find($filter)->toArray();

    $totalRating = 0;
    $reviewCount = count($documents);
    foreach ($documents as $doc) {
        $totalRating += (int)$doc['ReviewCount'];
    }
    $avgRating = $reviewCount ? round($totalRating / $reviewCount, 1) : 0;

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
    <link rel="stylesheet" href="testimonial.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand img {
            height: 40px;
        }
        .container {
            max-width: 1200px;
        }
        .btn-success {
            background-color: #1c4b4b;
            border-color: #1c4b4b;
        }
    </style>
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
                        <a class="nav-link dropdown-toggle active" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown">Pages</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="booking.php">Booking</a></li>
                            <li><a class="dropdown-item active" href="testimonial.php">Testimonial</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="navbar-login-item ms-2">
                        <?php
                        if (!isset($_SESSION['username'])) {
                            echo '<a href="login.php" class="navbar-login-button"><img src="img/user.png" alt="Login" class="navbar-login-icon rounded-circle" style="width:32px;height:32px;"></a>';
                        } else {
                            $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                            $user_role = $_SESSION['role'] ?? 'user';
                            $redirect_url = ($user_role === 'admin') ? 'admindashboard.php' : 'user_dashboard.php';
                            echo '<a href="' . $redirect_url . '" class="navbar-profile-button"><img src="' . $profile_image . '" alt="Profile" class="navbar-profile-icon rounded-circle" style="width:32px;height:32px;"></a>';
                        }
                        ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Stylish Header Section -->
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
            <label for="vehicle">Filter by Vehicle:</label>
            <select name="vehicle" id="vehicle" class="form-select d-inline-block w-auto mx-2">
                <option value="">All</option>
                <option value="Car" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Car') ? 'selected' : '' ?>>Car</option>
                <option value="Van" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Van') ? 'selected' : '' ?>>Van</option>
                <option value="Bus" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Bus') ? 'selected' : '' ?>>Bus</option>
                <option value="SUV" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'SUV') ? 'selected' : '' ?>>SUV</option>
            </select>
            <button type="submit" class="btn btn-dark">Apply</button>
        </form>
    </div>
</div>

<!-- Average Rating Display -->
<?php if ($reviewCount > 0): ?>
<div class="text-center mb-4">
    <h5 class="text-secondary">Average Rating:</h5>
    <div class="star-rating">
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="star<?= $i <= round($avgRating) ? ' filled' : '' ?>">&#9733;</span>
        <?php endfor; ?>
        <span class="ms-2">(<?= $avgRating ?> out of 5 from <?= $reviewCount ?> reviews)</span>
    </div>
</div>
<?php endif; ?>

<!-- Testimonials -->
<div class="container">
    <div class="row justify-content-center">
        <?php foreach ($documents as $doc): ?>
            <div class="col-md-4 mb-4">
                <div class="review-card">
                    <img src="img/avatar-user.png" alt="User" class="review-avatar">
                    <h5><?= htmlspecialchars($doc['UserName']) ?></h5>
                    <p class="review-date"><?= htmlspecialchars($doc['date']) ?></p>
                    <div class="rating">
                        <?php for ($i = 0; $i < $doc['ReviewCount']; $i++) echo '<span class="star filled">&#9733;</span>'; ?>
                    </div>
                    <p class="review-comment">"<?= htmlspecialchars($doc['Comment']) ?>"</p>
                    <?php if (!empty($doc['bookingID'])): ?>
                        <p class="text-muted small">Booking ID: <?= htmlspecialchars($doc['bookingID']) ?></p>
                    <?php endif; ?>
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
            <div class="mb-3 text-center">
                <label class="form-label d-block">Rating</label>
                <div class="rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                        <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="vehicle" class="form-label">Vehicle</label>
                <select name="vehicle" class="form-select" required>
                    <option value="Car">Car</option>
                    <option value="Van">Van</option>
                    <option value="Bus">Bus</option>
                    <option value="SUV">SUV</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="bookingID" class="form-label">Booking ID</label>
                <input type="text" name="bookingID" id="bookingID" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success px-4">Submit Review</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="container d-flex justify-content-center my-5">
    <div class="card shadow-lg border-0" style="background: linear-gradient(90deg, #0d4f4b 0%, #128377 100%); color: #fff; border-radius: 18px; max-width: 400px;">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="fas fa-user-lock fa-2x" style="color:#e8f5f1;"></i>
            </div>
            <h5 class="card-title mb-2">Login Required</h5>
            <p class="card-text mb-3">Please <a href="auth.php" style="color:#ffe082; text-decoration:underline;">login</a> to leave a review.</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Footer -->
<?php include 'components/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>