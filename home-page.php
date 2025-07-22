<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Travel & Vehicle Rental</title>
    <link rel="stylesheet" href="css/home-page.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body>
    <main>
        <!-- import header/navbar section -->
        <?php
            $currentPage = 'home'; 
            include 'components/header.php'; 
        ?>
        
        <!-- Welcome Section with Background Image -->
         <section class="welcome-slideshow" id="welcome">
            <div class="welcome-content">
                <h1>Welcome to Your Sri Lankan Adventure</h1>
                <p>Experience the beauty of Sri Lanka with our top-tier travel and vehicle rental services.</p>
            </div>
        </section>
        <!-- ------------------------- -->

        <!-- About Our Business -->
        <section class="about-business">
            <div class="about-container">
                <div class="about-text">
                    <h2>Our Business</h2>
                    <p>We are a premier travel and vehicle rental agency in Sri Lanka, dedicated to making your journey
                        unforgettable. From exploring ancient cultural sites to cruising along scenic coastlines, we
                        provide reliable vehicles and tailored travel experiences to suit your needs.</p>
                </div>
                <div class="about-image">
                    <img src="img/welcome-1.jpg" alt="Sri Lankan Culture">
                </div>
            </div>
        </section>

        <!-- Our Services -->
        <section class="services">
            <h2>What We Offer</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3>Vehicle Rentals</h3>
                    <p>Choose from a wide range of vehicles, including cars, vans, and tuk-tuks, for your travel needs.
                    </p>
                </div>
                <div class="service-card">
                    <h3>Guided Tours</h3>
                    <p>Explore Sri Lanka with our expert guides, offering personalized cultural and adventure tours.</p>
                </div>
                <div class="service-card">
                    <h3>Travel Planning</h3>
                    <p>We create customized itineraries to ensure a seamless and memorable travel experience.</p>
                </div>
            </div>
        </section>

        <!-- Vehicle Cards -->
        <section class="vehicles">
            <h2>Our Vehicles</h2>
            <div class="vehicle-grid">
                <div class="vehicle-card">
                    <img src="img/vehicles/vehicle-1.png" alt="KDH Van">
                    <h3>Vehicle 1</h3>
                    <p>Ideal for city tours and solo travelers.</p>
                    <button class="book-now">Book Now</button>
                </div>
                <div class="vehicle-card">
                    <img src="img/vehicles/vehicle-2.png" alt="Van High Roof">
                    <h3>Vehicle 2</h3>
                    <p>Spacious for group adventures.</p>
                    <button class="book-now">Book Now</button>
                </div>
                <div class="vehicle-card">
                    <img src="img/vehicles/vehicle-3.png" alt="Wagon Car">
                    <h3>Vehicle 3</h3>
                    <p>Fun and authentic for local exploration.</p>
                    <button class="book-now">Book Now</button>
                </div>
            </div>
        </section>

        <!-- Who We Are -->
        <section class="who-we-are">
            <h2>Who We Are</h2>
            <p>With over 10 years of experience, we are a trusted name in Sri Lanka’s travel industry. Our team is
                passionate about showcasing the island’s rich heritage, natural beauty, and vibrant culture. We pride
                ourselves on exceptional customer service and a commitment to creating lifelong memories for our
                clients.</p>
        </section>

        <!-- Slideshow Gallery -->
        <section class="gallery">
            <h2>Our Memories</h2>
            <div class="slideshow-container">
                <div class="slide active">
                    <img src="img/slideshow/memory-1.jpg" alt="Client Memory 1">
                </div>
                <div class="slide">
                    <img src="img/slideshow/memory-2.jpg" alt="Client Memory 2">
                </div>
                <div class="slide">
                    <img src="img/slideshow/memory-3.jpg" alt="Client Memory 3">
                </div>
                <button class="prev">❮</button>
                <button class="next">❯</button>
            </div>
        </section>

        <!-- import footer section -->
        <?php include 'components/footer.php'; ?>
    </main>






    <!-- // Scripts (JS) Section Starting // -->

    <script>
        // Welcome Section Background Images Slideshow
        const images = [
            'img/welcome-1.jpg',
            'img/welcome-2.jpg',
            'img/welcome-3.jpg'
        ];

        let current = 0;
        const section = document.getElementById('welcome');

        function changeBackground() {
            section.style.backgroundImage = `url(${images[current]})`;
            current = (current + 1) % images.length;
        }

        //initial background
        changeBackground();
        // Change background every 5 seconds
        setInterval(changeBackground, 5000);
    </script>  
    
    
    <script>
        // Memory/Gallery Slideshow functionality
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('.slide');
            const prevButton = document.querySelector('.prev');
            const nextButton = document.querySelector('.next');
            let currentSlide = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
            }

            prevButton.addEventListener('click', () => {
                currentSlide = (currentSlide === 0) ? slides.length - 1 : currentSlide - 1;
                showSlide(currentSlide);
            });

            nextButton.addEventListener('click', () => {
                currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
                showSlide(currentSlide);
            });

            // Auto-slide every 5 seconds
            setInterval(() => {
                currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
                showSlide(currentSlide);
            }, 5000);

            // Smooth scroll for book now buttons
            const buttons = document.querySelectorAll('.book-now');
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' }); // Adjust to link to booking form if added
                });
            });
        });
    </script>

    <script>
        window.onload = function() {
            window.scrollTo(0, 0);
        };
    </script>
</body>

</html>
