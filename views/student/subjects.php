<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';
require_once '../../models/Enrollment.php';
require_once '../../models/Lesson.php';
require_once '../../models/LessonProgress.php';

$subjectModel = new Subject();
$enrollmentModel = new Enrollment();
$lessonModel = new Lesson();
$progressModel = new LessonProgress();

// Get subjects the student is enrolled in
$my_subjects = $subjectModel->getStudentSubjects($_SESSION['user_id']);

// Calculate progress for each subject
$subjects_with_progress = [];
foreach ($my_subjects as $subject) {
    $lessons = $lessonModel->getSubjectLessons($subject['subject_id']);
    $total_lessons = count($lessons);
    $completed = $progressModel->getCompletedCount($_SESSION['user_id'], $subject['subject_id']);
    $progress = $total_lessons > 0 ? round(($completed / $total_lessons) * 100) : 0;
    
    $subject['total_lessons'] = $total_lessons;
    $subject['completed_lessons'] = $completed;
    $subject['progress'] = $progress;
    
    $subjects_with_progress[] = $subject;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner h1 {
            color: white;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 60%;
        }
        
        .welcome-stats {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }
        
        .stat-badge {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(5px);
            padding: 12px 24px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .stat-badge i {
            font-size: 20px;
        }
        
        .stat-badge span {
            font-size: 14px;
            font-weight: 500;
        }
        
        .stat-badge strong {
            font-size: 24px;
            font-weight: 700;
            margin-right: 5px;
        }
        
        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .section-header h2 i {
            color: #4f46e5;
            margin-right: 10px;
        }
        
        /* Subjects Grid */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .subject-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #eef2f6;
            position: relative;
            overflow: hidden;
        }
        
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
            border-color: #4f46e5;
        }
        
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .subject-code {
            background: #eef2ff;
            color: #4f46e5;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .teacher-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .subject-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .subject-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #64748b;
        }
        
        .subject-meta i {
            color: #4f46e5;
            width: 16px;
        }
        
        /* Progress Bar */
        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
            color: #475569;
        }
        
        .progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            transition: width 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .btn-continue {
            display: block;
            background: #4f46e5;
            color: white;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-continue:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
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
            margin-bottom: 25px;
        }
        
        .btn-enroll {
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        
        /* Available Subjects Section */
        .available-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-banner p {
                max-width: 100%;
            }
            
            .welcome-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .subjects-grid {
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
            <li><a href="subjects.php" class="active"><i class="fas fa-book"></i> My Subjects</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="/mywebsite10/views/student/quizzes.php"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> My Progress</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! 👋</h1>
            <p>Continue your learning journey. You're making great progress!</p>
            
            <div class="welcome-stats">
                <div class="stat-badge">
                    <i class="fas fa-book-open"></i>
                    <span><strong><?php echo count($my_subjects); ?></strong> Enrolled Subjects</span>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>
                        <strong>
                            <?php 
                            $total_completed = 0;
                            $total_lessons_all = 0;
                            foreach ($subjects_with_progress as $s) {
                                $total_completed += $s['completed_lessons'];
                                $total_lessons_all += $s['total_lessons'];
                            }
                            echo $total_completed;
                            ?>
                        </strong> / <?php echo $total_lessons_all; ?> Lessons Completed
                    </span>
                </div>
            </div>
        </div>

        <!-- My Subjects Section -->
        <div class="section-header">
            <h2><i class="fas fa-book-open"></i> My Subjects</h2>
        </div>

        <?php if (empty($subjects_with_progress)): ?>
            <div class="empty-state">
                <i class="fas fa-books"></i>
                <h3>No Subjects Yet</h3>
                <p>You're not enrolled in any subjects yet. Enroll now to start learning!</p>
                <a href="available-subjects.php" class="btn-enroll">
                    <i class="fas fa-plus"></i> Browse Available Subjects
                </a>
            </div>
        <?php else: ?>
            <div class="subjects-grid">
                <?php foreach ($subjects_with_progress as $subject): ?>
                    <div class="subject-card">
                        <div class="subject-header">
                            <span class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                            <span class="teacher-badge">
                                <i class="fas fa-chalkboard-teacher"></i> Teacher
                            </span>
                        </div>
                        
                        <h3 class="subject-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                        
                        <div class="subject-meta">
                            <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($subject['schedule'] ?? 'No schedule'); ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                        </div>
                        
                        <div class="progress-info">
                            <span>Progress</span>
                            <span><?php echo $subject['completed_lessons']; ?>/<?php echo $subject['total_lessons']; ?> lessons (<?php echo $subject['progress']; ?>%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $subject['progress']; ?>%;"></div>
                        </div>
                        
                        <a href="view-subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn-continue">
                            <i class="fas fa-play-circle"></i> Continue Learning
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Add animation to progress bars
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>