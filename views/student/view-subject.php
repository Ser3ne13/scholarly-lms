<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';
require_once '../../models/Lesson.php';
require_once '../../models/LessonProgress.php';

$subject_id = $_GET['id'] ?? 0;

$subjectModel = new Subject();
$lessonModel = new Lesson();
$progressModel = new LessonProgress();

$subject = $subjectModel->getSubject($subject_id);
$lessons = $lessonModel->getSubjectLessons($subject_id);
$progress = $progressModel->getStudentProgress($_SESSION['user_id'], $subject_id);

// Calculate overall progress
$total_lessons = count($lessons);
$completed_lessons = 0;
foreach ($progress as $p) {
    if ($p['completed']) $completed_lessons++;
}
$progress_percentage = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

if (!$subject) {
    header('Location: subjects.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject['subject_name']); ?> - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        /* Subject Header */
        .subject-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
        }
        
        .subject-header h1 {
            color: white;
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        
        .subject-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .meta-item {
            background: rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
        }
        
        /* Progress Card */
        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .progress-percentage {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
        }
        
        .progress-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            transition: width 0.5s;
        }
        
        .progress-stats {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #64748b;
        }
        
        .progress-stats i {
            color: #4f46e5;
            margin-right: 5px;
        }
        
        /* Lessons List */
        .lessons-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .section-title i {
            color: #4f46e5;
        }
        
        .lessons-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .lesson-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #eef2f6;
            transition: all 0.3s;
        }
        
        .lesson-item:hover {
            border-color: #4f46e5;
            background: white;
            transform: translateX(5px);
        }
        
        .lesson-item.completed {
            background: #f0fdf4;
            border-color: #86efac;
        }
        
        .lesson-number {
            width: 45px;
            height: 45px;
            background: #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #475569;
            margin-right: 20px;
        }
        
        .lesson-item.completed .lesson-number {
            background: #4f46e5;
            color: white;
        }
        
        .lesson-info {
            flex: 1;
        }
        
        .lesson-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .lesson-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #64748b;
        }
        
        .lesson-meta i {
            margin-right: 3px;
        }
        
        .lesson-status {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-pending {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-in-progress {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn-view {
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .subject-header {
                padding: 25px;
            }
            
            .subject-header h1 {
                font-size: 1.8rem;
            }
            
            .lesson-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .lesson-number {
                margin-right: 0;
            }
            
            .btn-view {
                width: 100%;
                justify-content: center;
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
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> My Progress</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <a href="subjects.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to My Subjects
        </a>

        <!-- Subject Header -->
        <div class="subject-header">
            <h1><?php echo htmlspecialchars($subject['subject_name']); ?></h1>
            <p><?php echo htmlspecialchars($subject['description'] ?? 'No description provided.'); ?></p>
            
            <div class="subject-meta">
                <span class="meta-item"><i class="fas fa-code"></i> <?php echo htmlspecialchars($subject['subject_code']); ?></span>
                <span class="meta-item"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($subject['schedule'] ?? 'No schedule'); ?></span>
                <span class="meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                <span class="meta-item"><i class="fas fa-user"></i> Teacher</span>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="progress-card">
            <div class="progress-header">
                <h3><i class="fas fa-chart-line" style="color: #4f46e5; margin-right: 8px;"></i> Your Progress</h3>
                <span class="progress-percentage"><?php echo $progress_percentage; ?>%</span>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%;"></div>
            </div>
            
            <div class="progress-stats">
                <span><i class="fas fa-check-circle"></i> <?php echo $completed_lessons; ?> Completed</span>
                <span><i class="fas fa-hourglass-half"></i> <?php echo $total_lessons - $completed_lessons; ?> Remaining</span>
                <span><i class="fas fa-list"></i> <?php echo $total_lessons; ?> Total Lessons</span>
            </div>
        </div>

        <!-- Lessons List -->
        <div class="lessons-section">
            <div class="section-title">
                <i class="fas fa-list"></i>
                <span>Lesson Modules</span>
            </div>

            <?php if (empty($lessons)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Lessons Yet</h3>
                    <p>The teacher hasn't added any lessons to this subject yet.</p>
                </div>
            <?php else: ?>
                <div class="lessons-list">
                    <?php foreach ($lessons as $index => $lesson): 
                        $is_completed = false;
                        foreach ($progress as $p) {
                            if ($p['lesson_id'] == $lesson['lesson_id'] && $p['completed']) {
                                $is_completed = true;
                                break;
                            }
                        }
                    ?>
                        <div class="lesson-item <?php echo $is_completed ? 'completed' : ''; ?>">
                            <div class="lesson-number"><?php echo $index + 1; ?></div>
                            
                            <div class="lesson-info">
                                <div class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
                                <div class="lesson-meta">
                                    <?php if ($lesson['file_path']): ?>
                                        <span><i class="fas fa-paperclip"></i> Has attachment</span>
                                    <?php endif; ?>
                                    <?php if ($lesson['video_url']): ?>
                                        <span><i class="fas fa-video"></i> Has video</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="lesson-status <?php echo $is_completed ? 'status-completed' : 'status-pending'; ?>">
                                    <?php if ($is_completed): ?>
                                        <i class="fas fa-check-circle"></i> Completed
                                    <?php else: ?>
                                        <i class="fas fa-clock"></i> Not Started
                                    <?php endif; ?>
                                </div>
                                
                                <a href="view-lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn-view">
                                    <?php if ($is_completed): ?>
                                        <i class="fas fa-redo-alt"></i> Review
                                    <?php else: ?>
                                        <i class="fas fa-play"></i> Start
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>