<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';
require_once '../../models/Lesson.php';
require_once '../../models/Enrollment.php';

$subject_id = $_GET['id'] ?? 0;

$subjectModel = new Subject();
$lessonModel = new Lesson();
$enrollmentModel = new Enrollment();

$subject = $subjectModel->getSubject($subject_id);
$lessons = $lessonModel->getSubjectLessons($subject_id);
$students = $enrollmentModel->getEnrolledStudents($subject_id);

if (!$subject || $subject['teacher_id'] != $_SESSION['user_id']) {
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
        .subject-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px;
            border-radius: var(--border-radius-2xl);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .subject-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        .subject-header h1 {
            color: white;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .subject-meta {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 30px;
            backdrop-filter: blur(5px);
        }
        
        .tab-container {
            background: white;
            border-radius: var(--border-radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        
        .tab-headers {
            display: flex;
            border-bottom: 2px solid var(--dark-100);
            background: var(--dark-50);
        }
        
        .tab-btn {
            padding: 15px 30px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: var(--dark-500);
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .tab-btn.active {
            color: var(--primary-600);
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-500);
            border-radius: 3px 3px 0 0;
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .lesson-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 1px solid var(--dark-100);
            border-radius: var(--border-radius-lg);
            margin-bottom: 15px;
            transition: all 0.3s;
            background: white;
        }
        
        .lesson-item:hover {
            transform: translateX(5px);
            border-color: var(--primary-300);
            box-shadow: var(--shadow-md);
        }
        
        .lesson-number {
            width: 40px;
            height: 40px;
            background: var(--primary-100);
            color: var(--primary-700);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 20px;
        }
        
        .lesson-info {
            flex: 1;
        }
        
        .lesson-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .lesson-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: var(--dark-500);
        }
        
        .lesson-actions {
            display: flex;
            gap: 10px;
        }
        
        .student-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid var(--dark-100);
            border-radius: var(--border-radius-lg);
            margin-bottom: 10px;
        }
        
        .student-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 15px;
        }
        
        .student-info {
            flex: 1;
        }
        
        .student-name {
            font-weight: 600;
        }
        
        .student-email {
            font-size: 13px;
            color: var(--dark-500);
        }
        
        .student-status {
            padding: 4px 12px;
            background: #d4edda;
            color: #155724;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .btn-add {
            margin-bottom: 20px;
        }
        
        .empty-state-small {
            text-align: center;
            padding: 50px;
            color: var(--dark-400);
        }
        
        .empty-state-small i {
            font-size: 48px;
            margin-bottom: 15px;
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
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a></li>
        
        <li><a href="subjects.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' || basename($_SERVER['PHP_SELF']) == 'view-subject.php' || basename($_SERVER['PHP_SELF']) == 'create-subject.php' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> My Subjects
        </a></li>
        
        <li><a href="students.php">
            <i class="fas fa-users"></i> Students
        </a></li>
        
        <li><a href="schedule.php">
            <i class="fas fa-calendar"></i> Schedule
        </a></li>
        
        <li><a href="quizzes.php">
            <i class="fas fa-question-circle"></i> Quizzes
        </a></li>
        
        <li><a href="assignments.php">
            <i class="fas fa-tasks"></i> Assignments
        </a></li>
        
        <li><a href="analytics.php">
            <i class="fas fa-chart-line"></i> Analytics
        </a></li>
        
        <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a></li>
    </ul>
</aside>

    <main class="main-content">
        <a href="subjects.php" class="btn btn-outline" style="margin-bottom: 20px;">
            <i class="fas fa-arrow-left"></i> Back to Subjects
        </a>
        
        <!-- Subject Header -->
        <div class="subject-header">
            <h1><?php echo htmlspecialchars($subject['subject_name']); ?></h1>
            <p><?php echo htmlspecialchars($subject['description'] ?? 'No description provided.'); ?></p>
            
            <div class="subject-meta">
                <span class="meta-item"><i class="fas fa-code"></i> <?php echo htmlspecialchars($subject['subject_code']); ?></span>
                <span class="meta-item"><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($subject['schedule'] ?? 'No schedule'); ?></span>
                <span class="meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                <span class="meta-item"><i class="fas fa-users"></i> <?php echo count($students); ?> Students</span>
                <span class="meta-item"><i class="fas fa-video"></i> <?php echo count($lessons); ?> Lessons</span>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tab-container">
            <div class="tab-headers">
                <button class="tab-btn active" onclick="showTab('lessons')">📚 Lessons</button>
                <button class="tab-btn" onclick="showTab('students')">👥 Students (<?php echo count($students); ?>)</button>
                <button class="tab-btn" onclick="showTab('settings')">⚙️ Settings</button>
                <button class="tab-btn" onclick="showTab('quizzes')">📝 Quizzes</button>
                <button class="tab-btn" onclick="showTab('assignments')">
                    <i class="fas fa-tasks"></i> Assignments
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Lessons Tab -->
                <div id="lessons" class="tab-pane active">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Lessons</h3>
                        <a href="create-lesson.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-primary btn-add">
                            <i class="fas fa-plus"></i> Add New Lesson
                        </a>
                    </div>
                    
                    <?php if (empty($lessons)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-video"></i>
                            <h4>No Lessons Yet</h4>
                            <p>Click "Add New Lesson" to create your first lesson.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <div class="lesson-item">
                                <div class="lesson-number"><?php echo $index + 1; ?></div>
                                <div class="lesson-info">
                                    <div class="lesson-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
                                    <div class="lesson-meta">
                                        <span><i class="far fa-clock"></i> <?php echo date('M d, Y', strtotime($lesson['created_at'])); ?></span>
                                        <?php if ($lesson['file_path']): ?>
                                            <span><i class="fas fa-paperclip"></i> Has attachment</span>
                                        <?php endif; ?>
                                        <?php if ($lesson['video_url']): ?>
                                            <span><i class="fas fa-video"></i> Has video</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="lesson-actions">
                                    <a href="edit-lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-outline" style="padding: 8px 16px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="view-lesson.php?id=<?php echo $lesson['lesson_id']; ?>" class="btn btn-primary" style="padding: 8px 16px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Students Tab -->
                <div id="students" class="tab-pane">
                    <h3 style="margin-bottom: 20px;">Enrolled Students</h3>
                    
                    <?php if (empty($students)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-users"></i>
                            <h4>No Students Enrolled</h4>
                            <p>Share the subject code with students to enroll.</p>
                            <div style="background: var(--dark-100); padding: 15px; border-radius: 10px; margin-top: 15px;">
                                <strong>Subject Code:</strong> <?php echo htmlspecialchars($subject['subject_code']); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($students as $student): ?>
                            <div class="student-item">
                                <div class="student-avatar">
                                    <?php echo substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1); ?>
                                </div>
                                <div class="student-info">
                                    <div class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                    <div class="student-email"><?php echo htmlspecialchars($student['email']); ?></div>
                                </div>
                                <div class="student-status">Active</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Settings Tab -->
                <div id="settings" class="tab-pane">
                    <h3 style="margin-bottom: 20px;">Subject Settings</h3>
                    
                    <form action="/mywebsite10/controllers/SubjectController.php?action=update" method="POST">
                        <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                        
                        <div class="form-group">
                            <label>Subject Code</label>
                            <input type="text" name="subject_code" class="form-control" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Subject Name</label>
                            <input type="text" name="subject_name" class="form-control" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($subject['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Schedule</label>
                                <input type="text" name="schedule" class="form-control" value="<?php echo htmlspecialchars($subject['schedule'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Room</label>
                                <input type="text" name="room" class="form-control" value="<?php echo htmlspecialchars($subject['room'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Academic Year</label>
                                <select name="academic_year" class="form-control">
                                    <option value="2024-2025" <?php echo ($subject['academic_year'] ?? '') == '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                                    <option value="2025-2026" <?php echo ($subject['academic_year'] ?? '') == '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" class="form-control">
                                    <option value="1st Semester" <?php echo ($subject['semester'] ?? '') == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                                    <option value="2nd Semester" <?php echo ($subject['semester'] ?? '') == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                                    <option value="Summer" <?php echo ($subject['semester'] ?? '') == 'Summer' ? 'selected' : ''; ?>>Summer</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Subject</button>
                    </form>
                    
                    <hr style="margin: 40px 0;">
                    
                    <div style="background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
                        <h4 style="color: #856404;">Danger Zone</h4>
                        <p style="color: #856404;">Once you delete a subject, all lessons and student enrollments will be permanently removed.</p>
                        <a href="/mywebsite10/controllers/SubjectController.php?action=delete&id=<?php echo $subject_id; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you absolutely sure? This cannot be undone.')">
                            <i class="fas fa-trash"></i> Delete Subject
                        </a>
                    </div>
                </div>
                <!-- Quizzes Tab -->
                <div id="quizzes" class="tab-pane">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Quizzes</h3>
                        <a href="create-quiz.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Quiz
                        </a>
                    </div>
                    
                    <?php
                    require_once '../../models/Quiz.php';
                    $quizModel = new Quiz();
                    $quizzes = $quizModel->getSubjectQuizzes($subject_id);
                    ?>
                    
                    <?php if (empty($quizzes)): ?>
                        <div class="empty-state-small">
                            <i class="fas fa-question-circle"></i>
                            <h4>No Quizzes Yet</h4>
                            <p>Create your first quiz to test student knowledge.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($quizzes as $quiz): ?>
                            <div class="quiz-item">
                                <div>
                                    <h4><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                    <small>Time: <?php echo $quiz['time_limit']; ?> mins | Passing: <?php echo $quiz['passing_score']; ?>% | Max Attempts: <?php echo $quiz['max_attempts']; ?></small>
                                </div>
                                <div class="quiz-actions">
                                    <a href="edit-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn-edit">Edit</a>
                                    <a href="view-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn-view">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Assignments Tab -->
<div id="assignments" class="tab-pane">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Assignments</h3>
        <a href="create-assignment.php?subject_id=<?php echo $subject_id; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Assignment
        </a>
    </div>
    
    <?php
    require_once '../../models/Assignment.php';
    $assignmentModel = new Assignment();
    $assignments = $assignmentModel->getSubjectAssignments($subject_id);
    ?>
    
    <?php if (empty($assignments)): ?>
        <div class="empty-state-small">
            <i class="fas fa-tasks"></i>
            <h4>No Assignments Yet</h4>
            <p>Create your first assignment for students.</p>
        </div>
    <?php else: ?>
        <?php foreach ($assignments as $assignment): 
            $is_past_due = strtotime($assignment['due_date']) < time();
        ?>
            <div class="assignment-item" style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px; border: 1px solid #eef2f6;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h4 style="margin-bottom: 8px;"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                        <p style="color: #64748b; margin-bottom: 10px;"><?php echo htmlspecialchars($assignment['description']); ?></p>
                        <div style="display: flex; gap: 20px; font-size: 13px;">
                            <span><i class="fas fa-calendar-alt" style="color: #4f46e5;"></i> Due: <?php echo date('M d, Y h:i A', strtotime($assignment['due_date'])); ?></span>
                            <span><i class="fas fa-star" style="color: #f59e0b;"></i> <?php echo $assignment['total_points']; ?> points</span>
                            <span class="<?php echo $is_past_due ? 'text-danger' : 'text-success'; ?>">
                                <i class="fas <?php echo $is_past_due ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                                <?php echo $is_past_due ? 'Past Due' : 'Active'; ?>
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="view-assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn-view" style="padding: 8px 16px;">View</a>
                        <a href="edit-assignment.php?id=<?php echo $assignment['assignment_id']; ?>" class="btn-edit" style="padding: 8px 16px;">Edit</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
            </div>
        </div>
    </main>

    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }

        // Auto-dismiss flash messages after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success, .alert-error, .alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 3000); // 3000ms = 3 seconds
});
    </script>

    
</body>
</html>