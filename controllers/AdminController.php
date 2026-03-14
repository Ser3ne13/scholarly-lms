<?php
session_start();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_users':
        getUsers();
        break;
    case 'create_user':
        handleCreateUser();
        break;
    case 'edit_user':
        handleEditUser();
        break;
    case 'update_user':
        handleUpdateUser();
        break;
    case 'delete_user':
        handleDeleteUser();
        break;
    case 'toggle_status':
        toggleUserStatus();
        break;
    default:
        header('Location: /mywebsite10/views/admin/users.php');
        break;
}

function getUsers() {
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    
    $userModel = new User();
    $users = $userModel->getAllUsers();
    
    header('Content-Type: application/json');
    echo json_encode($users);
    exit();
}

function handleCreateUser() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/admin/create-user.php');
        exit();
    }
    
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters';
        header('Location: /mywebsite10/views/admin/create-user.php');
        exit();
    }
    
    $userModel = new User();
    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'password' => $password,
        'role' => $role
    ];
    
    if ($userModel->register($data)) {
        $_SESSION['success'] = 'User created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create user. Email may already exist.';
    }
    
    header('Location: /mywebsite10/views/admin/users.php');
    exit();
}

function handleEditUser() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $user_id = $_GET['id'] ?? 0;
    $userModel = new User();
    $user = $userModel->getUserById($user_id);
    
    if (!$user) {
        header('Location: /mywebsite10/views/admin/users.php');
        exit();
    }
    
    $_SESSION['edit_user'] = $user;
    header("Location: /mywebsite10/views/admin/edit-user.php?id=$user_id");
    exit();
}

function handleUpdateUser() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    $userModel = new User();
    
    // Update basic info
    $data = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'role' => $role
    ];
    
    $result = $userModel->updateUser($user_id, $data);
    
    // Update password if provided
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) >= 6) {
            $userModel->updatePassword($user_id, $_POST['new_password']);
        }
    }
    
    if ($result) {
        $_SESSION['success'] = 'User updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update user.';
    }
    
    header('Location: /mywebsite10/views/admin/users.php');
    exit();
}

function handleDeleteUser() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $user_id = $_GET['id'] ?? 0;
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('Location: /mywebsite10/views/admin/users.php');
        exit();
    }
    
    $userModel = new User();
    
    if ($userModel->deleteUser($user_id)) {
        $_SESSION['success'] = 'User deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete user.';
    }
    
    header('Location: /mywebsite10/views/admin/users.php');
    exit();
}

function toggleUserStatus() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $user_id = $_GET['id'] ?? 0;
    $status = $_GET['status'] ?? 'active';
    
    $userModel = new User();
    $userModel->updateUserStatus($user_id, $status);
    
    $_SESSION['success'] = 'User status updated!';
    header('Location: /mywebsite10/views/admin/users.php');
    exit();
}
?>