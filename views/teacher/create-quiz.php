<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';

$subject_id = $_GET['subject_id'] ?? 0;
$subjectModel = new Subject();
$subject = $subjectModel->getSubject($subject_id);

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
    <title>Create Quiz - <?php echo htmlspecialchars($subject['subject_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .create-quiz-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #eef2f6;
        }
        
        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .subject-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-back:hover {
            background: #e2e8f0;
            transform: translateX(-3px);
        }
        
        .form-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eef2f6;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .section-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .section-title h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #334155;
        }
        
        .form-group label i {
            color: #4f46e5;
            margin-right: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.2s;
            background: #f8fafc;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eef2f6;
        }
        
        .btn {
            padding: 14px 32px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
        
        .help-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-card {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
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
            <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="create-quiz-container">
            <!-- Header Card -->
            <div class="header-card">
                <div class="header-left">
                    <h1><i class="fas fa-question-circle" style="color: #4f46e5; margin-right: 10px;"></i>Create New Quiz</h1>
                    <div class="subject-badge">
                        <i class="fas fa-book-open"></i>
                        <?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)
                    </div>
                </div>
                <a href="view-subject.php?id=<?php echo $subject_id; ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Subject
                </a>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="/mywebsite10/controllers/QuizController.php?action=create" method="POST" id="quizForm">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    
                    <!-- Quiz Settings -->
                    <div class="form-section">
                        <div class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div>
                                <h2>Quiz Settings</h2>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Quiz Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g., Chapter 1 Quiz, Midterm Exam, etc." required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe what this quiz covers..."></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-clock"></i> Time Limit (minutes)</label>
                                <input type="number" name="time_limit" class="form-control" value="30" min="1" max="180" required>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-percent"></i> Passing Score (%)</label>
                                <input type="number" name="passing_score" class="form-control" value="70" min="0" max="100" required>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-redo"></i> Max Attempts</label>
                                <input type="number" name="max_attempts" class="form-control" value="1" min="1" max="10" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Questions Section (will be added in next step) -->
                    <div class="form-section">
                        <div class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <div>
                                <h2>Questions</h2>
                                <p style="color: #64748b; font-size: 14px;">You'll add questions after creating the quiz</p>
                            </div>
                        </div>
                        
                        <div style="background: #f8fafc; padding: 30px; text-align: center; border-radius: 16px;">
                            <i class="fas fa-question-circle" style="font-size: 48px; color: #cbd5e1; margin-bottom: 15px;"></i>
                            <h3 style="color: #475569; margin-bottom: 10px;">No Questions Yet</h3>
                            <p style="color: #64748b;">After creating the quiz, you'll be able to add multiple choice and true/false questions.</p>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="view-subject.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a quiz title');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>