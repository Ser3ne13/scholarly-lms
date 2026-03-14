<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/User.php';
require_once '../../models/Subject.php';
require_once '../../models/Enrollment.php';

$userModel = new User();
$subjectModel = new Subject();
$enrollmentModel = new Enrollment();

// Get all subjects taught by this teacher
$teacher_subjects = $subjectModel->getTeacherSubjects($_SESSION['user_id']);

// Get filter from URL
$selected_subject = $_GET['subject_id'] ?? 'all';

// Get students based on filter
if ($selected_subject !== 'all' && $selected_subject !== '') {
    $students = $enrollmentModel->getEnrolledStudents($selected_subject);
    $current_subject = $subjectModel->getSubject($selected_subject);
} else {
    // Get all students (you might want to modify this based on your needs)
    $students = $userModel->getAllStudents();
    $current_subject = null;
}

// Search functionality
$search_term = $_GET['search'] ?? '';
if ($search_term) {
    $filtered_students = [];
    foreach ($students as $student) {
        if (stripos($student['first_name'], $search_term) !== false || 
            stripos($student['last_name'], $search_term) !== false ||
            stripos($student['email'], $search_term) !== false) {
            $filtered_students[] = $student;
        }
    }
    $students = $filtered_students;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .students-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
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
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-icon.primary { background: #eef2ff; color: #4f46e5; }
        .stat-icon.success { background: #dcfce7; color: #10b981; }
        .stat-icon.warning { background: #fff3cd; color: #f59e0b; }
        .stat-icon.info { background: #cffafe; color: #06b6d4; }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #64748b;
            font-size: 14px;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .subject-filter {
            flex: 1;
            min-width: 250px;
        }

        .subject-filter select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        .search-box {
            flex: 2;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
        }

        .search-box button {
            padding: 12px 24px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .search-box button:hover {
            background: #4338ca;
        }

        .reset-btn {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #475569;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Students Grid */
        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .student-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .student-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
            border-color: #4f46e5;
        }

        .student-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            font-weight: 600;
            border: 4px solid white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .student-name {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
            margin-bottom: 5px;
        }

        .student-email {
            color: #64748b;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }

        .student-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px 0;
            border-top: 1px solid #eef2f6;
            border-bottom: 1px solid #eef2f6;
        }

        .meta-item {
            text-align: center;
        }

        .meta-value {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }

        .meta-label {
            font-size: 12px;
            color: #64748b;
        }

        .student-subjects {
            margin-bottom: 20px;
        }

        .subject-tag {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            margin: 0 3px 5px;
        }

        .view-details-btn {
            display: block;
            background: #f8fafc;
            color: #1e293b;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .view-details-btn:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 24px;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-btn {
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-btn:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .page-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-bar {
                flex-direction: column;
            }

            .search-box {
                width: 100%;
            }

            .students-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="modern-header">
        <div class="header-content">
            <div class="logo">Scholarly</div>
            <div class="user-menu">
                <span class="user-avatar">
                    <?php echo substr($_SESSION['first_name'] ?? 'U', 0, 1) . substr($_SESSION['last_name'] ?? 'S', 0, 1); ?>
                </span>
            </div>
        </div>
    </header>

    <aside class="modern-sidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="subjects.php"><i class="fas fa-book"></i> My Subjects</a></li>
            <li><a href="students.php" class="active"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="quizzes.php"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="students-container">
            <!-- Page Header -->
            <div class="page-header" style="margin-bottom: 30px;">
                <h1>Students Management</h1>
                <p>View and manage all students enrolled in your subjects</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <?php
                // Calculate stats
                $total_students = count($userModel->getAllStudents());
                $total_enrollments = 0;
                $active_subjects = count($teacher_subjects);
                $recent_students = 0;
                
                foreach ($teacher_subjects as $subject) {
                    $total_enrollments += count($enrollmentModel->getEnrolledStudents($subject['subject_id']));
                }
                ?>
                
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_enrollments; ?></h3>
                        <p>Total Enrollments</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $active_subjects; ?></h3>
                        <p>Active Subjects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($students); ?></h3>
                        <p>Showing Now</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="subject-filter">
                    <select onchange="filterBySubject(this.value)">
                        <option value="all" <?php echo $selected_subject == 'all' ? 'selected' : ''; ?>>All Subjects</option>
                        <?php foreach ($teacher_subjects as $subject): ?>
                            <option value="<?php echo $subject['subject_id']; ?>" 
                                <?php echo $selected_subject == $subject['subject_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-box">
                    <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                        <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                        <input type="text" name="search" placeholder="Search by name or email..." 
                               value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
                
                <?php if ($search_term || $selected_subject != 'all'): ?>
                    <a href="students.php" class="reset-btn">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </div>

            <!-- Students Grid -->
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Students Found</h3>
                    <p>
                        <?php if ($selected_subject != 'all'): ?>
                            No students are enrolled in this subject yet.
                        <?php elseif ($search_term): ?>
                            No students match your search criteria.
                        <?php else: ?>
                            You don't have any students yet.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="students-grid">
                    <?php foreach ($students as $student): 
                        // Get subjects this student is enrolled in for this teacher
                        $student_subjects = [];
                        foreach ($teacher_subjects as $subject) {
                            if ($enrollmentModel->isEnrolled($student['user_id'], $subject['subject_id'])) {
                                $student_subjects[] = $subject;
                            }
                        }
                    ?>
                        <div class="student-card">
                            <div class="student-avatar">
                                <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                            </div>
                            
                            <div class="student-name">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </div>
                            
                            <div class="student-email">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student['email']); ?>
                            </div>
                            
                            <div class="student-meta">
                                <div class="meta-item">
                                    <div class="meta-value"><?php echo count($student_subjects); ?></div>
                                    <div class="meta-label">Subjects</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-value"><?php echo date('M Y', strtotime($student['created_at'])); ?></div>
                                    <div class="meta-label">Joined</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-value">
                                        <?php 
                                        $last_login = isset($student['last_login']) ? date('d', strtotime($student['last_login'])) : 'N/A';
                                        echo $last_login;
                                        ?>
                                    </div>
                                    <div class="meta-label">Last Active</div>
                                </div>
                            </div>
                            
                            <?php if (!empty($student_subjects)): ?>
                                <div class="student-subjects">
                                    <?php foreach (array_slice($student_subjects, 0, 3) as $sub): ?>
                                        <span class="subject-tag">
                                            <?php echo htmlspecialchars($sub['subject_code']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($student_subjects) > 3): ?>
                                        <span class="subject-tag">+<?php echo count($student_subjects) - 3; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="student-details.php?id=<?php echo $student['user_id']; ?>" class="view-details-btn">
                                <i class="fas fa-user-graduate"></i> View Profile
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (count($students) > 20): ?>
                    <div class="pagination">
                        <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">4</button>
                        <button class="page-btn">5</button>
                        <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function filterBySubject(subjectId) {
            window.location.href = 'students.php?subject_id=' + subjectId;
        }
    </script>
</body>
</html>