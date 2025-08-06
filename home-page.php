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
                    <img src="img/our-business-homepage.jpg" alt="Sri Lankan Culture">
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
                    <img src="img/vehicles/vehicle1-KDH.png" alt="KDH Van">
                    <h3>KDH Van</h3>
                    <button onclick="window.location.href='booking.php'" class="book-now">Book Now</button>
                </div>
                <div class="vehicle-card">
                    <img src="img/vehicles/vehicle2-WagonR.png" alt="Van High Roof">
                    <h3>Wagon R Car</h3>
                    <button onclick="window.location.href='booking.php'" class="book-now">Book Now</button>
                </div>
                <div class="vehicle-card">
                    <img src="img/vehicles/vehicle3-Prius.png" alt="Wagon Car">
                    <h3>Prius Car</h3>
                    <button onclick="window.location.href='booking.php'" class="book-now">Book Now</button>
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
                <div class="carousel-track">
                    <img src="img/slideshow/memory-1.jpg" alt="Client Memory 1">
                    <img src="img/slideshow/memory-2.jpg" alt="Client Memory 2">
                    <img src="img/slideshow/memory-3.jpg" alt="Client Memory 3">
                    <img src="img/slideshow/memory-4.jpg" alt="Client Memory 4">
                    <img src="img/slideshow/memory-5.jpg" alt="Client Memory 5">
                    <img src="img/slideshow/memory-6.jpg" alt="Client Memory 6">
                    <img src="img/slideshow/memory-7.jpg" alt="Client Memory 7">
                    <img src="img/slideshow/memory-8.jpg" alt="Client Memory 8">
                    <img src="img/slideshow/memory-9.jpg" alt="Client Memory 9">
                </div>
            
                <button class="prev"> ❮ </button>
                <button class="next"> ❯ </button>
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
        // Smooth Carousel Gallery functionality for memories
        document.addEventListener('DOMContentLoaded', () => {
            const carouselTrack = document.querySelector('.carousel-track');
            const images = document.querySelectorAll('.carousel-track img');
            const prevButton = document.querySelector('.gallery .prev');
            const nextButton = document.querySelector('.gallery .next');
    
            let currentIndex = 0;
            let imagesPerView = 3;
            let imageWidth = 0;
    
            // Function to update images per view based on screen size
            function updateImagesPerView() {
                if (window.innerWidth <= 768) {
                    imagesPerView = 1;
                } else {
                    imagesPerView = 3;
                }
            updateCarousel();
        }
    
        // Function to calculate and update carousel position
        function updateCarousel() {
            if (images.length > 0) {
                const containerWidth = carouselTrack.parentElement.offsetWidth - 60; // Minus padding for buttons
                const gap = 16; // 1rem gap
                imageWidth = (containerWidth - (gap * (imagesPerView - 1))) / imagesPerView;
            
                // Update image widths
                images.forEach(img => {
                    img.style.width = imageWidth + 'px';
                });
            
                // Calculate translation
                const translateX = currentIndex * (imageWidth + gap);
                carouselTrack.style.transform = `translateX(-${translateX}px)`;
            }
        }
    
        // Next image function
        function nextImage() {
            const maxIndex = images.length - imagesPerView;
            if (currentIndex < maxIndex) {
                currentIndex++;
            } else {
                currentIndex = 0; // Loop back to start
            }
            updateCarousel();
        }
    
        // Previous image function
        function prevImage() {
            const maxIndex = images.length - imagesPerView;
            if (currentIndex > 0) {
                currentIndex--;
            } else {
                currentIndex = maxIndex; // Loop to end
            }
            updateCarousel();
        }
    
        // Event listeners
        if (nextButton) {
            nextButton.addEventListener('click', nextImage);
        }
    
        if (prevButton) {
            prevButton.addEventListener('click', prevImage);
        }
    
        // Auto-slide every 3 seconds
        setInterval(nextImage, 4000);
    
        // Handle window resize
        window.addEventListener('resize', updateImagesPerView);
    
        // Initialize
        updateImagesPerView();
    
        // Smooth scroll for book now buttons
        // const buttons = document.querySelectorAll('.book-now');
        // buttons.forEach(button => {
        //     button.addEventListener('click', () => {
        //         window.scrollTo({ top: 0, behavior: 'smooth' });
        //     });
        // });
    });
    </script>



    <script>
        window.onload = function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
    </script>
</body>

</html>
