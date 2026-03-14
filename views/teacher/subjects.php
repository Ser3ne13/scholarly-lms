<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';

$subject = new Subject();
$subjects = $subject->getTeacherSubjects($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects - Scholarly</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Modern Design CSS -->
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
</head>

<body>
    <!-- Modern Header -->
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

    <!-- Modern Sidebar -->
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

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>My Subjects</h1>
            <a href="create-subject.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Subject
            </a>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo count($subjects); ?></h3>
                    <p>Total Subjects</p>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Active Students</p>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Total Lessons</p>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-video"></i>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Pending Tasks</p>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Subjects Grid -->
        <?php if (empty($subjects)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>No Subjects Yet</h3>
                <p>Create your first subject to start your teaching journey with Scholarly.</p>
                <a href="create-subject.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Your First Subject
                </a>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($subjects as $index => $subject): ?>
                    <div class="modern-card" style="--card-order: <?php echo $index + 1; ?>">
                        <div class="card-badge"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                        <h3 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                        <div class="card-subtitle">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo htmlspecialchars($subject['schedule'] ?? 'Schedule not set'); ?>
                        </div>
                        
                        <div class="subject-stats">
                            <span class="stat-item">
                                <i class="fas fa-users"></i> 0 Students
                            </span>
                            <span class="stat-item">
                                <i class="fas fa-video"></i> 0 Lessons
                            </span>
                            <span class="stat-item">
                                <i class="fas fa-clock"></i> <?php echo htmlspecialchars($subject['semester'] ?? 'No semester'); ?>
                            </span>
                        </div>
                        
                        <div class="card-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($subject['room'] ?? 'No room'); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($subject['created_at'])); ?></span>
                        </div>
                        
                        <div class="card-actions">
                            <a href="/mywebsite10/controllers/SubjectController.php?action=view&id=<?php echo $subject['subject_id']; ?>" class="btn btn-primary" style="flex: 2;">
                                <i class="fas fa-eye"></i> View Subject
                            </a>
                            <a href="edit-subject.php?id=<?php echo $subject['subject_id']; ?>" class="btn btn-outline" style="flex: 1;">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>