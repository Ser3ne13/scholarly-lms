<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Quiz.php';
require_once '../../models/QuizAttempt.php';

$attempt_id = $_GET['attempt_id'] ?? 0;

$attemptModel = new QuizAttempt();
$quizModel = new Quiz();

$attempt = $attemptModel->getAttempt($attempt_id);
$quiz = $quizModel->getQuiz($attempt['quiz_id']);

$percentage = ($attempt['score'] / $attempt['total_points']) * 100;
$passed = $percentage >= $quiz['passing_score'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .results-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .result-card {
            background: white;
            border-radius: 40px;
            padding: 50px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }

        .result-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
        }

        .result-icon.passed {
            background: #d1fae5;
            color: #10b981;
        }

        .result-icon.failed {
            background: #fee2e2;
            color: #ef4444;
        }

        .score-circle {
            width: 200px;
            height: 200px;
            margin: 30px auto;
            position: relative;
        }

        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            font-weight: 700;
        }

        .score-label {
            color: #64748b;
            margin-bottom: 5px;
        }

        .score-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
        }

        .btn-review {
            background: #4f46e5;
            color: white;
            padding: 15px 40px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-block;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="results-container">
        <div class="result-card">
            <div class="result-icon <?php echo $passed ? 'passed' : 'failed'; ?>">
                <i class="fas <?php echo $passed ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            </div>
            
            <h1 style="margin-bottom: 10px;"><?php echo $passed ? 'Congratulations!' : 'Better Luck Next Time'; ?></h1>
            <p style="color: #64748b; margin-bottom: 30px;"><?php echo $quiz['title']; ?></p>
            
            <div class="score-circle">
                <svg width="200" height="200">
                    <circle cx="100" cy="100" r="90" fill="none" stroke="#e2e8f0" stroke-width="20"/>
                    <circle cx="100" cy="100" r="90" fill="none" 
                            stroke="<?php echo $passed ? '#10b981' : '#ef4444'; ?>" 
                            stroke-width="20" 
                            stroke-dasharray="<?php echo ($percentage / 100) * 565; ?> 565"
                            stroke-dashoffset="0"
                            transform="rotate(-90 100 100)"/>
                </svg>
                <div class="score-text"><?php echo round($percentage); ?>%</div>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 40px; margin: 30px 0;">
                <div>
                    <div class="score-label">Your Score</div>
                    <div class="score-value"><?php echo $attempt['score']; ?>/<?php echo $attempt['total_points']; ?></div>
                </div>
                <div>
                    <div class="score-label">Passing Score</div>
                    <div class="score-value"><?php echo $quiz['passing_score']; ?>%</div>
                </div>
            </div>
            
            <a href="quizzes.php" class="btn-review">
                <i class="fas fa-arrow-left"></i> Back to Quizzes
            </a>
        </div>
    </div>
</body>
</html>