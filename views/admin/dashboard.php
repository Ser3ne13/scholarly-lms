<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/User.php';
require_once '../../models/Subject.php';
require_once '../../models/Enrollment.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$userModel = new User();
$subjectModel = new Subject();
$enrollmentModel = new Enrollment();

// Get real statistics
$total_users = count($userModel->getAllUsers());
$total_teachers = count($userModel->getAllTeachers());
$total_students = count($userModel->getAllStudents());
$total_subjects = count($subjectModel->getAllSubjects());

// Get recent activities
$recent_users = array_slice($userModel->getAllUsers(), 0, 5);
$recent_subjects = array_slice($subjectModel->getAllSubjects(), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .stat-icon.primary { background: #eef2ff; color: #4f46e5; }
        .stat-icon.success { background: #dcfce7; color: #10b981; }
        .stat-icon.warning { background: #fff3cd; color: #f59e0b; }
        .stat-icon.info { background: #cffafe; color: #06b6d4; }

        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #64748b;
            font-size: 14px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border: 1px solid #eef2f6;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: #4f46e5;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            background: #eef2ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #4f46e5;
            font-size: 24px;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .action-desc {
            font-size: 12px;
            color: #64748b;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .recent-section {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f6;
        }

        .section-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h2 i {
            color: #4f46e5;
        }

        .recent-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
        }

        .recent-item:hover {
            background: #f8fafc;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 15px;
        }

        .recent-info {
            flex: 1;
        }

        .recent-title {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .recent-meta {
            font-size: 12px;
            color: #64748b;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .view-all {
            color: #4f46e5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .quick-actions {
                grid-template-columns: 1fr;
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar"></i> Master Schedule</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="admin-container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h1>Welcome back, <?php echo $_SESSION['first_name']; ?>! 👋</h1>
                <p>Here's what's happening in your system today.</p>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_teachers; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_subjects; ?></h3>
                        <p>Subjects</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="create-user.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="action-title">Add New User</div>
                    <div class="action-desc">Create teacher or student account</div>
                </a>
                
                <a href="create-subject.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-book-medical"></i>
                    </div>
                    <div class="action-title">Create Subject</div>
                    <div class="action-desc">Add new subject to system</div>
                </a>
                
                <a href="subjects.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="action-title">Assign Teacher</div>
                    <div class="action-desc">Assign teacher to subject</div>
                </a>
                
                <a href="settings.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <div class="action-title">System Settings</div>
                    <div class="action-desc">Configure system parameters</div>
                </a>
            </div>

            <!-- Recent Activity Grid -->
            <div class="dashboard-grid">
                <!-- Recent Users -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-plus"></i> Recently Joined</h2>
                        <a href="users.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (empty($recent_users)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">No users yet</p>
                    <?php else: ?>
                        <?php foreach ($recent_users as $user): ?>
                            <div class="recent-item">
                                <div class="recent-avatar">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-title"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                    <div class="recent-meta">
                                        <span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                                        • Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Subjects -->
                <div class="recent-section">
                    <div class="section-header">
                        <h2><i class="fas fa-book"></i> Recent Subjects</h2>
                        <a href="subjects.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if (empty($recent_subjects)): ?>
                        <p style="text-align: center; color: #64748b; padding: 20px;">No subjects yet</p>
                    <?php else: ?>
                        <?php foreach ($recent_subjects as $subject): ?>
                            <div class="recent-item">
                                <div class="recent-avatar" style="background: #f59e0b;">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="recent-info">
                                    <div class="recent-title"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                    <div class="recent-meta">
                                        <span class="badge badge-<?php echo $subject['status']; ?>"><?php echo ucfirst($subject['status']); ?></span>
                                        • <?php echo $subject['subject_code']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div style="background: white; border-radius: 20px; padding: 20px; text-align: center;">
                    <div style="font-size: 36px; font-weight: 700; color: #4f46e5;"><?php echo $total_users; ?></div>
                    <div style="color: #64748b;">Total Users</div>
                </div>
                <div style="background: white; border-radius: 20px; padding: 20px; text-align: center;">
                    <div style="font-size: 36px; font-weight: 700; color: #10b981;"><?php echo $total_subjects; ?></div>
                    <div style="color: #64748b;">Total Subjects</div>
                </div>
                <div style="background: white; border-radius: 20px; padding: 20px; text-align: center;">
                    <?php
                    $total_enrollments = 0;
                    $all_subjects = $subjectModel->getAllSubjects();
                    foreach ($all_subjects as $subj) {
                        $total_enrollments += $enrollmentModel->getEnrolledStudentsCount($subj['subject_id']);
                    }
                    ?>
                    <div style="font-size: 36px; font-weight: 700; color: #f59e0b;"><?php echo $total_enrollments; ?></div>
                    <div style="color: #64748b;">Total Enrollments</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>