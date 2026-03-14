<?php
echo "<h2>Generate Password Hash</h2>";

$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "<br>";
echo "Copy this hash to use in your database:<br>";
echo "<textarea rows='3' cols='60'>" . $hash . "</textarea>";
?>