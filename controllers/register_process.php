<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Database connection failed");
}

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'student';

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match';
    header('Location: /mywebsite10/views/auth/register.php');
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters';
    header('Location: /mywebsite10/views/auth/register.php');
    exit();
}

// Check if email exists
$check_sql = "SELECT user_id FROM users WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $_SESSION['error'] = 'Email already exists';
    header('Location: /mywebsite10/views/auth/register.php');
    exit();
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users (email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $email, $password_hash, $first_name, $last_name, $role);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Registration successful! Please login.';
    header('Location: /mywebsite10/views/auth/login.php');
} else {
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: /mywebsite10/views/auth/register.php');
}
exit();
?>