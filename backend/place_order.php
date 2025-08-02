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

// Check if cart data is provided
if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    echo json_encode(["error" => "Cart data missing"]);
    exit();
}

// Check if delivery address is provided
if (!isset($data['delivery_address']) || empty(trim($data['delivery_address']))) {
    echo json_encode(["error" => "Delivery address is required"]);
    exit();
}

// Sanitize the delivery address
$delivery_address = trim($data['delivery_address']);

// Get the cart data
$cart = $data['cart'];

// Calculate the total price for the order
$total_price = 0;
foreach ($cart as $item) {
    $total_price += $item['price'] * $item['quantity']; // Calculate total price
}

// Ensure $pdo is set correctly
if (!$pdo) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

// Get the current time and calculate estimated delivery time (45 minutes from now)
$created_at = date('Y-m-d H:i:s'); // Current time
$estimated_delivery = date('Y-m-d H:i:s', strtotime('+45 minutes', strtotime($created_at))); // 45 minutes later

// Prepare to insert the order into the orders table
try {
    // Start a database transaction
    $pdo->beginTransaction();

    // Insert into orders table with delivery address and estimated delivery time
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, delivery_address, created_at, estimated_delivery) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total_price, $delivery_address, $created_at, $estimated_delivery]);
    $order_id = $pdo->lastInsertId(); // Get the last inserted order ID

    // Insert cart items into the order_items table
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Commit the transaction
    $pdo->commit();

    // Return success response
    echo json_encode([
        "success" => true,
        "message" => "Order placed successfully",
        "order_id" => $order_id,
        "delivery_address" => $delivery_address,
        "estimated_delivery" => $estimated_delivery // Include the estimated delivery time in the response
    ]);

} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "error" => "Error placing order: " . $e->getMessage()
    ]);
}

// End output buffering and send the response
ob_end_flush();
?>
