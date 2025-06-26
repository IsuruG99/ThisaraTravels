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
    <title>View All Reviews</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- External CSS & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            padding-top: 60px;
        }

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

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .rating .yellow-star {
            color: gold;
            font-size: 18px;
        }

        .like-dislike-icons {
            margin-top: 10px;
        }

        .like-icon {
            color: red;
            cursor: pointer;
        }

        .like-icon.clicked {
            font-weight: bold;
        }

        .arrows {
            text-align: center;
            margin: 20px 0;
        }

        .arrows button {
            margin: 0 10px;
            padding: 8px 16px;
        }
    </style>
</head>
<body>

<!-- Reviews Title -->
<div class="container text-center">
    <h2 class="text-primary">What Our Clients Say</h2>
    <p class="lead">See all reviews left by our users</p>
</div>

<!-- Display Reviews -->
<?php if ($cursor->isDead() === false): ?>
    <div class="user-data-container">
        <?php foreach ($cursor as $document): ?>
            <div class="user-data">
                <img src="img/avatar-user.png" alt="User Avatar" class="user-avatar">
                <div class="user-info">
                    <h5><?= htmlspecialchars($document['UserName']) ?></h5>
                    <p><?= htmlspecialchars($document['date']) ?></p>
                </div>
                <div class="rating">
                    <?php
                    $reviewCount = $document['ReviewCount'];
                    for ($i = 0; $i < $reviewCount; $i++) {
                        echo '<span class="yellow-star">&#9733;</span>';
                    }
                    ?>
                </div>
                <p><?= htmlspecialchars($document['Comment']) ?></p>
                <div class="like-dislike-icons">
                    <i class="fas fa-heart like-icon"></i> <span class="like-count"><?= htmlspecialchars($document['likeCount'] ?? 0) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="arrows">
        <button class="previous btn btn-outline-primary">Previous</button>
        <button class="next btn btn-outline-primary">Next</button>
    </div>
<?php else: ?>
    <p class="text-center text-muted">No reviews available.</p>
<?php endif; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function () {
    let currentPageIndex = 0;
    const containersPerPageDesktop = 6;
    const containersPerPageMobile = 2;
    const totalContainers = $(".user-data").length;

    function getContainersPerPage() {
        return window.innerWidth <= 767 ? containersPerPageMobile : containersPerPageDesktop;
    }

    function showUserData() {
        const containersPerPage = getContainersPerPage();
        const startIndex = currentPageIndex * containersPerPage;
        $(".user-data").hide();
        $(".user-data").slice(startIndex, startIndex + containersPerPage).show();
        $(".arrows").toggle(totalContainers > containersPerPage);
    }

    function autoTransition() {
        currentPageIndex++;
        if (currentPageIndex * getContainersPerPage() >= totalContainers) {
            currentPageIndex = 0;
        }
        showUserData();
    }

    let autoInterval = setInterval(autoTransition, 10000);

    $(".next").click(function () {
        clearInterval(autoInterval);
        currentPageIndex++;
        if (currentPageIndex * getContainersPerPage() >= totalContainers) {
            currentPageIndex = 0;
        }
        showUserData();
        autoInterval = setInterval(autoTransition, 10000);
    });

    $(".previous").click(function () {
        clearInterval(autoInterval);
        currentPageIndex--;
        if (currentPageIndex < 0) {
            currentPageIndex = Math.ceil(totalContainers / getContainersPerPage()) - 1;
        }
        showUserData();
        autoInterval = setInterval(autoTransition, 10000);
    });

    showUserData();
    $(window).resize(showUserData);

    // Like button functionality
    $('.like-icon').click(function () {
        $(this).toggleClass('clicked');
        const likeCount = $(this).siblings('.like-count');
        const count = parseInt(likeCount.text());
        likeCount.text($(this).hasClass('clicked') ? count + 1 : count - 1);
    });
});
</script>

</body>
</html>
