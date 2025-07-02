<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$uri = $_ENV['MONGODB_URI'];
$databaseName = "ThisaraTravels";

try {
    $client = new MongoDB\Client($uri);
    $database = $client->$databaseName;
    $usersCollection = $database->users;

    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        // Fetch all users
        $users = $usersCollection->find()->toArray();
        if (!$users) {
            echo json_encode(['error' => 'No users found in the database.']);
            exit();
        }

        // Prepare users for JSON output
        $response = [];
        foreach ($users as $user) {
            $response[] = [
                'id' => (string) $user['_id'], // Convert MongoDB ObjectId to string
                'name' => $user['UserName'] ?? 'N/A', // Correct field name for username
                'email' => $user['Email'] ?? 'N/A',
                'username' => $user['UserName'] ?? 'N/A', // Ensure it uses the correct field name
                'role' => $user['role'] ?? 'user', // Use 'role' field (lowercase)
                'date' => $user['date'] ?? 'N/A'
            ];
        }

        echo json_encode($response);
        exit();
    }

    // Handle POST request - Update or Delete a user
    $input = json_decode(file_get_contents('php://input'), true);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Update the user's role
        if (isset($input['id']) && isset($input['role'])) {
            $id = $input['id'];
            $newRole = $input['role'];

            // Update the user role in the database
            $updateResult = $usersCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => ['role' => $newRole]]
            );

            if ($updateResult->getModifiedCount() === 1) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Unable to update user role.']);
            }
        } 
        // Delete the user
        else if (isset($input['id'])) {
            $id = $input['id'];

            // Delete the user
            $deleteResult = $usersCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

            if ($deleteResult->getDeletedCount() === 1) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Unable to delete user.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        }
        exit();
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}

