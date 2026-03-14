<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/User.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$userModel = new User();
$teachers = $userModel->getAllTeachers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Subject - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            padding: 10px 20px;
            background: #f1f5f9;
            border-radius: 40px;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            transform: translateX(-3px);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #eef2f6;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #1e293b;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .section-title i {
            color: #4f46e5;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
        }

        .form-group label i {
            color: #4f46e5;
            margin-right: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .teacher-selector {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .teacher-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .teacher-option:hover {
            background: #eef2ff;
        }

        .teacher-option.selected {
            background: #eef2ff;
            border: 2px solid #4f46e5;
        }

        .teacher-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .teacher-info {
            flex: 1;
        }

        .teacher-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .teacher-email {
            font-size: 12px;
            color: #64748b;
        }

        .radio-input {
            width: 20px;
            height: 20px;
            accent-color: #4f46e5;
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
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
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

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 25px;
            }
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
            <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="subjects.php" class="active"><i class="fas fa-book"></i> Manage Subjects</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar"></i> Master Schedule</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="form-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Create New Subject</h1>
                <a href="subjects.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Subjects
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="form-card">
                <form action="http://localhost/mywebsite10/controllers/AdminSubjectController.php?action=create" method="POST" id="createSubjectForm">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-info-circle"></i>
                            <span>Basic Information</span>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-code"></i> Subject Code</label>
                                <input type="text" name="subject_code" class="form-control" 
                                       placeholder="e.g., CS101" required maxlength="20">
                                <div class="info-text">Unique identifier for the subject</div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-heading"></i> Subject Name</label>
                                <input type="text" name="subject_name" class="form-control" 
                                       placeholder="e.g., Introduction to Programming" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Describe what this subject covers..."></textarea>
                        </div>
                    </div>

                    <!-- Schedule Information -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Schedule Information</span>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Schedule</label>
                                <input type="text" name="schedule" class="form-control" 
                                       placeholder="e.g., Mon/Wed 10:00-11:30">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Room</label>
                                <input type="text" name="room" class="form-control" 
                                       placeholder="e.g., Room 201">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-calendar"></i> Academic Year</label>
                                <select name="academic_year" class="form-control">
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026">2025-2026</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-layer-group"></i> Semester</label>
                                <select name="semester" class="form-control">
                                    <option value="1st Semester">1st Semester</option>
                                    <option value="2nd Semester">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Assignment -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Assign Teacher</span>
                        </div>
                        
                        <div class="teacher-selector">
                            <?php if (empty($teachers)): ?>
                                <div style="text-align: center; padding: 20px; color: #64748b;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p>No teachers available. Please create a teacher account first.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($teachers as $index => $teacher): ?>
                                    <label class="teacher-option">
                                        <input type="radio" name="teacher_id" value="<?php echo $teacher['user_id']; ?>" 
                                            class="radio-input" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                        <div class="teacher-avatar">
                                            <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                                        </div>
                                        <div class="teacher-info">
                                            <div class="teacher-name"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></div>
                                            <div class="teacher-email"><?php echo htmlspecialchars($teacher['email']); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="info-text">
                            <i class="fas fa-info-circle"></i> You can assign or change the teacher later
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-toggle-on"></i>
                            <span>Status</span>
                        </div>
                        
                        <div style="display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="status" value="active" checked> 
                                <span style="font-weight: 500;">Active</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px;">
                                <input type="radio" name="status" value="archived"> 
                                <span style="font-weight: 500;">Archived</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create Subject
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('createSubjectForm').addEventListener('submit', function(e) {
            const subjectCode = document.querySelector('input[name="subject_code"]').value.trim();
            const subjectName = document.querySelector('input[name="subject_name"]').value.trim();
            
            if (!subjectCode || !subjectName) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>