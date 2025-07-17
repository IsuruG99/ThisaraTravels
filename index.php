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
        <!-- Header Start -->
        <?php include 'components/header.php'; ?>
        <!-- Header End -->


        <!-- Home Page Loading -->
        <div class="loading-homepage">
            <?php include 'home-page.php'; ?>
        </div>


        <!-- Footer Start -->
        <?php include 'components/footer.php'; ?>
        <!-- Footer End -->
    </body>
</html>