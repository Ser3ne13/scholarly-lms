<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/Section.php';
require_once '../../models/User.php';
require_once '../../models/Schedule.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$section_id = $_GET['id'] ?? 0;

$sectionModel = new Section();
$userModel = new User();
$scheduleModel = new Schedule();

$section = $sectionModel->getSection($section_id);

if (!$section) {
    header('Location: sections.php');
    exit();
}

// Get students in this section
$section_students = $sectionModel->getSectionStudents($section_id);

// Get all available students (not in any section or can be added)
$all_students = $userModel->getAllStudents();

// Get section schedule
$section_schedule = $sectionModel->getSectionSchedule($section_id);

// Get available teachers for schedule assignment
$teachers = $userModel->getAllTeachers();

// Get available subjects
require_once '../../models/Subject.php';
$subjectModel = new Subject();
$subjects = $subjectModel->getAllSubjects();

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($section['section_name']); ?> - Section Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .section-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 2rem;
            color: #1e293b;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            padding: 10px 20px;
            background: #f1f5f9;
            border-radius: 40px;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #e2e8f0;
            transform: translateX(-3px);
        }

        .btn-edit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .section-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .section-icon-large {
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.2);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }

        .section-info h2 {
            color: white;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .section-meta {
            display: flex;
            gap: 20px;
            font-size: 16px;
            opacity: 0.9;
        }

        .section-meta i {
            margin-right: 8px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-info h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-info p {
            color: #64748b;
            font-size: 13px;
        }

        .tab-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .tab-headers {
            display: flex;
            border-bottom: 2px solid #eef2f6;
            background: #f8fafc;
        }

        .tab-btn {
            padding: 15px 30px;
            background: none;
            border: none;
            font-size: 15px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn.active {
            color: #4f46e5;
            border-bottom: 3px solid #4f46e5;
            background: white;
        }

        .tab-content {
            padding: 30px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* Students Table */
        .students-table {
            width: 100%;
            border-collapse: collapse;
        }

        .students-table th {
            text-align: left;
            padding: 15px;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .students-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eef2f6;
        }

        .students-table tr:hover {
            background: #f8fafc;
        }

        .student-avatar-small {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }

        .btn-remove {
            background: #fee2e2;
            color: #ef4444;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-remove:hover {
            background: #ef4444;
            color: white;
        }

        .btn-add-student {
            background: #4f46e5;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 20px;
        }

        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .schedule-day {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            min-height: 300px;
        }

        .day-header {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }

        .schedule-item {
            background: #eef2ff;
            border-left: 3px solid #4f46e5;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .schedule-time {
            font-weight: 600;
            color: #4f46e5;
            margin-bottom: 4px;
        }

        .schedule-subject {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .schedule-teacher {
            font-size: 11px;
            color: #64748b;
        }

        .btn-add-schedule {
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
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
        }

        .modal-content {
            background: white;
            width: 500px;
            border-radius: 30px;
            padding: 30px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
        }

        .btn-save {
            background: #4f46e5;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 40px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .schedule-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .section-header {
                flex-direction: column;
                text-align: center;
            }
            .schedule-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <header class="modern-header">
        <div class="header-content">
            <div class="logo">Scholarly Admin</div>
            <div class="user-menu">
                <span class="user-avatar">
                    <?php echo substr($_SESSION['first_name'] ?? 'A', 0, 1) . substr($_SESSION['last_name'] ?? 'D', 0, 1); ?>
                </span>
                <span><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></span>
                <a href="/mywebsite10/controllers/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <aside class="modern-sidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Master Schedule</a></li>
            <li><a href="sections.php" class="active"><i class="fas fa-layer-group"></i> Sections</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="section-container">
            <!-- Page Header -->
            <div class="page-header">
                <a href="sections.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Sections
                </a>
                <a href="edit-section.php?id=<?php echo $section_id; ?>" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Section
                </a>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Section Header -->
            <div class="section-header">
                <div class="section-icon-large">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="section-info">
                    <h2><?php echo htmlspecialchars($section['section_name']); ?></h2>
                    <div class="section-meta">
                        <span><i class="fas fa-calendar-alt"></i> <?php echo $section['academic_year']; ?></span>
                        <span><i class="fas fa-graduation-cap"></i> Year <?php echo $section['year_level']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($section_students); ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #10b981;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($section_schedule); ?></h3>
                        <p>Classes/Week</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3cd; color: #f59e0b;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php 
                            $unique_teachers = [];
                            foreach ($section_schedule as $class) {
                                $unique_teachers[$class['teacher_id']] = true;
                            }
                            echo count($unique_teachers);
                        ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #cffafe; color: #06b6d4;">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php 
                            $unique_rooms = [];
                            foreach ($section_schedule as $class) {
                                $unique_rooms[$class['room']] = true;
                            }
                            echo count($unique_rooms);
                        ?></h3>
                        <p>Rooms Used</p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tab-container">
                <div class="tab-headers">
                    <button class="tab-btn active" onclick="showTab('students')">
                        <i class="fas fa-users"></i> Students (<?php echo count($section_students); ?>)
                    </button>
                    <button class="tab-btn" onclick="showTab('schedule')">
                        <i class="fas fa-calendar-alt"></i> Class Schedule
                    </button>
                </div>

                <div class="tab-content">
                    <!-- Students Tab -->
                    <div id="students" class="tab-pane active">
                        <button class="btn-add-student" onclick="openAddStudentModal()">
                            <i class="fas fa-plus"></i> Add Student to Section
                        </button>

                        <?php if (empty($section_students)): ?>
                            <div style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 15px;"></i>
                                <h3>No Students in this Section</h3>
                                <p>Click "Add Student" to assign students to this section.</p>
                            </div>
                        <?php else: ?>
                            <table class="students-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($section_students as $student): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <div class="student-avatar-small">
                                                        <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                            <td>
                                                <a href="/mywebsite10/controllers/SectionController.php?action=remove_student&student_id=<?php echo $student['user_id']; ?>&section_id=<?php echo $section_id; ?>" 
                                                   class="btn-remove" 
                                                   onclick="return confirm('Remove this student from the section?')">
                                                    <i class="fas fa-times"></i> Remove
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- Schedule Tab -->
                    <div id="schedule" class="tab-pane">
                        <button class="btn-add-schedule" onclick="openAddScheduleModal()">
                            <i class="fas fa-plus"></i> Add Class to Schedule
                        </button>

                        <div class="schedule-grid">
                            <?php foreach ($days as $day): ?>
                                <div class="schedule-day">
                                    <div class="day-header"><?php echo $day; ?></div>
                                    <?php 
                                    $day_classes = array_filter($section_schedule, function($class) use ($day) {
                                        return $class['day_of_week'] === $day;
                                    });
                                    
                                    if (empty($day_classes)): ?>
                                        <div style="color: #94a3b8; text-align: center; padding: 10px;">
                                            <i class="fas fa-calendar-times"></i>
                                            <p>No classes</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($day_classes as $class): ?>
                                            <div class="schedule-item">
                                                <div class="schedule-time">
                                                    <?php echo date('h:i A', strtotime($class['start_time'])); ?> - 
                                                    <?php echo date('h:i A', strtotime($class['end_time'])); ?>
                                                </div>
                                                <div class="schedule-subject"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                                                <div class="schedule-teacher">
                                                    <i class="fas fa-chalkboard-teacher"></i> 
                                                    <?php echo htmlspecialchars($class['first_name'] . ' ' . $class['last_name']); ?>
                                                </div>
                                                <div style="font-size: 10px; margin-top: 5px;">
                                                    <i class="fas fa-door-open"></i> <?php echo $class['room']; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Student to Section</h3>
                <button class="close-btn" onclick="closeAddStudentModal()">&times;</button>
            </div>
            <form action="/mywebsite10/controllers/SectionController.php?action=add_student" method="POST">
                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">Choose a student</option>
                        <?php foreach ($all_students as $student): 
                            $already_in_section = false;
                            foreach ($section_students as $s) {
                                if ($s['user_id'] == $student['user_id']) {
                                    $already_in_section = true;
                                    break;
                                }
                            }
                            if (!$already_in_section):
                        ?>
                            <option value="<?php echo $student['user_id']; ?>">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> (<?php echo $student['email']; ?>)
                            </option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-save">Add to Section</button>
            </form>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Class to Schedule</h3>
                <button class="close-btn" onclick="closeAddScheduleModal()">&times;</button>
            </div>
            <form action="/mywebsite10/controllers/SectionScheduleController.php?action=add" method="POST">
                <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                
                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['subject_id']; ?>">
                                <?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo $subject['subject_code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Teacher</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['user_id']; ?>">
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Day</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="">Select Day</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Room</label>
                    <input type="text" name="room" class="form-control" placeholder="e.g., Room 201" required>
                </div>
                
                <button type="submit" class="btn-save">Add to Schedule</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function openAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'flex';
        }

        function closeAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'none';
        }

        function openAddScheduleModal() {
            document.getElementById('addScheduleModal').style.display = 'flex';
        }

        function closeAddScheduleModal() {
            document.getElementById('addScheduleModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>