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
    <title>Create Assignment - <?php echo htmlspecialchars($subject['subject_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .create-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
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
        <div class="create-container">
            <div style="margin-bottom: 20px;">
                <a href="view-subject.php?id=<?php echo $subject_id; ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($subject['subject_name']); ?>
                </a>
            </div>
            
            <div class="form-card">
                <h1 style="margin-bottom: 30px;">Create New Assignment</h1>
                
                <form action="/mywebsite10/controllers/AssignmentController.php?action=create" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    
                    <div class="form-group">
                        <label>Assignment Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea name="instructions" class="form-control" rows="5" placeholder="Provide detailed instructions for students..."></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Total Points</label>
                            <input type="number" name="total_points" class="form-control" value="100" min="1" max="1000">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Attachment (Optional)</label>
                        <input type="file" name="assignment_file" class="form-control">
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create Assignment
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>