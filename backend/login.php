<?php
session_save_path(__DIR__ . "/sessions"); // Store sessions locally
if (!is_dir(__DIR__ . "/sessions")) {
    mkdir(__DIR__ . "/sessions", 0777, true); // Create the folder if it doesn't exist
}

ini_set('session.cookie_domain', '.localhost'); // Ensure session cookie is set for localhost
ini_set('session.cookie_lifetime', 0); // Session cookie lasts until the browser is closed
ini_set('session.save_path', __DIR__ . "/sessions"); // Ensure it's the same path

session_start();
include 'database_connect.php'; // Ensure the database connection is included

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']); // Trim spaces to prevent accidental issues
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            $redirect_page = ($user['role'] === 'staff') ? "../frontend/staff_dashboard.html" : "../frontend/dashboard.html";
            header("Location: $redirect_page");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password"; // Store error in session
            header("Location: ../frontend/login.html"); // Redirect back to login page
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage()); // Log error for debugging
        $_SESSION['login_error'] = "System error. Please try later.";
        header("Location: ../frontend/login.html");
        exit();
    }
}
?>
