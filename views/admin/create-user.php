<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");


require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .role-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .role-option:hover {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        
        .role-option.selected {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        
        .role-option i {
            font-size: 24px;
            color: #4f46e5;
            margin-bottom: 8px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header class="modern-header">
        <div class="header-content">
            <div class="logo">Scholarly Admin</div>
            <div class="user-menu">
                <span class="user-avatar">
                    <?php echo substr($_SESSION['first_name'] ?? 'A', 0, 1) . substr($_SESSION['last_name'] ?? 'D', 0, 1); ?>
                </span>
                <a href="/mywebsite10/controllers/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <aside class="modern-sidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar"></i> Master Schedule</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="form-container">
            <a href="users.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            
            <div class="form-card">
                <h1 style="margin-bottom: 30px;">Create New User</h1>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form action="/mywebsite10/controllers/AdminController.php?action=create_user" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <small style="color: #64748b;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <div class="role-selector">
                            <div class="role-option selected" onclick="selectRole('admin')" id="roleAdmin">
                                <i class="fas fa-user-shield"></i>
                                <div>Admin</div>
                            </div>
                            <div class="role-option" onclick="selectRole('teacher')" id="roleTeacher">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <div>Teacher</div>
                            </div>
                            <div class="role-option" onclick="selectRole('student')" id="roleStudent">
                                <i class="fas fa-user-graduate"></i>
                                <div>Student</div>
                            </div>
                        </div>
                        <input type="hidden" name="role" id="selectedRole" value="admin">
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create User
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function selectRole(role) {
            document.querySelectorAll('.role-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            document.getElementById('role' + role.charAt(0).toUpperCase() + role.slice(1)).classList.add('selected');
            document.getElementById('selectedRole').value = role;
        }
    </script>
</body>
</html>