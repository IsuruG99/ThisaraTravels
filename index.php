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
    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="ssheet.css">
</head>
<body>

     <!-- Home Page Loading -->
    <div class="loading-homepage">
        <?php include 'home-page.php'; ?>
    </div>

    <!-- Footer Start -->
    <?php include 'components/footer.php'; ?>
    <!-- Footer End -->
</body>
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
</script>
</html>