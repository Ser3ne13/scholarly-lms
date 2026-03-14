<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../models/Section.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateSection();
        break;
    case 'edit':
        handleEditSection();
        break;
    case 'update':
        handleUpdateSection();
        break;
    case 'delete':
        handleDeleteSection();
        break;
    case 'add_student':
        handleAddStudent();
        break;
    case 'remove_student':
        handleRemoveStudent();
        break;
    default:
        header('Location: /mywebsite10/views/admin/sections.php');
        exit();
}

function handleCreateSection() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/admin/create-section.php');
        exit();
    }
    
    $section = new Section();
    $data = [
        'section_name' => $_POST['section_name'],
        'academic_year' => $_POST['academic_year'],
        'year_level' => $_POST['year_level']
    ];
    
    if ($section->createSection($data)) {
        $_SESSION['success'] = 'Section created successfully!';
        header('Location: /mywebsite10/views/admin/sections.php');
    } else {
        $_SESSION['error'] = 'Failed to create section.';
        header('Location: /mywebsite10/views/admin/create-section.php');
    }
    exit();
}

function handleEditSection() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $section_id = $_GET['id'] ?? 0;
    $section = new Section();
    $section_data = $section->getSection($section_id);
    
    if (!$section_data) {
        header('Location: /mywebsite10/views/admin/sections.php');
        exit();
    }
    
    $_SESSION['edit_section'] = $section_data;
    header("Location: /mywebsite10/views/admin/edit-section.php?id=$section_id");
    exit();
}

function handleUpdateSection() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $section_id = $_POST['section_id'];
    $section = new Section();
    
    $data = [
        'section_name' => $_POST['section_name'],
        'academic_year' => $_POST['academic_year'],
        'year_level' => $_POST['year_level']
    ];
    
    if ($section->updateSection($section_id, $data)) {
        $_SESSION['success'] = 'Section updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update section.';
    }
    
    header('Location: /mywebsite10/views/admin/sections.php');
    exit();
}

function handleDeleteSection() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $section_id = $_POST['section_id'] ?? $_GET['id'] ?? 0;
    $section = new Section();
    
    if ($section->deleteSection($section_id)) {
        $_SESSION['success'] = 'Section deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete section.';
    }
    
    header('Location: /mywebsite10/views/admin/sections.php');
    exit();
}

function handleAddStudent() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $student_id = $_POST['student_id'];
    $section_id = $_POST['section_id'];
    
    $section = new Section();
    
    if ($section->addStudentToSection($student_id, $section_id)) {
        $_SESSION['success'] = 'Student added to section!';
    } else {
        $_SESSION['error'] = 'Student already in section or failed to add.';
    }
    
    header("Location: /mywebsite10/views/admin/view-section.php?id=$section_id");
    exit();
}

function handleRemoveStudent() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $student_id = $_GET['student_id'];
    $section_id = $_GET['section_id'];
    
    $section = new Section();
    
    if ($section->removeStudentFromSection($student_id, $section_id)) {
        $_SESSION['success'] = 'Student removed from section!';
    } else {
        $_SESSION['error'] = 'Failed to remove student.';
    }
    
    header("Location: /mywebsite10/views/admin/view-section.php?id=$section_id");
    exit();
}
?>