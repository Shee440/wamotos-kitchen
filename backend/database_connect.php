<?php
// backend/database_connect.php
$host = 'localhost';
$dbname = 'hotel_food_ordering';
$user = 'root';
$pass = '9417Wekm.';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>