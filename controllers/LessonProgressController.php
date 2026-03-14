<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once __DIR__ . '/../models/LessonProgress.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete') {
    
    $lesson_id = $_POST['lesson_id'] ?? 0;
    $subject_id = $_POST['subject_id'] ?? 0;
    
    $progress = new LessonProgress();
    $result = $progress->markCompleted($_SESSION['user_id'], $lesson_id);
    
    if ($result) {
        $_SESSION['success'] = 'Lesson marked as completed!';
    } else {
        $_SESSION['error'] = 'Failed to mark lesson as completed.';
    }
    
    // Redirect back to the subject page
    header("Location: /mywebsite10/views/student/view-subject.php?id=$subject_id");
    exit();
}

// If we get here, redirect to subjects page
header('Location: /mywebsite10/views/student/subjects.php');
exit();
?>