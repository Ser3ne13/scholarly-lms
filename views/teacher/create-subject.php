<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Subject - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
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
            <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Create New Subject</h1>
            <a href="subjects.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Subjects
            </a>
        </div>

        <div class="modern-form">
            <form action="/mywebsite10/controllers/SubjectController.php?action=create" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject_code">Subject Code</label>
                        <input type="text" id="subject_code" name="subject_code" class="form-control" 
                               placeholder="e.g., CS101" required>
                    </div>

                    <div class="form-group">
                        <label for="subject_name">Subject Name</label>
                        <input type="text" id="subject_name" name="subject_name" class="form-control" 
                               placeholder="e.g., Introduction to Programming" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" 
                              placeholder="Describe what this subject covers..." rows="4"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="schedule">Schedule</label>
                        <input type="text" id="schedule" name="schedule" class="form-control" 
                               placeholder="e.g., Mon/Wed 10:00-11:30">
                    </div>

                    <div class="form-group">
                        <label for="room">Room</label>
                        <input type="text" id="room" name="room" class="form-control" 
                               placeholder="e.g., Room 201">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <select id="academic_year" name="academic_year" class="form-control">
                            <option value="2024-2025">2024-2025</option>
                            <option value="2025-2026">2025-2026</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" class="form-control">
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-save"></i> Create Subject
                    </button>
                    <a href="subjects.php" class="btn btn-outline" style="flex: 0.3;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>