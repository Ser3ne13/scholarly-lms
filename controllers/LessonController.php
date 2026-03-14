<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateLesson();
        break;
    case 'edit':
        handleEditLesson();
        break;
    case 'update':
        handleUpdateLesson();
        break;
    case 'delete':
        handleDeleteLesson();
        break;
    case 'update':
        handleUpdateLesson();
    break;
    case 'delete':
        handleDeleteLesson();
        break;
    default:
        header('Location: /mywebsite10/views/teacher/subjects.php');
        break;
}

function handleCreateLesson() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/teacher/subjects.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $video_url = $_POST['video_url'];
    
    // Handle file upload
    $file_path = '';
    if (isset($_FILES['lesson_file']) && $_FILES['lesson_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['lesson_file'], 'lessons');
        if ($upload_result) {
            $file_path = $upload_result;
        }
    }
    
    $lesson = new Lesson();
    $data = [
        'subject_id' => $subject_id,
        'title' => $title,
        'content' => $content,
        'file_path' => $file_path,
        'video_url' => $video_url
    ];
    
    if ($lesson->createLesson($data)) {
        $_SESSION['success'] = 'Lesson created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create lesson.';
    }
    
    header("Location: /mywebsite10/views/teacher/view-subject.php?id=$subject_id");
    exit();
}

function handleEditLesson() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $lesson_id = $_GET['id'] ?? 0;
    $lesson = new Lesson();
    $lesson_data = $lesson->getLesson($lesson_id);
    
    if (!$lesson_data) {
        header('Location: /mywebsite10/views/teacher/subjects.php');
        exit();
    }
    
    // Store in session for edit page
    $_SESSION['edit_lesson'] = $lesson_data;
    header("Location: /mywebsite10/views/teacher/edit-lesson.php?id=$lesson_id");
    exit();
}

function handleUpdateLesson() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $lesson_id = $_POST['lesson_id'];
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $video_url = $_POST['video_url'];
    
    $lesson = new Lesson();
    $data = [
        'title' => $title,
        'content' => $content,
        'video_url' => $video_url
    ];
    
    // Handle new file upload if provided
    if (isset($_FILES['lesson_file']) && $_FILES['lesson_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['lesson_file'], 'lessons');
        if ($upload_result) {
            $data['file_path'] = $upload_result;
        }
    }
    
    if ($lesson->updateLesson($lesson_id, $data)) {
        $_SESSION['success'] = 'Lesson updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update lesson.';
    }
    
    header("Location: /mywebsite10/views/teacher/view-subject.php?id=$subject_id");
    exit();
}

function handleDeleteLesson() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $lesson_id = $_GET['id'] ?? 0;
    $lesson = new Lesson();
    $lesson_data = $lesson->getLesson($lesson_id);
    
    if ($lesson->deleteLesson($lesson_id)) {
        $_SESSION['success'] = 'Lesson deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete lesson.';
    }
    
    header("Location: /mywebsite10/views/teacher/view-subject.php?id=" . $lesson_data['subject_id']);
    exit();
}
?>