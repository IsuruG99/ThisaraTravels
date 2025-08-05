<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
</head>

<body>
    <nav class="navbar noselect">
        <div class="navbar-container">
            <div class="logo">
                <a href="index.php" class="header-logo">
                    <!-- Image logo -->
                    <img src="img/Logo.png" alt="Thisara Travels" class="header-logo-img">
                    <!-- Logo Text -->
                    <div class="header-logo-text">
                        <span class="header-logo-text1">Thisara</span>
                        <span class="header-logo-text2">Travels & Tours</span>
                    </div>
                </a>
            </div>

            <button class="navbar-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <ul class="navbar-menu">
                <li><a href="index.php" class="<?= ($currentPage == 'home') ? 'active' : '' ?>">Home</a></li>
                <li><a href="about.php" class="<?= ($currentPage == 'aboutUs') ? 'active' : '' ?>">About Us</a></li>
                <li><a href="booking.php" class="<?= ($currentPage == 'booking') ? 'active' : '' ?>">Booking</a></li>
                <li><a href="testimonial.php" class="<?= ($currentPage == 'review') ? 'active' : '' ?>">Testimonials</a></li>
                <li><a href="contact.php" class="<?= ($currentPage == 'contact') ? 'active' : '' ?>">Contact</a></li>
                <?php
                    if (!isset($_SESSION['username'])) {
                    // User not logged in
                        echo '<a href="auth.php" class="navbar-login-button">
                            <img src="img/profile-icon.png" class="navbar-login-icon rounded-circle" style="width:32px;height:32px;" alt="Login">
                        </a>';
                    } else {
                    // User is logged in
                        $profile_image = $_SESSION['profile_image'] ?? ''; // Fetch from session
                        $profile_image = (!empty($profile_image) && file_exists($profile_image)) ? $profile_image : 'img/profile-icon.png';

                        echo '<a href="profile-page.php" class="navbar-profile-button">
                            <img src="' . htmlspecialchars($profile_image) . '" class="navbar-profile-icon rounded-circle" style="width:32px;height:32px;" alt="Profile">
                        </a>';
                    }
                ?>
            </ul>
        </div>
    </nav>



    <script>
        // Toggle mobile menu button
        const navbarToggle = document.querySelector('.navbar-toggle');
        const navbarMenu = document.querySelector('.navbar-menu');
        
        navbarToggle.addEventListener('click', () => {
            navbarToggle.classList.toggle('active');
            navbarMenu.classList.toggle('active');
        });

        // Close the menu when a link is clicked
        // const navbarLinks = document.querySelectorAll('.navbar-menu a');
        // navbarLinks.forEach(link => {  
        //     link.addEventListener('click', () => {
        //         if (navbarMenu.classList.contains('active')) {
        //             navbarToggle.classList.remove('active');
        //             navbarMenu.classList.remove('active');
        //         }
        //     });
        // });
    </script>
</body>

</html>
