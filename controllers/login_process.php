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

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields';
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] === 'teacher') {
            header('Location: /mywebsite10/views/teacher/subjects.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: /mywebsite10/views/admin/dashboard.php');
        } else {
            header('Location: /mywebsite10/views/student/subjects.php');
        }
        exit();
    }

    
}

$_SESSION['error'] = 'Invalid email or password';
header('Location: /mywebsite10/views/auth/login.php');
exit();
?>