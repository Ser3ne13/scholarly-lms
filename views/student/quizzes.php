<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Quiz.php';
require_once '../../models/Subject.php';
require_once '../../models/Enrollment.php';

$student_id = $_SESSION['user_id'];

// Get subjects the student is enrolled in
$enrollmentModel = new Enrollment();
$subjectModel = new Subject();
$quizModel = new Quiz();

// For now, get all quizzes from enrolled subjects
// You'll need to modify this query based on your needs
$subjects = $enrollmentModel->getEnrolledSubjects($student_id);
$all_quizzes = [];

foreach ($subjects as $subject) {
    $quizzes = $quizModel->getSubjectQuizzes($subject['subject_id']);
    foreach ($quizzes as $quiz) {
        $quiz['subject_name'] = $subject['subject_name'];
        $quiz['subject_code'] = $subject['subject_code'];
        $all_quizzes[] = $quiz;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Quizzes - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .quizzes-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2.2rem;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .quiz-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 1px solid #eef2f6;
            position: relative;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
            border-color: #4f46e5;
        }

        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .quiz-header::before {
            content: '📝';
            position: absolute;
            bottom: -10px;
            right: -10px;
            font-size: 80px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }

        .quiz-subject {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
            backdrop-filter: blur(5px);
        }

        .quiz-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .quiz-body {
            padding: 25px;
        }

        .quiz-meta {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            font-size: 14px;
        }

        .meta-item i {
            width: 20px;
            color: #4f46e5;
        }

        .quiz-stats {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 1px solid #eef2f6;
            border-bottom: 1px solid #eef2f6;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stat-label {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        .btn-take-quiz {
            display: block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-take-quiz:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

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

        @media (max-width: 768px) {
            .quiz-grid {
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
            <li><a href="quizzes.php" class="active"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> My Progress</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="quizzes-container">
            <div class="page-header">
                <h1>My Quizzes</h1>
                <p>Test your knowledge with available quizzes from your subjects</p>
            </div>

            <?php if (empty($all_quizzes)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Quizzes Available</h3>
                    <p>There are no quizzes available for your enrolled subjects yet.</p>
                </div>
            <?php else: ?>
                <div class="quiz-grid">
                    <?php foreach ($all_quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <span class="quiz-subject">
                                    <i class="fas fa-book-open"></i> <?php echo htmlspecialchars($quiz['subject_code']); ?>
                                </span>
                                <h3 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h3>
                            </div>
                            
                            <div class="quiz-body">
                                <div class="quiz-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-align-left"></i>
                                        <span><?php echo htmlspecialchars($quiz['description'] ?? 'No description'); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-book"></i>
                                        <span><?php echo htmlspecialchars($quiz['subject_name']); ?></span>
                                    </div>
                                </div>

                                <div class="quiz-stats">
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $quiz['time_limit']; ?></div>
                                        <div class="stat-label">Minutes</div>
                                    </div>
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $quiz['passing_score']; ?>%</div>
                                        <div class="stat-label">Passing</div>
                                    </div>
                                    <div class="stat">
                                        <div class="stat-value"><?php echo $quiz['max_attempts']; ?></div>
                                        <div class="stat-label">Attempts</div>
                                    </div>
                                </div>

                                <a href="take-quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="btn-take-quiz">
                                    <i class="fas fa-play"></i> Start Quiz
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