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
</head>

<body>
    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <!-- Message Form Section -->
            <div class="message-form">
                <h2>Leave a Message</h2>
                <form action="mailto:your-email@example.com" method="POST" enctype="multipart/form-data" id="contact-form">
                    <div class="input-group">
                        <label for="first-name">First Name <span class="required">*</span></label>
                        <input type="text" id="first-name" name="first-name" required>
                    </div>
                    <div class="input-group">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="last-name">
                    </div>
                    <div class="input-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="phone">Contact Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="input-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="input-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>

            <!-- Google Map Section -->
            <div class="map-section">
                <h2>Find Us on Google Maps</h2>
                <div class="google-map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14364.706530308164!2d80.53918361663818!3d5.942341588542237!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae1392985f6c5b5%3A0xe4b95b6411013edf!2sMatara%20Beach!5e1!3m2!1sen!2slk!4v1753112204630!5m2!1sen!2slk" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

</body>

</html>
