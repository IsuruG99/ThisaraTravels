<?php
session_start();
if (isset($_SESSION['username'])) {
    error_log("Session Username: " . $_SESSION['username']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thisara Travels & Tours | Best Travels & Tour Agent in Sri Lanka</title>
    <style>
        body {
            text-align: center;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
            /* padding: 50px; */
            height: 100vh;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        p {
            color: #666;
            text-align: center;
        }

        .maintenance-message {
            margin: 0 auto;
            padding: 20px;
            height: 1080px;
            max-height: 60vh;
        }
    </style>


    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="ssheet.css">
</head>

<!-- Content Start -->
<body>
    <nav class="navbar">
        <a href="index.php" class="logo-link">
            <div class="logo">
                <!-- Image logo -->
                <img src="img/Logo.png" alt="Logo" class="logo-img">
                <!-- Logo Text -->
                <div class="logo-text">
                    <span class="logo-text1">Thisara</span>
                    <span class="logo-text2">Travels & Tours</span>
                </div>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="active-link" data-page="home">HOME</a></li>
            <li><a href="about.php" data-page="about">ABOUT</a></li>
            <li><a href="service.php" data-page="services">SERVICES</a></li>
            <li class="dropdown">
                <a data-page="pages">PAGES <i class="fas fa-chevron-down dropdown-icon"></i></a>
                <ul class="dropdown-menu">
                    <li><a href="booking.php" data-page="booking">BOOKING</a></li>
                    <li><a href="testimonial.php" data-page="testimonial">TESTIMONIAL</a></li>
                </ul>
            </li>
            <li><a href="contact.php" data-page="contact">CONTACT</a></li>
            <li class="navbar-login-item">
                <?php
                if (!isset($_SESSION['username'])) {
                    echo '<a href="auth.php" class="navbar-login-button" title="Login">
                    <img src="img/user.png" alt="Login" class="navbar-login-icon rounded-circle">
                  </a>';
                } else {
                    $profile_image = $_SESSION['profile_image'] ?? 'img/default-profile.png';
                    $user_role = $_SESSION['role'] ?? 'user';
                    $redirect_url = ($user_role === 'admin') ? 'admindashboard.php' : 'user_dashboard.php';

                    echo '<a href="' . $redirect_url . '" class="navbar-profile-button" title="Profile">
                    <img src="' . $profile_image . '" alt="Profile" class="navbar-profile-icon rounded-circle">
                  </a>';
                }
                ?>
            </li>
        </ul>

        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <div class="maintenance-message">
        <h1>We’ll Be Right Back!</h1>
        <p>Our website is undergoing maintenance. Please check back later.</p>
    </div>

    <!-- Footer Start -->
    <?php include 'components/footer.php'; ?>
    <!-- Footer End -->
</body>
<!-- Content End -->





<script>
    // Get the current page URL path
    const currentPage = window.location.pathname.split('/').pop(); // Extracts the filename

    // Select all navigation links
    const navLinks = document.querySelectorAll('.nav-links a');

    // Loop through links to find the matching page
    navLinks.forEach(link => {
        // Check if the href of the link matches the current page
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active-link'); // Add the active class
        } else {
            link.classList.remove('active-link'); // Remove the active class from others
        }
    });
    document.addEventListener("DOMContentLoaded", function () {
        const hamburger = document.querySelector('.hamburger'); // Hamburger menu
        const navLinks = document.querySelector('.nav-links'); // Navigation links
        const header = document.querySelector('.custom-page-header'); // Page header

        // Toggle menu function
        function toggleMenu() {
            navLinks.classList.toggle('active'); // Toggle menu visibility
            toggleIcon(); // Toggle hamburger/close icon
            adjustHeaderMargin(); // Adjust header margin
        }

        // Toggle hamburger/close icon
        function toggleIcon() {
            if (navLinks.classList.contains('active')) {
                hamburger.classList.add('close-icon'); // Show close icon
            } else {
                hamburger.classList.remove('close-icon'); // Show hamburger icon
            }
        }

        // Adjust header margin based on menu state
        function adjustHeaderMargin() {
            if (navLinks.classList.contains('active')) {
                const navHeight = navLinks.scrollHeight; // Get dropdown height
                header.style.marginTop = `${navHeight}px`; // Push header down
            } else {
                header.style.marginTop = '0'; // Reset header position
            }
        }

        // Close menu when clicking outside
        function closeMenu(event) {
            if (!navLinks.contains(event.target) && !hamburger.contains(event.target)) {
                navLinks.classList.remove('active'); // Hide menu
                hamburger.classList.remove('close-icon'); // Reset icon to hamburger
                adjustHeaderMargin(); // Reset header margin
            }
        }

        // Reset menu and header margin based on screen size
        function handleResize() {
            if (window.innerWidth > 768) { // For larger screens
                navLinks.classList.remove('active'); // Hide dropdown menu
                hamburger.classList.remove('close-icon'); // Reset close icon
                header.style.marginTop = '0'; // Reset header margin
            } else {
                header.style.marginTop = '0'; // Ensure header resets properly
            }
        }

        // Event listeners
        hamburger.addEventListener('click', toggleMenu); // Toggle menu on click
        document.addEventListener('click', closeMenu); // Close menu on outside click
        window.addEventListener('resize', handleResize); // Reset on resize
    });
</script>

</html>
