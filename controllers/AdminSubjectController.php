<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateSubject();
        break;
    case 'edit':
        handleEditSubject();
        break;
    case 'update':
        handleUpdateSubject();
        break;
    case 'delete':
        handleDeleteSubject();
        break;
    case 'assign_teacher':
        handleAssignTeacher();
        break;
    default:
        header('Location: /mywebsite10/views/admin/subjects.php');
        break;
}

function handleCreateSubject() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/admin/create-subject.php');
        exit();
    }
    
    $subject = new Subject();
    $data = [
        'subject_code' => $_POST['subject_code'] ?? '',
        'subject_name' => $_POST['subject_name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'teacher_id' => !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null,
        'schedule' => $_POST['schedule'] ?? '',
        'room' => $_POST['room'] ?? '',
        'academic_year' => $_POST['academic_year'] ?? '',
        'semester' => $_POST['semester'] ?? '',
        'status' => $_POST['status'] ?? 'active'
    ];
    
    try {
        if ($subject->createSubject($data)) {
            $_SESSION['success'] = 'Subject created successfully!';
            header('Location: /mywebsite10/views/admin/subjects.php');
        } else {
            $_SESSION['error'] = 'Failed to create subject.';
            header('Location: /mywebsite10/views/admin/create-subject.php');
        }
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error'] = 'Subject code already exists. Please use a different code.';
        } else {
            $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        }
        header('Location: /mywebsite10/views/admin/create-subject.php');
    }
    exit();
}

function handleEditSubject() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_GET['id'] ?? 0;
    $subject = new Subject();
    $subject_data = $subject->getSubject($subject_id);
    
    if (!$subject_data) {
        header('Location: /mywebsite10/views/admin/subjects.php');
        exit();
    }
    
    $_SESSION['edit_subject'] = $subject_data;
    header("Location: /mywebsite10/views/admin/edit-subject.php?id=$subject_id");
    exit();
}

function handleUpdateSubject() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $subject = new Subject();
    
    $data = [
        'subject_code' => $_POST['subject_code'],
        'subject_name' => $_POST['subject_name'],
        'description' => $_POST['description'],
        'teacher_id' => $_POST['teacher_id'] ?: null,
        'schedule' => $_POST['schedule'],
        'room' => $_POST['room'],
        'academic_year' => $_POST['academic_year'],
        'semester' => $_POST['semester'],
        'status' => $_POST['status']
    ];
    
    if ($subject->updateSubject($subject_id, $data)) {
        $_SESSION['success'] = 'Subject updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update subject.';
    }
    
    header('Location: /mywebsite10/views/admin/subjects.php');
    exit();
}

function handleDeleteSubject() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'] ?? $_GET['id'] ?? 0;
    $subject = new Subject();
    
    if ($subject->deleteSubject($subject_id)) {
        $_SESSION['success'] = 'Subject deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete subject.';
    }
    
    header('Location: /mywebsite10/views/admin/subjects.php');
    exit();
}

function handleAssignTeacher() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $teacher_id = $_POST['teacher_id'] ?: null;
    
    $subject = new Subject();
    $data = ['teacher_id' => $teacher_id];
    
    // You'll need to add this method to Subject model
    if ($subject->updateSubject($subject_id, $data)) {
        $_SESSION['success'] = 'Teacher assigned successfully!';
    } else {
        $_SESSION['error'] = 'Failed to assign teacher.';
    }
    
    header('Location: /mywebsite10/views/admin/subjects.php');
    exit();
}
?>