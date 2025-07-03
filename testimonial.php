<?php
session_start();
require 'vendor/autoload.php';
//reminder change the name  testomonial to review page after everything is done
// MongoDB connection and data fetch
try {
    $client = new MongoDB\Client("mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels");
    $db = $client->ThisaraTravels;
    $collection = $db->userdata;
    $cursor = $collection->find();
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

    <!-- Favicon and CSS/JS library links -->
    <link rel="icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ssheet.css">
    <style>
        /* Testimonial card and layout styles */
        .user-data-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 20px;
            padding: 20px;
        }
        .user-data {
            width: 300px;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .yellow-star { color: gold; font-size: 18px; }
        .like-icon { color: red; cursor: pointer; }
        .like-dislike-icons { margin-top: 10px; }
        .arrows { text-align: center; margin: 20px 0; }
        .arrows button { margin: 0 10px; padding: 8px 16px; }
        #spinner.show {
            display: flex;
        }
        #spinner {
            display: none;
        }
    </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
<!-- Success message alert -->
<div class="alert alert-success text-center"><?= htmlspecialchars($_GET['success']) ?></div>
<?php elseif (isset($_GET['error'])): ?>
<!-- Error message alert -->
<div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<!-- Loading Spinner (hidden after page load) -->
<div id="spinner" style="display: flex;" class="bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<!-- Navigation Bar -->
<nav class="navbar">
    <a href="index.php" class="logo-link">
        <div class="logo">
            <img src="img/Logo.png" alt="Logo" class="logo-img">
            <div class="logo-text">
                <span class="logo-text1">Thisara</span>
                <span class="logo-text2">Travels & Tours</span>
            </div>
        </div>
    </a>
    <ul class="nav-links">
        <li><a href="index.php">HOME</a></li>
        <li><a href="about.php">ABOUT</a></li>
        <li><a href="service.php">SERVICES</a></li>
        <li class="dropdown">
            <a>PAGES <i class="fas fa-chevron-down dropdown-icon"></i></a>
            <ul class="dropdown-menu">
                <li><a href="booking.php">BOOKING</a></li>
                <li><a class="active-link" href="testimonial.php">TESTIMONIAL</a></li>
            </ul>
        </li>
        <li><a href="contact.php">CONTACT</a></li>
        <li class="navbar-login-item">
            <?php
            // Show login or profile icon based on session
            if (!isset($_SESSION['username'])) {
                echo '<a href="login.php" class="navbar-login-button"><img src="img/user.png" alt="Login" class="navbar-login-icon rounded-circle"></a>';
            } else {
                $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                $user_role = $_SESSION['role'] ?? 'user';
                $redirect_url = ($user_role === 'admin') ? 'admindashboard.php' : 'user_dashboard.php';
                echo '<a href="' . $redirect_url . '" class="navbar-profile-button"><img src="' . $profile_image . '" alt="Profile" class="navbar-profile-icon rounded-circle"></a>';
            }
            ?>
        </li>
    </ul>
    <div class="hamburger">
        <span></span><span></span><span></span>
    </div>
</nav>

<!-- Page Header with background image -->
<div class="custom-page-header mb-custom p-custom" style="background-image: url('img/01 (1).jpg')">
    <div class="container text-center">
        <h1 class="custom-page-title text-white">Testimonial</h1>
    </div>
</div>

<!-- Testimonials Section Title -->
<div class="container text-center">
    <h1 class="mb-5">Our Clients Say!</h1>
</div>

<?php if ($cursor->isDead() === false): ?>
    <!-- Testimonials List -->
    <div class="user-data-container">
        <?php foreach ($cursor as $doc): ?>
            <div class="user-data">
                <!-- User avatar and name -->
                <img src="img/avatar-user.png" alt="User" class="img-fluid rounded-circle mb-2" width="60">
                <h5><?= htmlspecialchars($doc['UserName']) ?></h5>
                <p class="text-muted small"><?= htmlspecialchars($doc['date']) ?></p>
                <!-- Star rating -->
                <div class="rating">
                    <?php for ($i = 0; $i < $doc['ReviewCount']; $i++) echo '<span class="yellow-star">&#9733;</span>'; ?>
                </div>
                <!-- User comment -->
                <p><?= htmlspecialchars($doc['Comment']) ?></p>
                <!-- Like icon and count -->
                <div class="like-dislike-icons">
                    <i class="fas fa-heart like-icon"></i> <span class="like-count"><?= htmlspecialchars($doc['likeCount'] ?? 0) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Pagination arrows -->
    <div class="arrows">
        <button class="previous btn btn-outline-primary">Previous</button>
        <button class="next btn btn-outline-primary">Next</button>
    </div>
<?php else: ?>
    <!-- No testimonials message -->
    <p class="text-center">No reviews available.</p>
<?php endif; ?>

<!-- Review Submission Form (only for logged-in users) -->
<?php if (isset($_SESSION['username'])): ?>
<div class="container my-5">
    <h4 class="text-center mb-3">Leave a Review</h4>
    <form action="submit_review.php" method="POST" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="rating" class="form-label">Rating</label><br>
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                <label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
        </div>
        <div class="mb-3">
            <label for="comment" class="form-label">Comment</label>
            <textarea name="comment" id="comment" rows="4" class="form-control" maxlength="200" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
</div>
<?php else: ?>
<!-- Prompt to login if not logged in -->
<div class="text-center my-5">
    <p><a href="login.php">Login</a> to post a review.</p>
</div>
<?php endif; ?>

<!-- Footer include -->
<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function () {
    // Hide spinner after page loads
    $("#spinner").fadeOut("slow");

    // Pagination logic for testimonials
    let currentPageIndex = 0;
    const perPageDesktop = 6, perPageMobile = 2;
    const total = $(".user-data").length;

    // Determine number of testimonials per page based on screen size
    function getPerPage() {
        return window.innerWidth <= 767 ? perPageMobile : perPageDesktop;
    }

    // Show testimonials for the current page( reviews)
    function showPage() {
        let perPage = getPerPage();
        let start = currentPageIndex * perPage;
        $(".user-data").hide().slice(start, start + perPage).show();
    }

    // Initial display and event binding
    showPage();
    $(window).resize(showPage);
    $(".next").click(() => { 
        currentPageIndex = (currentPageIndex + 1) % Math.ceil(total / getPerPage()); 
        showPage(); 
    });
    $(".previous").click(() => { 
        currentPageIndex = (currentPageIndex - 1 + Math.ceil(total / getPerPage())) % Math.ceil(total / getPerPage()); 
        showPage(); 
    });

    // Like button toggle logic
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
