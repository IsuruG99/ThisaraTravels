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
    <link rel="stylesheet" href="css/header.css !important">
</head>
<body>
    <nav class="navbar noselect">
        <div class="logo">
            <a href="index.php"></a>
            <div class="header-logo">
                <!-- Image logo -->
                <img src="img/Logo.png" alt="Thisara Travels" class="header-logo-img">
                <!-- Logo Text -->
                <div class="footer-logo-text">
                    <span class="header-logo-text1">Thisara</span>
                    <span class="header-logo-text2">Travels & Tours</span>
                </div>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="index.php" class="<?= ($currentPage == 'home')? 'active': '' ?>">Home</a></li>
            <li><a href="about.php" class="<?= ($currentPage == 'aboutUs')? 'active': '' ?>">About</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="contact.php" class="<?= ($currentPage == 'contact')? 'active': '' ?>">Contact</a></li>
            <li><a href="booking.php" class="<?= ($currentPage == 'booking')? 'active': '' ?>">Booking</a></li>
            <li><a href="testimonial.php" class="<?= ($currentPage == 'review')? 'active': '' ?>">Testimonials</a></li>
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
        </ul>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
    <script>
        // Toggle mobile menu
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Close mobile menu when clicking a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            });
        });

        // Add scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Keyboard navigation support
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    link.click();
                }
            });
        });
    </script>
</body>
</html>
