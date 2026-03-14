<?php
require_once 'config/database.php';

echo "<h1>Lesson File Check</h1>";

$db = new Database();
$conn = $db->connect();

// Check all lessons
$result = $conn->query("SELECT lesson_id, title, file_path FROM lessons");

if ($result->num_rows == 0) {
    echo "<p>No lessons found in database.</p>";
} else {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Title</th><th>File Path</th><th>File Exists?</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['lesson_id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . ($row['file_path'] ?: 'No file') . "</td>";
        
        // Check if file exists
        if ($row['file_path']) {
            $full_path = __DIR__ . '/' . $row['file_path'];
            $exists = file_exists($full_path) ? '✅ Yes' : '❌ No';
            echo "<td>" . $exists . "</td>";
        } else {
            echo "<td>No file</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Check upload directory
echo "<h2>Upload Directory</h2>";
$upload_dir = __DIR__ . '/assets/uploads/lessons/';
echo "Path: " . $upload_dir . "<br>";

if (file_exists($upload_dir)) {
    echo "✅ Directory exists<br>";
    $files = scandir($upload_dir);
    echo "Files found: " . count($files) . "<br>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "📄 " . $file . "<br>";
        }
    }
} else {
    echo "❌ Directory does not exist<br>";
    // Try to create it
    if (mkdir($upload_dir, 0777, true)) {
        echo "✅ Directory created successfully<br>";
    } else {
        echo "❌ Failed to create directory<br>";
    }
}

// List actual files in the lessons folder
echo "<h3>Files in lessons folder:</h3>";
$files = scandir($upload_dir);
$has_files = false;
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $has_files = true;
        $file_path = "assets/uploads/lessons/$file";
        $file_size = filesize($upload_dir . $file);
        echo "📄 " . $file . " - " . round($file_size/1024, 2) . " KB<br>";
        
        // Check if this file is linked to any lesson
        $check_sql = "SELECT lesson_id, title FROM lessons WHERE file_path LIKE '%$file%'";
        $check_result = $conn->query($check_sql);
        if ($check_result->num_rows > 0) {
            $lesson = $check_result->fetch_assoc();
            echo "   ✅ Linked to lesson: " . $lesson['title'] . " (ID: " . $lesson['lesson_id'] . ")<br>";
        } else {
            echo "   ❌ Not linked to any lesson<br>";
        }
    }
}
if (!$has_files) {
    echo "No files found in lessons folder.<br>";
}
?>