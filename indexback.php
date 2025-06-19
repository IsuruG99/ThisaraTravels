<?php
session_start(); // Start the session

// Check if the user is logged in (i.e., the session contains 'username')
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to the login page
    exit; // Exit the script to prevent further execution
}

require 'vendor/autoload.php'; // Ensure Composer's autoload is included

$uri = "mongodb+srv://ThisaraTravels:ThisaraTravels071@thisaratravels.vjuro.mongodb.net/?retryWrites=true&w=majority&appName=ThisaraTravels"; // Replace with your MongoDB Atlas connection string
$databaseName = "ThisaraTravels"; // Replace with your database name

// Enable error reporting to display PHP errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $collection = $database->userdata; // Replace with your collection name

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Retrieve data from POST request
        $user_name = $_SESSION['username']; // Use the session username instead of POST
        $review_count = (int)$_POST["rating"]; // Ensure the rating is an integer
        $comment = $_POST["comment"];

        // Check if comment is empty
        if (empty($comment)) {
            echo "Error: Comment is required."; // Optional: You can keep this or remove it.
            return;
        }

        // Get the current date
        $date = date("Y-m-d");

        // Create the document to be inserted
        $document = [
            'UserName' => $user_name,
            'ReviewCount' => $review_count,
            'Comment' => $comment,
            'date' => $date,
            'likeCount' => 0, // Initialize likeCount (optional)
            'dislikeCount' => 0 // Initialize dislikeCount (optional)
        ];

        // Insert the document into the MongoDB collection
        $insertResult = $collection->insertOne($document);

        // Check if the insertion was successful
        if ($insertResult->getInsertedCount() === 1) {
            header("Location: index.php"); // Redirect on success
            exit;
        } else {
            echo "Error: Unable to insert the document.";
        }
    } else {
        echo "Error: Comment is required."; // Optional: You can keep this or remove it.
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?>
