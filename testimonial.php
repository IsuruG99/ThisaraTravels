<?php
session_start();
require 'vendor/autoload.php';

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
    <link rel="icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="ssheet.css">
    <style>
        .user-data-container { display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 20px; padding: 20px; }
        .user-data { width: 300px; background-color: #f8f9fa; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .yellow-star { color: gold; font-size: 18px; }
        .like-icon { color: red; cursor: pointer; }
        .like-dislike-icons { margin-top: 10px; }
        .arrows { text-align: center; margin: 20px 0; }
        .arrows button { margin: 0 10px; padding: 8px 16px; }
        #spinner.show { display: flex; }
        #spinner { display: none; }
        #vehicleFilter { max-width: 300px; margin: 0 auto 20px auto; }
    </style>
</head>
<body>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success text-center"><?= htmlspecialchars($_GET['success']) ?></div>
<?php elseif (isset($_GET['error'])): ?>
<div class="alert alert-danger text-center"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div id="spinner" class="bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" role="status"></div>
</div>

<?php include 'navbar.php'; ?>

<div class="custom-page-header mb-custom p-custom" style="background-image: url('img/01 (1).jpg')">
    <div class="container text-center">
        <h1 class="custom-page-title text-white">Testimonial</h1>
    </div>
</div>

<div class="container text-center">
    <h1 class="mb-3">Our Clients Say!</h1>
    <!-- Filter Dropdown -->
    <select id="vehicleFilter" class="form-select mb-4">
        <option value="All">All Vehicles</option>
        <?php
        $vehicles = $collection->distinct("Vehicle");
        sort($vehicles);
        foreach ($vehicles as $vehicle) {
            echo "<option value='" . htmlspecialchars($vehicle) . "'>" . htmlspecialchars($vehicle) . "</option>";
        }
        ?>
    </select>
</div>

<?php if ($cursor->isDead() === false): ?>
<div class="user-data-container">
    <?php foreach ($cursor as $doc): ?>
        <div class="user-data" data-vehicle="<?= htmlspecialchars($doc['Vehicle'] ?? 'Unknown') ?>">
            <img src="img/avatar-user.png" alt="User" class="img-fluid rounded-circle mb-2" width="60">
            <h5><?= htmlspecialchars($doc['UserName']) ?></h5>
            <p class="text-muted small"><?= htmlspecialchars($doc['date']) ?></p>
            <div class="rating">
                <?php for ($i = 0; $i < $doc['ReviewCount']; $i++) echo '<span class="yellow-star">&#9733;</span>'; ?>
            </div>
            <p><?= htmlspecialchars($doc['Comment']) ?></p>
            <div class="like-dislike-icons">
                <i class="fas fa-heart like-icon"></i> <span class="like-count"><?= htmlspecialchars($doc['likeCount'] ?? 0) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="arrows">
    <button class="previous btn btn-outline-primary">Previous</button>
    <button class="next btn btn-outline-primary">Next</button>
</div>
<?php else: ?>
<p class="text-center">No reviews available.</p>
<?php endif; ?>

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
        <div class="mb-3">
            <label for="vehicle" class="form-label">Vehicle Used</label>
            <input type="text" name="vehicle" id="vehicle" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
</div>
<?php else: ?>
<div class="text-center my-5">
    <p><a href="login.php">Login</a> to post a review.</p>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function () {
    $("#spinner").fadeOut("slow");

    let currentPageIndex = 0;
    const perPageDesktop = 6, perPageMobile = 2;

    function getPerPage() {
        return window.innerWidth <= 767 ? perPageMobile : perPageDesktop;
    }

    function showPage() {
        const perPage = getPerPage();
        const visible = $('.user-data:visible');
        visible.hide();
        visible.slice(currentPageIndex * perPage, (currentPageIndex + 1) * perPage).show();
    }

    function resetPagination() {
        currentPageIndex = 0;
        showPage();
    }

    $(".next").click(() => {
        const perPage = getPerPage();
        const totalVisible = $('.user-data:visible').length;
        const pageCount = Math.ceil(totalVisible / perPage);
        currentPageIndex = (currentPageIndex + 1) % pageCount;
        showPage();
    });

    $(".previous").click(() => {
        const perPage = getPerPage();
        const totalVisible = $('.user-data:visible').length;
        const pageCount = Math.ceil(totalVisible / perPage);
        currentPageIndex = (currentPageIndex - 1 + pageCount) % pageCount;
        showPage();
    });

    // Filter by vehicle
    $("#vehicleFilter").on("change", function () {
        const selected = $(this).val();
        $(".user-data").each(function () {
            const vehicle = $(this).data("vehicle");
            if (selected === "All" || vehicle === selected) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        resetPagination();
    });

    // Like button toggle
    $('.like-icon').click(function () {
        $(this).toggleClass('clicked');
        const likeCount = $(this).siblings('.like-count');
        let count = parseInt(likeCount.text());
        likeCount.text($(this).hasClass('clicked') ? count + 1 : count - 1);
    });

    resetPagination();
    $(window).resize(resetPagination);
});
</script>
</body>
</html>
