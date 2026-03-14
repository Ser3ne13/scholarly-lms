<?php
session_start();
require_once __DIR__ . '/../models/Assignment.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateAssignment();
        break;
    default:
        header('Location: /mywebsite10/views/teacher/subjects.php');
        break;
}

function handleCreateAssignment() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    
    // Handle file upload
    $file_path = '';
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['assignment_file'], 'assignments');
        if ($upload_result) {
            $file_path = $upload_result;
        }
    }
    
    $assignment = new Assignment();
    $data = [
        'subject_id' => $subject_id,
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'instructions' => $_POST['instructions'],
        'due_date' => $_POST['due_date'],
        'total_points' => $_POST['total_points'],
        'file_path' => $file_path
    ];
    
    if ($assignment->createAssignment($data)) {
        $_SESSION['success'] = 'Assignment created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create assignment.';
    }
    
    header("Location: /mywebsite10/views/teacher/view-subject.php?id=$subject_id");
    exit();
}
?>