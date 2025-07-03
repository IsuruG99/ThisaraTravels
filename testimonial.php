<?php
session_start();
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

try {
    // MongoDB connection and data fetch
    $client = new MongoDB\Client($uri);
    $db = $client->ThisaraTravels;
    $collection = $db->userdata;

    // Filter by vehicle if selected
    $filter = [];
    if (isset($_GET['vehicle']) && $_GET['vehicle'] !== '') {
        $filter['vehicle'] = $_GET['vehicle'];
    }

    $cursor = $collection->find($filter);

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="ssheet.css">
    <style>
        .user-data-container { display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 20px; padding: 20px; }
        .user-data { width: 300px; background-color: #f8f9fa; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .yellow-star { color: gold; font-size: 18px; }
        .like-icon { color: red; cursor: pointer; }
        .like-dislike-icons { margin-top: 10px; }
        .arrows { text-align: center; margin: 20px 0; }
        .arrows button { margin: 0 10px; padding: 8px 16px; }
    </style>
</head>

<body>

<!-- Navbar -->
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="img/Logo.png" alt="Logo" width="40">
                Thisara Travels & Tours
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Page Header -->
<div class="bg-primary py-5 text-white text-center">
    <h1>Testimonial</h1>
</div>

<!-- Filter Dropdown -->
<div class="container my-4">
    <form method="GET" class="text-center">
        <label for="vehicle">Filter by Vehicle:</label>
        <select name="vehicle" id="vehicle" class="form-select d-inline-block w-auto mx-2">
            <option value="">All</option>
            <option value="Car" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Car') ? 'selected' : '' ?>>Car</option>
            <option value="Van" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Van') ? 'selected' : '' ?>>Van</option>
            <option value="Bus" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'Bus') ? 'selected' : '' ?>>Bus</option>
            <option value="SUV" <?= (isset($_GET['vehicle']) && $_GET['vehicle'] == 'SUV') ? 'selected' : '' ?>>SUV</option>
        </select>
        <button type="submit" class="btn btn-primary">Apply</button>
    </form>
</div>

<!-- Testimonials -->
<div class="container">
    <h2 class="text-center mb-4">Our Clients Say!</h2>

    <?php if ($cursor->isDead() === false): ?>
        <div class="user-data-container">
            <?php foreach ($cursor as $doc): ?>
                <div class="user-data">
                    <img src="img/avatar-user.png" alt="User" class="img-fluid rounded-circle mb-2" width="60">
                    <h5><?= htmlspecialchars($doc['UserName']) ?></h5>
                    <p class="text-muted small"><?= htmlspecialchars($doc['date']) ?></p>
                    <div class="rating">
                        <?php for ($i = 0; $i < $doc['ReviewCount']; $i++) echo '<span class="yellow-star">&#9733;</span>'; ?>
                    </div>
                    <p><?= htmlspecialchars($doc['Comment']) ?></p>
                    <div class="like-dislike-icons">
                        <i class="fas fa-heart like-icon"></i>
                        <span class="like-count"><?= isset($doc['likeCount']) ? htmlspecialchars($doc['likeCount']) : 0 ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No reviews available.</p>
    <?php endif; ?>
</div>

<!-- Post Review Form -->
<?php if (isset($_SESSION['username'])): ?>
<div class="container my-5">
    <h4 class="text-center">Leave a Review</h4>
    <form action="submit_review.php" method="POST" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Rating</label><br>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
        </div>
        <div class="mb-3">
            <label for="vehicle">Vehicle</label>
            <select name="vehicle" class="form-select" required>
                <option value="Car">Car</option>
                <option value="Van">Van</option>
                <option value="Bus">Bus</option>
                <option value="SUV">SUV</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="comment" class="form-label">Comment</label>
            <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Submit Review</button>
    </form>
</div>
<?php else: ?>
<div class="container text-center my-5">
    <p><a href="login.php">Login</a> to leave a review.</p>
</div>
<?php endif; ?>

<!-- Footer -->
<footer class="bg-light text-center py-4">
    <p>&copy; 2025 Thisara Travels & Tours. All Rights Reserved. Designed by WebWizards.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
    $('.like-icon').click(function () {
        $(this).toggleClass('clicked');
        const likeCount = $(this).siblings('.like-count');
        let count = parseInt(likeCount.text());
        likeCount.text($(this).hasClass('clicked') ? count + 1 : count - 1);
    });
});
</script>
</body>

</html>