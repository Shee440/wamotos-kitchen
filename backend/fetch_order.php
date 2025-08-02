<?php
// Include the database connection
include 'database_connect.php';

header("Content-Type: application/json");

// Check if order_id is provided in the query string
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo json_encode(["error" => "Order ID is required"]);
    exit;
}

// Get the order_id from the query string
$order_id = $_GET['order_id'];

try {
    // Fetch the basic order details (status, estimated delivery, delivery address)
    $stmt = $pdo->prepare("SELECT o.order_id, o.status, o.delivery_address, o.estimated_delivery
                           FROM orders o
                           WHERE o.order_id = ?");
    $stmt->execute([$order_id]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(["error" => "Order not found"]);
        exit;
    }

    // Fetch the items in the order
    $stmt = $pdo->prepare("SELECT oi.product_id, oi.quantity, oi.price, m.name 
                           FROM order_items oi
                           JOIN menu m ON oi.product_id = m.product_id
                           WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the order details and items as JSON
    echo json_encode([
        "success" => true,
        "status" => $order['status'],
        "estimatedDelivery" => $order['estimated_delivery'],
        "deliveryAddress" => $order['delivery_address'],
        "items" => $items
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>

