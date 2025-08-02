<?php
// Include database connection
include 'database_connect.php';

header("Content-Type: application/json");

try {
    // Fetch menu items from the 'menu' table
    $stmt = $pdo->query("SELECT product_id, name, description, price, category, is_spicy, is_vegetarian, image_url, is_available FROM menu");
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$menuItems) {
        echo json_encode(["error" => "No menu items found"]);
        exit;
    }

    echo json_encode($menuItems, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
