<?php
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireLogin() {
    initSession();
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'views/auth/login.php');
        exit();
    }
}

function requireTeacher() {
    requireLogin();
    if (!isTeacher()) {
        header('Location: ' . SITE_URL . 'views/student/dashboard.php');
        exit();
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        header('Location: ' . SITE_URL . 'views/teacher/dashboard.php');
        exit();
    }
}
?>