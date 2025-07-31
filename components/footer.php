<link href="css/footer.css" rel="stylesheet" type="text/css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<footer class="site-footer">
    <div class="footer-row">
        
        <!-- Logo and Description -->
        <div class="footer-col">
            <div class="logo-column rounded">
                <div class="footer-logo">
                    <!-- Image logo -->
                    <img src="img/Logo.png" alt="Thisara Travels" class="footer-logo-img">
                    <!-- Logo Text -->
                    <div class="footer-logo-text">
                        <span class="footer-logo-text1">Thisara</span>
                        <span class="footer-logo-text2">Travels & Tours</span>
                    </div>
                </div>

                <div class="description">
                    <p>We are a leading travel agency in Sri Lanka, offering a wide range of tour packages and travel services since 2010.</p>
                </div>

                <div class="social-icons">
                    <a href="https://www.facebook.com" target="_blank" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.instagram.com" target="_blank" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://www.twitter.com" target="_blank" title="Twitter">
                        <i class="bi bi-twitter"></i>
                    </a>
                    <a href="https://www.youtube.com" target="_blank" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
            <div class="quick-links rounded">
                <h3>Quick Links</h3>
                <p><a href="booking.php">Bookings</a></p>
                <p><a href="about.php">About Us</a></p>
                <p><a href="contact.php">Contact Us</a></p>
                <p><a href="testimonial.php">Testimonials</a></p>
                <p><a href="gallery.php">Gallery</a></p>
            </div>    
        </div>

        <!-- Contact Information-->
        <div class="footer-col">
            <div class="text-white rounded">
                <h3>Contact Info</h3>

                <!-- Phone -->
                <div class="contact-item">
                    <div class="icon-wrapper">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle">
                            <i class="bi bi-telephone-fill fs-5 text-success"></i>
                        </div>    
                    </div>
                    <div class="contact-info">
                        <p class="small">Drop a Line</p>
                        <a href="tel:+94715303131">+94 71 530 3131</a>
                    </div>
                </div>

                <!-- Email -->
                <div class="contact-item">
                    <div class="icon-wrapper">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle">
                            <i class="bi bi-envelope-fill fs-5 text-success"></i>
                        </div>  
                    </div>
                    <div class="contact-info">
                        <p class="small">Email Address</p>
                        <a href="mailto:thisaramobile@gmail.com">thisaramobile@gmail.com</a>
                    </div>
                </div>

                <!-- Address -->
                <div class="contact-item">
                    <div class="icon-wrapper">
                        <div class="d-flex align-items-center justify-content-center bg-white rounded-circle">
                            <i class="bi bi-geo-alt-fill fs-5 text-success"></i>
                        </div> 
                    </div>
                    <div class="contact-info">
                        <p class="small">Visit Office</p>
                        <p>Binkama, Angunakolapelessa,<br>Sri Lanka</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Thisara Travels & Tours. All Rights Reserved.
    </div>
</footer>
