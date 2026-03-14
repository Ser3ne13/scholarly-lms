<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Quiz.php';
require_once '../../models/Question.php';
require_once '../../models/Subject.php';

$quiz_id = $_GET['id'] ?? 0;

$quizModel = new Quiz();
$questionModel = new Question();
$subjectModel = new Subject();

$quiz = $quizModel->getQuiz($quiz_id);

if (!$quiz) {
    header('Location: subjects.php');
    exit();
}

$subject = $subjectModel->getSubject($quiz['subject_id']);
$questions = $questionModel->getQuizQuestions($quiz_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - <?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
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

        .edit-quiz-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Quiz Header */
        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .quiz-header::before {
            content: '📝';
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }

        .quiz-header h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .quiz-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .meta-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .meta-badge i {
            font-size: 16px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 40px;
            margin-bottom: 20px;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(-5px);
        }

        /* Stats Cards */
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
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-icon.primary { background: #eef2ff; color: #4f46e5; }
        .stat-icon.success { background: #dcfce7; color: #10b981; }
        .stat-icon.warning { background: #fff3cd; color: #f59e0b; }
        .stat-icon.info { background: #cffafe; color: #06b6d4; }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #64748b;
            font-size: 14px;
        }

        /* Questions Section */
        .questions-section {
            background: white;
            border-radius: 30px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f6;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h2 i {
            color: #4f46e5;
        }

        /* Question Cards */
        .question-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid #eef2f6;
            transition: all 0.3s;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .question-card:hover {
            border-color: #4f46e5;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .question-badges {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .question-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .question-type-badge {
            background: #e2e8f0;
            color: #475569;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .points-badge {
            background: #fef3c7;
            color: #92400e;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .question-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 16px;
        }

        .edit-btn {
            background: #eef2ff;
            color: #4f46e5;
        }

        .edit-btn:hover {
            background: #4f46e5;
            color: white;
            transform: scale(1.1);
        }

        .delete-btn {
            background: #fee2e2;
            color: #ef4444;
        }

        .delete-btn:hover {
            background: #ef4444;
            color: white;
            transform: scale(1.1);
        }

        .question-text {
            font-size: 16px;
            font-weight: 500;
            color: #1e293b;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        /* Options Grid */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .option-item {
            padding: 15px;
            background: white;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .option-item.correct {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .option-text {
            font-size: 14px;
            color: #334155;
        }

        .correct-badge {
            background: #10b981;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Add Question Section */
        .add-question-section {
            background: white;
            border-radius: 30px;
            padding: 30px;
            border: 2px dashed #cbd5e1;
            transition: all 0.3s;
        }

        .add-question-section:hover {
            border-color: #4f46e5;
            background: #f8fafc;
        }

        .type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .type-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
        }

        .type-btn.active {
            border-color: #4f46e5;
            background: #eef2ff;
            color: #4f46e5;
        }

        .type-btn i {
            font-size: 18px;
        }

        .options-input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .option-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .option-input-group input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #4f46e5;
        }

        .tf-options {
            display: flex;
            gap: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
            margin-top: 10px;
        }

        .tf-option {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 40px;
            transition: all 0.2s;
        }

        .tf-option:hover {
            background: #e2e8f0;
        }

        .tf-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #4f46e5;
        }

        .btn-add {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        /* Empty State */
        .empty-questions {
            text-align: center;
            padding: 60px;
            background: #f8fafc;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .empty-questions i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-questions h3 {
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-questions p {
            color: #64748b;
        }

        /* Modal */
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
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            width: 600px;
            border-radius: 30px;
            padding: 40px;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eef2f6;
        }

        .modal-header h3 {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .close-btn:hover {
            color: #ef4444;
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .options-grid {
                grid-template-columns: 1fr;
            }

            .options-input-grid {
                grid-template-columns: 1fr;
            }

            .type-selector {
                flex-direction: column;
            }

            .modal-content {
                width: 90%;
                padding: 25px;
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
            <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="edit-quiz-container">
            <!-- Back Button -->
            <a href="view-subject.php?id=<?php echo $quiz['subject_id']; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($subject['subject_name']); ?>
            </a>

            <!-- Quiz Header -->
            <div class="quiz-header">
                <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
                <p style="opacity: 0.9; margin-bottom: 20px;"><?php echo htmlspecialchars($quiz['description'] ?? 'No description'); ?></p>
                
                <div class="quiz-meta">
                    <span class="meta-badge"><i class="fas fa-clock"></i> <?php echo $quiz['time_limit']; ?> minutes</span>
                    <span class="meta-badge"><i class="fas fa-percent"></i> Passing: <?php echo $quiz['passing_score']; ?>%</span>
                    <span class="meta-badge"><i class="fas fa-redo"></i> Max Attempts: <?php echo $quiz['max_attempts']; ?></span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value"><?php echo count($questions); ?></div>
                    <div class="stat-label">Total Questions</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <?php 
                    $totalPoints = 0;
                    foreach ($questions as $q) {
                        $totalPoints += $q['points'];
                    }
                    ?>
                    <div class="stat-value"><?php echo $totalPoints; ?></div>
                    <div class="stat-label">Total Points</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $quiz['time_limit']; ?></div>
                    <div class="stat-label">Minutes</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value"><?php echo $quiz['passing_score']; ?>%</div>
                    <div class="stat-label">Passing Score</div>
                </div>
            </div>

            <!-- Questions Section -->
            <div class="questions-section">
                <div class="section-header">
                    <h2><i class="fas fa-question-circle"></i> Quiz Questions</h2>
                </div>

                <?php if (empty($questions)): ?>
                    <div class="empty-questions">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Questions Yet</h3>
                        <p>Start building your quiz by adding questions below</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="question-card" id="question-<?php echo $q['question_id']; ?>">
                            <div class="question-header">
                                <div class="question-badges">
                                    <span class="question-number"><?php echo $index + 1; ?></span>
                                    <span class="question-type-badge">
                                        <i class="fas <?php echo $q['question_type'] === 'multiple_choice' ? 'fa-list-ul' : 'fa-check-circle'; ?>"></i>
                                        <?php echo $q['question_type'] === 'multiple_choice' ? 'Multiple Choice' : 'True/False'; ?>
                                    </span>
                                    <span class="points-badge">
                                        <i class="fas fa-star"></i> <?php echo $q['points']; ?> points
                                    </span>
                                </div>
                                <div class="question-actions">
                                    <button class="action-btn edit-btn" onclick="editQuestion(<?php echo $q['question_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteQuestion(<?php echo $q['question_id']; ?>, <?php echo $quiz_id; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="question-text">
                                <?php echo htmlspecialchars($q['question_text']); ?>
                            </div>
                            
                            <div class="options-grid">
                                <?php foreach ($q['options'] as $opt): ?>
                                    <div class="option-item <?php echo $opt['is_correct'] ? 'correct' : ''; ?>">
                                        <span class="option-text"><?php echo htmlspecialchars($opt['option_text']); ?></span>
                                        <?php if ($opt['is_correct']): ?>
                                            <span class="correct-badge"><i class="fas fa-check"></i> Correct</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Question Form -->
            <div class="add-question-section">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-plus-circle" style="color: #4f46e5;"></i> Add New Question</h3>
                
                <form action="/mywebsite10/controllers/QuizController.php?action=add_question" method="POST" id="addQuestionForm">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="question_type" id="questionType" value="multiple_choice">
                    
                    <div class="type-selector">
                        <button type="button" class="type-btn active" onclick="selectType('multiple_choice')" id="mcBtn">
                            <i class="fas fa-list-ul"></i> Multiple Choice
                        </button>
                        <button type="button" class="type-btn" onclick="selectType('true_false')" id="tfBtn">
                            <i class="fas fa-check-circle"></i> True/False
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Question Text</label>
                        <textarea name="question_text" class="form-control" rows="3" placeholder="Enter your question here..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Points</label>
                        <input type="number" name="points" class="form-control" value="1" min="1" max="10" style="width: 120px;">
                    </div>
                    
                    <!-- Multiple Choice Fields -->
                    <div id="multipleChoiceFields">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Options (select the correct one)</label>
                        <div class="options-input-grid">
                            <div class="option-input-group">
                                <input type="radio" name="correct_option" value="0" checked>
                                <input type="text" name="options[]" class="form-control" placeholder="Option A" id="optA" required>
                            </div>
                            <div class="option-input-group">
                                <input type="radio" name="correct_option" value="1">
                                <input type="text" name="options[]" class="form-control" placeholder="Option B" id="optB" required>
                            </div>
                            <div class="option-input-group">
                                <input type="radio" name="correct_option" value="2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option C" id="optC">
                            </div>
                            <div class="option-input-group">
                                <input type="radio" name="correct_option" value="3">
                                <input type="text" name="options[]" class="form-control" placeholder="Option D" id="optD">
                            </div>
                        </div>
                    </div>
                    
                    <!-- True/False Fields -->
                    <div id="trueFalseFields" style="display: none;">
                        <label style="font-weight: 600; margin-bottom: 8px; display: block;">Select the correct answer</label>
                        <div class="tf-options">
                            <label class="tf-option">
                                <input type="radio" name="tf_correct" value="true" checked> 
                                <span>True</span>
                            </label>
                            <label class="tf-option">
                                <input type="radio" name="tf_correct" value="false"> 
                                <span>False</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-add">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: #4f46e5;"></i> Edit Question</h3>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            
            <form id="editForm" action="/mywebsite10/controllers/QuizController.php?action=update_question" method="POST">
                <input type="hidden" name="question_id" id="edit_question_id">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                <input type="hidden" name="question_type" id="edit_question_type">
                
                <div class="form-group">
                    <label style="font-weight: 600;">Question Text</label>
                    <textarea name="question_text" id="edit_question_text" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label style="font-weight: 600;">Points</label>
                    <input type="number" name="points" id="edit_points" class="form-control" value="1" min="1" max="10">
                </div>
                
                <!-- Multiple Choice Edit Fields -->
                <div id="edit_multiple_choice_fields">
                    <label style="font-weight: 600; margin-bottom: 10px; display: block;">Options</label>
                    <div id="edit_options_container"></div>
                </div>
                
                <!-- True/False Edit Fields -->
                <div id="edit_true_false_fields" style="display: none;">
                    <label style="font-weight: 600;">Correct Answer</label>
                    <div class="tf-options">
                        <label class="tf-option">
                            <input type="radio" name="edit_tf_correct" value="true" id="edit_tf_true"> 
                            <span>True</span>
                        </label>
                        <label class="tf-option">
                            <input type="radio" name="edit_tf_correct" value="false" id="edit_tf_false"> 
                            <span>False</span>
                        </label>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-add" style="flex: 1;">Update Question</button>
                    <button type="button" onclick="closeEditModal()" class="btn-add" style="flex: 0.5; background: #64748b;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Question type selector
        function selectType(type) {
            document.getElementById('questionType').value = type;
            
            document.getElementById('mcBtn').classList.remove('active');
            document.getElementById('tfBtn').classList.remove('active');
            
            if (type === 'multiple_choice') {
                document.getElementById('mcBtn').classList.add('active');
                document.getElementById('multipleChoiceFields').style.display = 'block';
                document.getElementById('trueFalseFields').style.display = 'none';
                
                document.querySelectorAll('#multipleChoiceFields input[name="options[]"]').forEach(input => {
                    input.required = true;
                });
            } else {
                document.getElementById('tfBtn').classList.add('active');
                document.getElementById('multipleChoiceFields').style.display = 'none';
                document.getElementById('trueFalseFields').style.display = 'block';
                
                document.querySelectorAll('#multipleChoiceFields input[name="options[]"]').forEach(input => {
                    input.required = false;
                });
            }
        }

        // Edit question
        function editQuestion(questionId) {
            fetch(`/mywebsite10/controllers/QuizController.php?action=get_question&id=${questionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_question_id').value = data.question_id;
                    document.getElementById('edit_question_text').value = data.question_text;
                    document.getElementById('edit_points').value = data.points;
                    document.getElementById('edit_question_type').value = data.question_type;
                    
                    if (data.question_type === 'multiple_choice') {
                        document.getElementById('edit_multiple_choice_fields').style.display = 'block';
                        document.getElementById('edit_true_false_fields').style.display = 'none';
                        
                        let html = '';
                        data.options.forEach((opt, index) => {
                            html += `
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; background: #f8fafc; padding: 10px; border-radius: 12px;">
                                    <input type="radio" name="edit_correct_option" value="${index}" ${opt.is_correct ? 'checked' : ''}>
                                    <input type="text" name="edit_options[]" class="form-control" value="${opt.option_text.replace(/"/g, '&quot;')}" required>
                                </div>
                            `;
                        });
                        document.getElementById('edit_options_container').innerHTML = html;
                    } else {
                        document.getElementById('edit_multiple_choice_fields').style.display = 'none';
                        document.getElementById('edit_true_false_fields').style.display = 'block';
                        
                        const correctValue = data.options.find(o => o.is_correct)?.option_text.toLowerCase() === 'true' ? 'true' : 'false';
                        document.getElementById('edit_tf_true').checked = (correctValue === 'true');
                        document.getElementById('edit_tf_false').checked = (correctValue === 'false');
                    }
                    
                    document.getElementById('editModal').style.display = 'flex';
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteQuestion(questionId, quizId) {
            if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                window.location.href = `/mywebsite10/controllers/QuizController.php?action=delete_question&id=${questionId}&quiz_id=${quizId}`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Form validation
        document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
            const type = document.getElementById('questionType').value;
            
            if (type === 'multiple_choice') {
                const optA = document.getElementById('optA').value.trim();
                const optB = document.getElementById('optB').value.trim();
                
                if (optA === '' || optB === '') {
                    e.preventDefault();
                    alert('Please fill in at least Options A and B');
                }
            }
        });

        // Initialize with multiple choice selected
        window.onload = function() {
            selectType('multiple_choice');
        };
    </script>
</body>
</html>