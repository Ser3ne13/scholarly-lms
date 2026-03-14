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

// Get user data from session (set by AdminController)
$user = $_SESSION['edit_user'] ?? null;

if (!$user) {
    header('Location: users.php');
    exit();
}

// Clear from session
unset($_SESSION['edit_user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></title>
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
        
        .user-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eef2f6;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 600;
        }
        
        .user-header-info h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .user-header-info p {
            color: #64748b;
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
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
        
        .password-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px dashed #e2e8f0;
        }
        
        .password-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .password-toggle input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
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
            margin-top: 30px;
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .info-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
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
                <span><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></span>
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
                <div class="user-header">
                    <div class="user-avatar-large">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                    <div class="user-header-info">
                        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <form action="/mywebsite10/controllers/AdminController.php?action=update_user" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <div class="role-selector">
                            <div class="role-option <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>" 
                                 onclick="selectRole('admin')" id="roleAdmin">
                                <i class="fas fa-user-shield"></i>
                                <div>Admin</div>
                            </div>
                            <div class="role-option <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>" 
                                 onclick="selectRole('teacher')" id="roleTeacher">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <div>Teacher</div>
                            </div>
                            <div class="role-option <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>" 
                                 onclick="selectRole('student')" id="roleStudent">
                                <i class="fas fa-user-graduate"></i>
                                <div>Student</div>
                            </div>
                        </div>
                        <input type="hidden" name="role" id="selectedRole" value="<?php echo $user['role']; ?>">
                    </div>
                    
                    <!-- Password Change Section -->
                    <div class="password-section">
                        <div class="password-toggle">
                            <input type="checkbox" id="changePassword" onclick="togglePasswordFields()">
                            <label for="changePassword" style="font-weight: 600;">Change Password</label>
                        </div>
                        
                        <div id="passwordFields" style="display: none;">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" id="newPassword" class="form-control" 
                                       placeholder="Enter new password">
                                <div class="info-text">Minimum 6 characters</div>
                            </div>
                            
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" id="confirmPassword" class="form-control" 
                                       placeholder="Confirm new password">
                                <div id="passwordMatchError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;">
                                    Passwords do not match
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit" onclick="return validateForm()">
                        <i class="fas fa-save"></i> Update User
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
        
        function togglePasswordFields() {
            const checkbox = document.getElementById('changePassword');
            const passwordFields = document.getElementById('passwordFields');
            passwordFields.style.display = checkbox.checked ? 'block' : 'none';
        }
        
        function validateForm() {
            const checkbox = document.getElementById('changePassword');
            
            if (checkbox.checked) {
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmPassword').value;
                
                if (newPass.length < 6) {
                    alert('Password must be at least 6 characters long');
                    return false;
                }
                
                if (newPass !== confirmPass) {
                    alert('Passwords do not match');
                    return false;
                }
            }
            
            return true;
        }
        
        // Real-time password match checking
        document.getElementById('confirmPassword')?.addEventListener('keyup', function() {
            const newPass = document.getElementById('newPassword').value;
            const confirmPass = this.value;
            const errorDiv = document.getElementById('passwordMatchError');
            
            if (newPass !== confirmPass && confirmPass.length > 0) {
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none';
            }
        });
    </script>
</body>
</html>