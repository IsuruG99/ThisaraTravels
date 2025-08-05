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
    <title>About Us - Thisara Travel & Tours</title>
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- import header/navbar section -->
        <?php
            $currentPage = 'aboutUs'; 
            include 'components/header.php'; 
        ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>About Thisara Travel & Tours</h1>
            <p>Your trusted partner for unforgettable Sri Lankan adventures since 2010</p>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- Who We Are Section -->
    <section class="who-we-are">
        <div class="container">
            <div class="section-header">
                <h2>Who We Are</h2>
                <div class="section-line"></div>
            </div>
            
            <div class="content-grid">
                <div class="content-text">
                    <p class="intro-text">
                        For over a decade, Thisara Travel & Tours has been Sri Lanka's premier travel companion, 
                        specializing in creating extraordinary experiences that showcase the natural beauty, 
                        rich culture, and warm hospitality of our island nation.
                    </p>
                    
                    <p>
                        Founded in 2010 with a simple mission - to share the wonders of Sri Lanka with the world - 
                        we have grown from a small local operation to one of the most trusted travel agencies 
                        in the Southern Province. Our team of experienced professionals brings together decades 
                        of local knowledge, passion for travel, and commitment to exceptional service.
                    </p>
                    
                    <p>
                        Whether you're seeking adventure in our national parks, relaxation on pristine beaches, 
                        cultural immersion in ancient cities, or comfortable transportation for your journey, 
                        we provide personalized solutions that exceed expectations.
                    </p>
                    
                    <div class="stats-row">
                        <div class="stat-item">
                            <h3>1000+</h3>
                            <p>Happy Customers</p>
                        </div>
                        <div class="stat-item">
                            <h3>50+</h3>
                            <p>Tour Packages</p>
                        </div>
                        <div class="stat-item">
                            <h3>15+</h3>
                            <p>Years Experience</p>
                        </div>
                    </div>
                </div>
                
                <div class="content-image">
                    <img src="img/about-us-image.jpg" alt="Thisara Travel Team">
                    <div class="image-overlay">
                        <h4>Exploring Sri Lanka Since 2010</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vision & Mission Section -->
    <section class="vision-mission">
        <div class="container">
            <div class="vm-grid">
                <div class="vision-card">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Our Vision</h3>
                    <p>
                        To be Sri Lanka's most trusted and innovative travel partner, 
                        creating transformative experiences that connect travelers with 
                        the authentic beauty, culture, and spirit of our island paradise.
                    </p>
                </div>
                
                <div class="mission-card">
                    <div class="card-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <h3>Our Mission</h3>
                    <p>
                        We are committed to providing exceptional travel experiences through 
                        personalized service, local expertise, and sustainable tourism practices. 
                        Our mission is to showcase Sri Lanka's hidden gems while ensuring 
                        every journey is safe, comfortable, and unforgettable.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Thisara Travel & Tours</h2>
                <div class="section-line"></div>
                <p class="section-subtitle">Discover what makes us different</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h4>Local Expertise</h4>
                    <p>15+ years of deep local knowledge and insider access to Sri Lanka's hidden treasures.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Personalized Service</h4>
                    <p>Customized itineraries tailored to your interests, budget, and travel style.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h4>Premium Fleet</h4>
                    <p>Well-maintained, comfortable vehicles with experienced drivers for safe travels.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p>Round-the-clock customer support to ensure your peace of mind throughout your journey.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4>Eco-Friendly</h4>
                    <p>Committed to sustainable tourism practices that preserve Sri Lanka's natural beauty.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Fully Licensed</h4>
                    <p>Registered with Sri Lanka Tourism Board and fully insured for your protection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Owner/Team Section -->
    <section class="owner-section">
        <div class="container">
            <div class="section-header">
                <h2>Meet Our Founder</h2>
                <div class="section-line"></div>
            </div>
            
            <div class="owner-content">
                <div class="owner-image">
                    <img src="img/owner-photo.jpg" alt="Thisara - Founder & CEO">
                    <div class="image-border"></div>
                </div>
                
                <div class="owner-info">
                    <div class="owner-details">
                        <h3>Mr. Thisara Perera</h3>
                        <p class="owner-title">Founder & CEO</p>
                        
                        <div class="owner-description">
                            <p>
                                With over 15 years of experience in Sri Lanka's tourism industry, 
                                Thisara founded this company with a vision to share the authentic 
                                beauty of Sri Lanka with travelers from around the world.
                            </p>
                            
                            <p>
                                Born and raised in the Southern Province, Thisara's deep understanding 
                                of local culture, hidden gems, and traveler needs has been instrumental 
                                in building our reputation as a trusted travel partner.
                            </p>
                            
                            <p>
                                His commitment to excellence and personal attention to every guest 
                                has earned recognition from international travel platforms and 
                                countless satisfied customers who return year after year.
                            </p>
                        </div>
                        
                        <div class="owner-credentials">
                            <div class="credential">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Tourism Management Graduate</span>
                            </div>
                            <div class="credential">
                                <i class="fas fa-certificate"></i>
                                <span>Licensed Tour Guide</span>
                            </div>
                            <div class="credential">
                                <i class="fas fa-award"></i>
                                <span>Excellence in Tourism Award 2022</span>
                            </div>
                        </div>
                        
                        <div class="owner-quote">
                            <blockquote>
                                "Every journey should be a story worth telling. Our goal is to create 
                                memories that last a lifetime while showcasing the true spirit of Sri Lanka."
                            </blockquote>
                            <cite>- Thisara Perera</cite>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Your Sri Lankan Adventure?</h2>
                <p>Let us help you create unforgettable memories in the pearl of the Indian Ocean</p>
                <div class="cta-buttons">
                    <a href="contact.php" class="btn-primary">
                        <i class="fas fa-envelope"></i>
                        Contact Us Today
                    </a>
                    <a href="tours.php" class="btn-secondary">
                        <i class="fas fa-route"></i>
                        View Tour Packages
                    </a>
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
