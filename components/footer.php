<link href="css/footer.css" rel="stylesheet" type="text/css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">



<footer class="site-footer">
    <div class="footer-row">
        
        <!-- Logo and Description -->
        <div class="footer-col">
            <div class="logo-column rounded p-4">
                <div class="row-1">
                    <div class="footer-logo">
                        <!-- Image logo -->
                        <img src="img/Logo.png" alt="Thisara Travels" class="footer-logo-img">
                        <!-- Logo Text -->
                        <div class="footer-logo-text">
                            <span class="footer-logo-text1">Thisara</span>
                            <span class="footer-logo-text2">Travels & Tours</span>
                        </div>
                    </div>
                </div>

                <div class="description">
                    <p>We are a leading travel agency in Sri Lanka, offering a wide range of tour packages and travel services since 2010.</p>
                </div>

                <div class="social-icons">
                    <a href="https://www.facebook.com" target="_blank" class="text-white"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.instagram.com" target="_blank" class="text-white"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
        



        <!-- Quick Links -->
        <div class="footer-col">
            <div class="quick-links p-4 rounded">
                <h3 class="mb-4">Quick Links</h3>
                <p><a href="tours.php">Tour Packages</a></p>
                <p><a href="about.php">About Us</a></p>
                <p><a href="contact.php">Contact Us</a></p>
            </div>    
        </div>
        



        <!-- Contact Information-->
        <div class="footer-col">
            <div class="text-white p-4 rounded">
                <h3 class="mb-4">Contact</h3>

                <!-- Phone -->
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 40px; height: 40px;">
                            <i class="bi bi-telephone-fill fs-5 text-success"></i>
                        </div>    
                    </div>
                    <div>
                        <p class="mb-0 small" style="color: #cccccc; text-align: left">Drop a Line</p>
                        <a href="tel:+94777562425" class="text-white text-decoration-none">+94 71 512 3719</a>
                    </div>
                </div>

                <!-- Email -->
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 40px; height: 40px;">
                            <i class="bi bi-envelope-fill fs-5 text-success"></i>
                        </div>  
                    </div>
                    <div>
                        <p class="mb-0 small" style="color: #cccccc; text-align: left">Email Address</p>
                        <a href="mailto:bookings@thisaratravels.com" class="text-white text-decoration-none">bookings@thisaratravels.com</a>
                    </div>
                </div>

                <!-- Address -->
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 40px; height: 40px;">
                            <i class="bi bi-geo-alt-fill fs-5 text-success"></i>
                        </div> 
                    </div>
                    <div>
                        <p class="mb-0 small" style="color: #cccccc; text-align: left">Visit office</p>
                        <p class="mb-0 text-white">Binkama, Angunakolapelessa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Thisara Travels & Tours. All Rights Reserved.
    </div>
</footer>
