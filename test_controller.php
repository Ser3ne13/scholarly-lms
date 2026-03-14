<?php
echo "<h1>File Structure Check</h1>";

$files_to_check = [
    '/controllers/AdminSubjectController.php',
    '/views/admin/subjects.php',
    '/views/admin/create-subject.php',
    '/views/admin/edit-subject.php',
    '/models/Subject.php'
];

echo "<ul>";
foreach ($files_to_check as $file) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/mywebsite10' . $file;
    if (file_exists($full_path)) {
        echo "<li style='color:green'>✅ $file - EXISTS</li>";
    } else {
        echo "<li style='color:red'>❌ $file - NOT FOUND at: $full_path</li>";
    }
}
echo "</ul>";

echo "<h2>Test Controller Access</h2>";
echo "<p><a href='/mywebsite10/controllers/AdminSubjectController.php?action=test' target='_blank'>Click to test controller</a></p>";
?>