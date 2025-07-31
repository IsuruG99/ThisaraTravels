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
    <title>Contact Us - Thisara Travel & Tours</title>
    <link rel="stylesheet" href="css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- import header/navbar section -->
        <?php
            $currentPage = 'contact'; 
            include 'components/header.php'; 
        ?>

    <!-- Contact Info Cards Section -->
    <section class="contact-cards-section">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-cards">
                <div class="contact-card">
                    <div class="card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Our Address</h3>
                    <p>Binkama,<br>
                    Angunakolapelessa,<br>
                    Sri Lanka</p>
                </div>
                
                <div class="contact-card">
                    <div class="card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>thisaramobile@gmail.com</p>
                </div>
                
                <div class="contact-card">
                    <div class="card-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3>WhatsApp</h3>
                    <p>+94 71 530 3131<br>
                    24/7 Customer Support</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact-section">
        <div class="container">
            <div class="contact-content">
                <!-- Left side - Info/Image -->
                <div class="contact-info">
                    <div class="info-content">
                        <h2>Let's Start Your Journey</h2>
                        <p>Planning your perfect Sri Lankan adventure? We're here to help make your travel dreams come true! Whether you need a reliable vehicle rental or want to explore the beautiful destinations across the island, our experienced team is ready to assist you.</p>
                        
                        <div class="info-highlights">
                            <div class="highlight-item">
                                <i class="fas fa-car"></i>
                                <span>Premium Vehicle Fleet</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-map-signs"></i>
                                <span>Expert Tour Guides</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-clock"></i>
                                <span>24/7 Customer Support</span>
                            </div>
                            <div class="highlight-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Fully Insured & Licensed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side - Message Form -->
                <div class="message-form">
                    <h2>Send Us a Message</h2>
                    <form action="mailto:thisaramobile@gmail..com" method="POST" enctype="multipart/form-data" id="contact-form">
                        <div class="form-row">
                            <div class="input-group">
                                <label for="first-name">First Name <span class="required">*</span></label>
                                <input type="text" id="first-name" name="first-name" required>
                            </div>
                            <div class="input-group">
                                <label for="last-name">Last Name</label>
                                <input type="text" id="last-name" name="last-name">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="input-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="input-group">
                                <label for="phone">Contact Number</label>
                                <input type="tel" id="phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" required placeholder="Tell us about your travel plans, vehicle requirements, or any questions you have..."></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Enhanced Google Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Visit Our Office</h2>
            <p class="map-description">Our office is easily accessible and we're always ready to welcome you for personalized travel planning.</p>
            
            <div class="google-map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14364.706530308164!2d80.53918361663818!3d5.942341588542237!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae1392985f6c5b5%3A0xe4b95b6411013edf!2sMatara%20Beach!5e1!3m2!1sen!2slk!4v1753112204630!5m2!1sen!2slk" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
                
                <div class="map-overlay">
                    <div class="map-info">
                        <h4>Office Hours</h4>
                        <p>Monday - Sunday: 7:00 AM - 9:00 PM</p>
                        <a href="https://goo.gl/maps/your-location-link" target="_blank" class="directions-btn">
                            <i class="fas fa-directions"></i>
                            Get Directions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Import Footer Section -->
    <?php 
        include 'components/footer.php'; 
    ?>
    
</body>

</html>
