<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'database_connect.php';

$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$action) {
    // Fetch orders
    $orders = $pdo->query("
        SELECT o.order_id, u.full_name AS user_name, o.total_price, o.status, o.created_at, o.delivery_address, o.estimated_delivery
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch menu
    $menu = $pdo->query("
        SELECT product_id, name, description, price, category, is_spicy, is_vegetarian, image_url, is_available
        FROM menu
    ")->fetchAll(PDO::FETCH_ASSOC);
    $reviews = $pdo->query("
    SELECT r.rating, r.review_text, r.review_date, u.full_name AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    ORDER BY r.review_date DESC
")->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode(['orders' => $orders, 'menu' => $menu, 'reviews' => $reviews]);
    exit;
}

// Handle POST actions
$data = json_decode(file_get_contents("php://input"), true);

switch ($action) {
    case 'update_status':
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$data['status'], $data['order_id']]);
        echo json_encode(['message' => 'Order status updated']);
        break;

    case 'add_menu':
        $stmt = $pdo->prepare("
            INSERT INTO menu (name, description, price, category, is_spicy, is_vegetarian, image_url, is_available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category'],
            $data['is_spicy'],
            $data['is_vegetarian'],
            $data['image_url'],
            $data['is_available']
        ]);
        echo json_encode(['message' => 'Menu item added']);
        break;

    case 'delete_menu':
        $stmt = $pdo->prepare("DELETE FROM menu WHERE product_id = ?");
        $stmt->execute([$data['product_id']]);
        echo json_encode(['message' => 'Menu item deleted']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
