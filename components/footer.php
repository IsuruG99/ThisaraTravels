<link rel="stylesheet" href="css/footer.css">
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">



<footer class="site-footer">
    <div class="footer-row">
        
        <!-- Logo and Description -->
        <div class="footer-col">
            <img src="img/logo-white.png" alt="Thisara Travels" class="footer-logo">
            <p>Your trusted travel partner in Sri Lanka since 2010</p>
        </div>
        
        <!-- Quick Links -->
        <div class="footer-col">
            <h3>Quick Links</h3>
                <ul><a href="tours.php">Tour Packages</a></ul>
                <ul><a href="about.php">About Us</a></ul>
                <ul><a href="contact.php">Contact</a></ul>
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
                        <a href="tel:+94777562425" class="text-white text-decoration-none">+94 77 756 2425</a>
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
                        <a href="mailto:bookings@lankatrek.com" class="text-white text-decoration-none">bookings@lankatrek.com</a>
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
                        <p class="mb-0 text-white">436A, Methsara, Delgalla.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Thisara Travels & Tours. All Rights Reserved.
    </div>
</footer>
