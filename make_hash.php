<?php
echo "<h2>Password Hash Generator</h2>";

$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: <strong>" . $password . "</strong><br>";
echo "Hash: <strong>" . $hash . "</strong><br>";
echo "<br>";
echo "Copy this SQL to update your database:<br>";
echo "<textarea rows='5' cols='80' style='padding:10px;'>";
echo "UPDATE users SET password_hash = '" . $hash . "' WHERE email = 'teacher@test.com';\n";
echo "UPDATE users SET password_hash = '" . $hash . "' WHERE email = 'student@test.com';";
echo "</textarea>";
?>