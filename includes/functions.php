<?php
// Make sure config is loaded
require_once __DIR__ . '/config.php';

// Rest of your functions...
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isTeacher() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher');
}

function isStudent() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'student');
}

// Add this new function
function isAdmin() {
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

// Update requireTeacher function to prevent admin access
function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        header('Location: /mywebsite10/index.php');
        exit();
    }
}

// Update requireStudent function to prevent admin access
function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: /mywebsite10/index.php');
        exit();
    }
}

// Add new requireAdmin function
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /mywebsite10/index.php');
        exit();
    }
}


function setMessage($message, $type = 'success') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $class = $message['type'] === 'error' ? 'error-message' : 'success-message';
        echo "<div class='$class'>{$message['text']}</div>";
        unset($_SESSION['message']);
    }
}

function uploadFile($file, $folder) {
    // Use absolute path
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/mywebsite10/assets/uploads/$folder/";
    
    // Debug - create a log file
    $log = fopen($_SERVER['DOCUMENT_ROOT'] . "/mywebsite10/upload_log.txt", "a");
    fwrite($log, date('Y-m-d H:i:s') . " - Upload attempt: " . $file['name'] . "\n");
    fwrite($log, "Target dir: " . $upload_dir . "\n");
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
        fwrite($log, "Created directory\n");
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        fwrite($log, "Directory not writable\n");
        fclose($log);
        return false;
    }
    
    $file_name = time() . '_' . basename($file['name']);
    $target_path = $upload_dir . $file_name;
    
    // Allowed file types
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'mp4', 'txt'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    fwrite($log, "File extension: " . $file_extension . "\n");
    
    if (!in_array($file_extension, $allowed_types)) {
        fwrite($log, "File type not allowed\n");
        fclose($log);
        return false;
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        fwrite($log, "File moved successfully to: " . $target_path . "\n");
        fclose($log);
        return "assets/uploads/$folder/$file_name";
    } else {
        fwrite($log, "Failed to move file. Error: " . $file['error'] . "\n");
        fclose($log);
        return false;
    }

    function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}


}
?>