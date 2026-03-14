<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/Subject.php';
require_once '../../models/User.php';
require_once '../../models/Enrollment.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}




$subjectModel = new Subject();
$userModel = new User();
$enrollmentModel = new Enrollment();

// Get all teachers
$teachers = $userModel->getAllTeachers();

// Get all subjects
$all_subjects = $subjectModel->getAllSubjects();

// Organize subjects by teacher
$subjects_by_teacher = [];
$unassigned_subjects = [];

foreach ($all_subjects as $subject) {
    if ($subject['teacher_id']) {
        $subjects_by_teacher[$subject['teacher_id']][] = $subject;
    } else {
        $unassigned_subjects[] = $subject;
    }
}

// Get all students
$all_students = $userModel->getAllStudents();

// Get enrollments by subject
$enrollments_by_subject = [];
foreach ($all_subjects as $subject) {
    $enrollments_by_subject[$subject['subject_id']] = $enrollmentModel->getEnrolledStudents($subject['subject_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organized Subjects - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1e293b;
        }

        .btn-create {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        /* Stats Overview */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-info p {
            color: #64748b;
            font-size: 13px;
        }

        /* Teacher Section */
        .teacher-section {
            background: white;
            border-radius: 24px;
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }

        .teacher-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .teacher-header:hover {
            opacity: 0.95;
        }

        .teacher-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .teacher-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 600;
            border: 2px solid rgba(255,255,255,0.5);
        }

        .teacher-details h2 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .teacher-details p {
            font-size: 13px;
            opacity: 0.9;
        }

        .teacher-stats {
            display: flex;
            gap: 20px;
        }

        .teacher-stat {
            text-align: center;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
        }

        .teacher-stat i {
            margin-right: 5px;
        }

        .toggle-icon {
            font-size: 24px;
        }

        /* Subjects Grid */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 25px;
        }

        .subject-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 20px;
            border: 1px solid #eef2f6;
            transition: all 0.3s;
        }

        .subject-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.15);
            border-color: #4f46e5;
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .subject-code {
            background: #eef2ff;
            color: #4f46e5;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-archived {
            background: #f1f5f9;
            color: #475569;
        }

        .subject-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .subject-schedule {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: #64748b;
        }

        .subject-schedule i {
            color: #4f46e5;
            width: 16px;
        }

        /* Students Section */
        .students-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
        }

        .students-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .students-title i {
            transition: transform 0.3s;
        }

        .students-title.collapsed i {
            transform: rotate(-90deg);
        }

        .students-list {
            max-height: 200px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .student-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            background: white;
            border-radius: 10px;
            margin-bottom: 5px;
            font-size: 12px;
            border: 1px solid #eef2f6;
        }

        .student-avatar-small {
            width: 25px;
            height: 25px;
            background: #eef2ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 10px;
            font-weight: 600;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 500;
        }

        .student-email {
            font-size: 10px;
            color: #64748b;
        }

        .student-count-badge {
            background: #4f46e5;
            color: white;
            padding: 2px 8px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-view {
            background: #eef2ff;
            color: #4f46e5;
        }

        .btn-edit {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-assign {
            background: #dcfce7;
            color: #166534;
        }

        .unassigned-section {
            background: #fff7ed;
            border: 2px dashed #f59e0b;
        }

        .unassigned-header {
            background: #f59e0b;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: #f8fafc;
            border-radius: 20px;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 40px;
            color: #cbd5e1;
            margin-bottom: 10px;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .subjects-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .subjects-grid {
                grid-template-columns: 1fr;
            }
            
            .teacher-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .teacher-stats {
                flex-wrap: wrap;
                justify-content: center;
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
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Organized Subjects</h1>
                <a href="create-subject.php" class="btn-create">
                    <i class="fas fa-plus"></i> Create New Subject
                </a>
            </div>

            <!-- MESSAGES WITH AUTO-DISMISS -->
            <?php if (isset($_SESSION['success'])): ?>
                <div id="successMessage" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; transition: opacity 0.5s;">
                    <i class="fas fa-check-circle"></i> 
                    <span style="flex: 1;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #155724; cursor: pointer; font-size: 18px;">&times;</button>
                </div>
                
                <script>
                    // Auto-dismiss after 3 seconds
                    setTimeout(function() {
                        const msg = document.getElementById('successMessage');
                        if (msg) {
                            msg.style.opacity = '0';
                            setTimeout(function() {
                                if (msg) msg.style.display = 'none';
                            }, 500);
                        }
                    }, 3000);
                </script>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div id="errorMessage" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; transition: opacity 0.5s;">
                    <i class="fas fa-exclamation-circle"></i> 
                    <span style="flex: 1;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    <button onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: #721c24; cursor: pointer; font-size: 18px;">&times;</button>
                </div>
                
                <script>
                    // Auto-dismiss after 3 seconds
                    setTimeout(function() {
                        const msg = document.getElementById('errorMessage');
                        if (msg) {
                            msg.style.opacity = '0';
                            setTimeout(function() {
                                if (msg) msg.style.display = 'none';
                            }, 500);
                        }
                    }, 3000);
                </script>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($teachers); ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #10b981;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($all_subjects); ?></h3>
                        <p>Total Subjects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3cd; color: #f59e0b;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($all_students); ?></h3>
                        <p>Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #cffafe; color: #06b6d4;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php 
                            $total_enrollments = 0;
                            foreach ($enrollments_by_subject as $enrollments) {
                                $total_enrollments += count($enrollments);
                            }
                            echo $total_enrollments;
                        ?></h3>
                        <p>Enrollments</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($unassigned_subjects); ?></h3>
                        <p>Unassigned</p>
                    </div>
                </div>
            </div>

            <!-- Teachers Sections -->
            <?php foreach ($teachers as $teacher): 
                $teacher_subjects = $subjects_by_teacher[$teacher['user_id']] ?? [];
                $total_students = 0;
                foreach ($teacher_subjects as $subject) {
                    $total_students += count($enrollments_by_subject[$subject['subject_id']] ?? []);
                }
            ?>
                <div class="teacher-section">
                    <div class="teacher-header" onclick="toggleSection('teacher-<?php echo $teacher['user_id']; ?>')">
                        <div class="teacher-info">
                            <div class="teacher-avatar">
                                <?php echo strtoupper(substr($teacher['first_name'], 0, 1) . substr($teacher['last_name'], 0, 1)); ?>
                            </div>
                            <div class="teacher-details">
                                <h2><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h2>
                                <p><?php echo htmlspecialchars($teacher['email']); ?></p>
                            </div>
                        </div>
                        <div class="teacher-stats">
                            <span class="teacher-stat"><i class="fas fa-book"></i> <?php echo count($teacher_subjects); ?> Subjects</span>
                            <span class="teacher-stat"><i class="fas fa-users"></i> <?php echo $total_students; ?> Students</span>
                            <span class="toggle-icon"><i class="fas fa-chevron-up" id="icon-teacher-<?php echo $teacher['user_id']; ?>"></i></span>
                        </div>
                    </div>
                    
                    <div id="teacher-<?php echo $teacher['user_id']; ?>" class="subjects-grid">
                        <?php if (empty($teacher_subjects)): ?>
                            <div class="empty-state" style="grid-column: 1 / -1;">
                                <i class="fas fa-book-open"></i>
                                <p>No subjects assigned yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($teacher_subjects as $subject): 
                                $enrolled_students = $enrollments_by_subject[$subject['subject_id']] ?? [];
                            ?>
                                <div class="subject-card">
                                    <div class="subject-header">
                                        <span class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                                        <span class="status-badge status-<?php echo $subject['status']; ?>">
                                            <?php echo ucfirst($subject['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                    
                                    <div class="subject-schedule">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo htmlspecialchars($subject['schedule'] ?? 'No schedule'); ?></span>
                                    </div>
                                    
                                    <div class="subject-schedule">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                                    </div>
                                    
                                    <!-- Students Section -->
                                    <div class="students-section">
                                        <div class="students-title" onclick="toggleStudents('students-<?php echo $subject['subject_id']; ?>', this)">
                                            <i class="fas fa-chevron-down"></i>
                                            <span>Enrolled Students</span>
                                            <span class="student-count-badge"><?php echo count($enrolled_students); ?></span>
                                        </div>
                                        
                                        <div id="students-<?php echo $subject['subject_id']; ?>" class="students-list">
                                            <?php if (empty($enrolled_students)): ?>
                                                <div style="text-align: center; padding: 10px; color: #64748b;">
                                                    <i class="fas fa-user-slash"></i> No students enrolled
                                                </div>
                                            <?php else: ?>
                                                <?php foreach (array_slice($enrolled_students, 0, 5) as $student): ?>
                                                    <div class="student-item">
                                                        <div class="student-avatar-small">
                                                            <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                                        </div>
                                                        <div class="student-info">
                                                            <div class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                                            <div class="student-email"><?php echo htmlspecialchars($student['email']); ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                                
                                                <?php if (count($enrolled_students) > 5): ?>
                                                    <div style="text-align: center; padding: 5px; font-size: 11px; color: #4f46e5;">
                                                        +<?php echo count($enrolled_students) - 5; ?> more students
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="/mywebsite10/views/teacher/view-subject.php?id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="/mywebsite10/controllers/AdminSubjectController.php?action=edit&id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="/mywebsite10/controllers/AdminSubjectController.php?action=edit&id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-assign">
                                            <i class="fas fa-user-tie"></i> Assign Teacher
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Unassigned Subjects Section -->
            <?php if (!empty($unassigned_subjects)): ?>
                <div class="teacher-section unassigned-section">
                    <div class="teacher-header unassigned-header" onclick="toggleSection('unassigned-section')">
                        <div class="teacher-info">
                            <div class="teacher-avatar">
                                <i class="fas fa-question"></i>
                            </div>
                            <div class="teacher-details">
                                <h2>Unassigned Subjects</h2>
                                <p>Subjects without a teacher</p>
                            </div>
                        </div>
                        <div class="teacher-stats">
                            <span class="teacher-stat"><i class="fas fa-book"></i> <?php echo count($unassigned_subjects); ?> Subjects</span>
                            <span class="toggle-icon"><i class="fas fa-chevron-up" id="icon-unassigned-section"></i></span>
                        </div>
                    </div>
                    
                    <div id="unassigned-section" class="subjects-grid">
                        <?php foreach ($unassigned_subjects as $subject): ?>
                            <div class="subject-card">
                                <div class="subject-header">
                                    <span class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                                    <span class="status-badge status-<?php echo $subject['status']; ?>">
                                        <?php echo ucfirst($subject['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                
                                <div class="subject-schedule">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo htmlspecialchars($subject['schedule'] ?? 'No schedule'); ?></span>
                                </div>
                                
                                <div class="subject-schedule">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                                </div>
                                
                                <div style="margin: 15px 0; padding: 10px; background: #fff3cd; border-radius: 10px; font-size: 12px; color: #856404;">
                                    <i class="fas fa-exclamation-triangle"></i> No teacher assigned
                                </div>
                                
                                <div class="card-actions">
                                    <a href="http://localhost/mywebsite10/views/teacher/view-subject.php?id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="edit-subject.php?id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="assign-teacher.php?id=<?php echo $subject['subject_id']; ?>" class="action-btn btn-assign">
                                        <i class="fas fa-user-tie"></i> Assign
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleSection(sectionId) {
            const section = document.getElementById(sectionId);
            const icon = document.getElementById('icon-' + sectionId);
            
            if (section.style.display === 'none') {
                section.style.display = 'grid';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                section.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        
        function toggleStudents(listId, element) {
            const list = document.getElementById(listId);
            const icon = element.querySelector('i');
            
            if (list.style.display === 'none') {
                list.style.display = 'block';
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                list.style.display = 'none';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }
        
        // Initialize all students lists to be visible initially
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.students-list').forEach(list => {
                list.style.display = 'block';
            });
        });

        // Auto-dismiss messages after 3 seconds
setTimeout(function() {
    document.querySelectorAll('.alert-success, .alert-error, .alert').forEach(function(alert) {
        if (alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    alert.style.display = 'none';
                }
            }, 500);
        }
    });
}, 3000);
    </script>
</body>
</html>