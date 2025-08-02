<?php
// Start output buffering to prevent header errors
ob_start();

// Set the session save path to the same folder for consistency
session_save_path(__DIR__ . "/sessions");
if (!is_dir(__DIR__ . "/sessions")) {
    mkdir(__DIR__ . "/sessions", 0777, true); // Create the folder if it doesn't exist
}

ini_set('session.cookie_domain', '.localhost'); // Ensure session cookie is set for localhost
ini_set('session.cookie_lifetime', 0); // Session cookie lasts until the browser is closed
ini_set('session.save_path', __DIR__ . "/sessions"); // Ensure it's the same path

// Start the session
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'database_connect.php';

// Set the content type for the response to JSON
header('Content-Type: application/json');

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Authentication required. Please log in."]);
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request"]);
    exit();
}

// Get JSON input from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if the review data is provided
if (!$data || !isset($data['review_text']) || !isset($data['rating'])) {
    echo json_encode(["error" => "Missing review data"]);
    exit();
}

$review_text = $data['review_text'];
$rating = (int)$data['rating']; // Ensure the rating is an integer

// Ensure $pdo is set correctly
if (!$pdo) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Insert the review into the database using PDO
try {
    // Prepare the insert query using PDO
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, rating, review_text, review_date) VALUES (?, ?, ?, NOW())");
    
    // Execute the query with values
    $stmt->execute([$_SESSION['user_id'], $rating, $review_text]);

    // Return success response
    echo json_encode(["success" => "Review submitted successfully."]);

} catch (PDOException $e) {
    // Catch any database errors and return them
    echo json_encode(["error" => "Error submitting review: " . $e->getMessage()]);
}

// End output buffering and send the response
ob_end_flush();
?>
