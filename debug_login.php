<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'config/database.php';

echo "<h1>🔍 Login Debug Tool</h1>";

// Test database connection
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("<span style='color:red'>❌ Database connection failed</span>");
}
echo "<span style='color:green'>✅ Database connected</span><br><br>";

// Show all users in database
echo "<h2>Users in Database:</h2>";
$result = $conn->query("SELECT user_id, email, first_name, last_name, role, password_hash FROM users");
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th><th>Password Hash (first 20 chars)</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['user_id'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "<td>" . substr($row['password_hash'], 0, 20) . "...</td>";
    echo "</tr>";
}
echo "</table><br>";

// Test login with hardcoded credentials
echo "<h2>Test Login:</h2>";

$test_email = 'teacher@test.com';
$test_password = 'password123';

echo "Testing with: <strong>$test_email / $test_password</strong><br><br>";

// Method 1: Direct query without password_verify
echo "<h3>Test 1: Direct comparison (password_hash = entered password)</h3>";
$sql = "SELECT * FROM users WHERE email = '$test_email' AND password_hash = '$test_password'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    echo "<span style='color:green'>✅ Direct comparison SUCCESSFUL</span><br>";
} else {
    echo "<span style='color:red'>❌ Direct comparison FAILED</span><br>";
}

// Method 2: Using prepared statement
echo "<h3>Test 2: Prepared statement with direct comparison</h3>";
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND password_hash = ?");
$stmt->bind_param("ss", $test_email, $test_password);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<span style='color:green'>✅ Prepared statement SUCCESSFUL</span><br>";
} else {
    echo "<span style='color:red'>❌ Prepared statement FAILED</span><br>";
}

// Method 3: Get user first, then verify
echo "<h3>Test 3: Get user then password_verify</h3>";
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $test_email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "✅ User found in database<br>";
    echo "Stored hash: " . $row['password_hash'] . "<br>";
    
    if (password_verify($test_password, $row['password_hash'])) {
        echo "<span style='color:green'>✅ password_verify SUCCESSFUL</span><br>";
    } else {
        echo "<span style='color:red'>❌ password_verify FAILED</span><br>";
        
        // Generate correct hash for this password
        $correct_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "Correct hash for '$test_password': <strong>" . $correct_hash . "</strong><br>";
        echo "Run this SQL to fix:<br>";
        echo "<code>UPDATE users SET password_hash = '$correct_hash' WHERE email = '$test_email';</code><br>";
    }
} else {
    echo "<span style='color:red'>❌ User not found with email: $test_email</span><br>";
}

// Check if sessions are working
echo "<h2>Session Test:</h2>";
$_SESSION['test'] = 'working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    echo "<span style='color:green'>✅ Sessions are working</span><br>";
} else {
    echo "<span style='color:red'>❌ Sessions are NOT working</span><br>";
}

// Check file paths
echo "<h2>Path Test:</h2>";
echo "Current script: " . __FILE__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>