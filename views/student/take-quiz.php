<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Quiz.php';
require_once '../../models/Question.php';
require_once '../../models/QuizAttempt.php';

$quiz_id = $_GET['id'] ?? 0;

if (!$quiz_id) {
    header('Location: quizzes.php');
    exit();
}

$quizModel = new Quiz();
$questionModel = new Question();
$attemptModel = new QuizAttempt();

$quiz = $quizModel->getQuiz($quiz_id);

if (!$quiz) {
    header('Location: quizzes.php');
    exit();
}

$questions = $questionModel->getQuizQuestions($quiz_id);

// Start new attempt
$attempt_id = $attemptModel->startAttempt($quiz_id, $_SESSION['user_id']);

if (!$attempt_id) {
    $_SESSION['error'] = 'You have reached the maximum attempts for this quiz.';
    header('Location: quizzes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        .quiz-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
        }

        .quiz-header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .quiz-timer {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .timer-warning {
            color: #fbbf24;
        }

        .progress-bar {
            height: 8px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: white;
            width: 0%;
            transition: width 0.3s;
        }

        .question-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }

        .question-number {
            background: #eef2ff;
            color: #4f46e5;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
        }

        .question-points {
            color: #64748b;
            font-size: 14px;
        }

        .question-text {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 25px;
        }

        .options-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-item {
            padding: 15px 20px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .option-item:hover {
            border-color: #4f46e5;
            background: #eef2ff;
        }

        .option-item.selected {
            border-color: #4f46e5;
            background: #eef2ff;
        }

        .option-radio {
            width: 20px;
            height: 20px;
            accent-color: #4f46e5;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .nav-btn {
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .nav-btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }

        .nav-btn:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn {
            background: #10b981;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 30px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
        }

        .modal-content i {
            font-size: 60px;
            color: #f59e0b;
            margin-bottom: 20px;
        }

        .modal-content button {
            background: #4f46e5;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 40px;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <form id="quizForm" action="/mywebsite10/controllers/QuizAttemptController.php?action=submit" method="POST">
            <input type="hidden" name="attempt_id" value="<?php echo $attempt_id; ?>">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            
            <!-- Quiz Header with Timer -->
            <div class="quiz-header">
                <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <div class="quiz-timer" id="timer"><?php echo $quiz['time_limit']; ?>:00</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressBar"></div>
                </div>
            </div>

            <!-- Questions -->
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?php echo $index + 1; ?>" style="<?php echo $index > 0 ? 'display: none;' : ''; ?>">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></span>
                        <span class="question-points"><?php echo $question['points']; ?> points</span>
                    </div>
                    
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['question_text']); ?>
                    </div>
                    
                    <div class="options-list">
                        <?php foreach ($question['options'] as $option): ?>
                            <label class="option-item">
                                <input type="radio" 
                                       name="question_<?php echo $question['question_id']; ?>" 
                                       value="<?php echo $option['option_id']; ?>" 
                                       class="option-radio"
                                       onchange="updateProgress()">
                                <span><?php echo htmlspecialchars($option['option_text']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="navigation-buttons">
                        <?php if ($index > 0): ?>
                            <button type="button" class="nav-btn" onclick="showQuestion(<?php echo $index; ?>)">
                                <i class="fas fa-arrow-left"></i> Previous
                            </button>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        
                        <?php if ($index < count($questions) - 1): ?>
                            <button type="button" class="nav-btn" onclick="showQuestion(<?php echo $index + 2; ?>)">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="submit-btn" onclick="submitQuiz()">
                                Submit Quiz <i class="fas fa-check"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </form>
    </div>

    <!-- Time's Up Modal -->
    <div id="timeUpModal" class="modal">
        <div class="modal-content">
            <i class="fas fa-hourglass-end"></i>
            <h2 style="margin-bottom: 15px;">Time's Up!</h2>
            <p style="margin-bottom: 25px;">Your quiz will be submitted automatically.</p>
            <button onclick="forceSubmit()">OK</button>
        </div>
    </div>

    <script>
        let currentQuestion = 1;
        const totalQuestions = <?php echo count($questions); ?>;
        let timeLeft = <?php echo $quiz['time_limit'] * 60; ?>;
        const timerElement = document.getElementById('timer');
        const progressBar = document.getElementById('progressBar');
        
        // Timer
        const timer = setInterval(function() {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            // Progress bar
            const totalTime = <?php echo $quiz['time_limit'] * 60; ?>;
            const percentage = ((totalTime - timeLeft) / totalTime) * 100;
            progressBar.style.width = percentage + '%';
            
            // Warning at 5 minutes
            if (timeLeft === 300) {
                timerElement.classList.add('timer-warning');
            }
            
            // Time's up
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.getElementById('timeUpModal').style.display = 'flex';
            }
        }, 1000);
        
        function showQuestion(questionNumber) {
            for (let i = 1; i <= totalQuestions; i++) {
                document.getElementById(`question-${i}`).style.display = 'none';
            }
            document.getElementById(`question-${questionNumber}`).style.display = 'block';
            currentQuestion = questionNumber;
        }
        
        function updateProgress() {
            let answered = 0;
            for (let i = 1; i <= totalQuestions; i++) {
                const question = document.getElementById(`question-${i}`);
                if (question) {
                    const radios = question.querySelectorAll('input[type="radio"]');
                    for (let radio of radios) {
                        if (radio.checked) {
                            answered++;
                            break;
                        }
                    }
                }
            }
            const answeredPercentage = (answered / totalQuestions) * 100;
            progressBar.style.width = answeredPercentage + '%';
        }
        
        function submitQuiz() {
            if (confirm('Are you sure you want to submit your quiz?')) {
                document.getElementById('quizForm').submit();
            }
        }
        
        function forceSubmit() {
            document.getElementById('quizForm').submit();
        }
    </script>
</body>
</html>