<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/Subject.php';
require_once '../../models/User.php';
require_once '../../models/Enrollment.php';
require_once '../../models/Section.php';
require_once '../../models/Schedule.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$subjectModel = new Subject();
$userModel = new User();
$enrollmentModel = new Enrollment();
$sectionModel = new Section();
$scheduleModel = new Schedule();

// Get view type
$view = $_GET['view'] ?? 'teachers';

// Get all teachers for filter
$teachers = $userModel->getAllTeachers();

// Get all sections
$sections = $sectionModel->getAllSections();

// Get filter parameters
$selected_teacher = $_GET['teacher_id'] ?? 'all';
$selected_section = $_GET['section_id'] ?? '';
$selected_room = $_GET['room'] ?? '';
$academic_year = $_GET['academic_year'] ?? date('Y') . '-' . (date('Y') + 1);
$semester = $_GET['semester'] ?? '1st Semester';



// Get current week
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_timestamp = strtotime($current_date);
$week_start = date('Y-m-d', strtotime('monday this week', $current_timestamp));
$week_end = date('Y-m-d', strtotime('sunday this week', $current_timestamp));

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];


// Prepare Teachers Schedule Data
$teachers_schedule = [];
foreach ($days as $day) {
    $teachers_schedule[$day] = [];
}

// Get all subjects and organize by day
$all_subjects = $subjectModel->getAllSubjects();
foreach ($all_subjects as $subject) {
    if (!empty($subject['schedule'])) {
        $schedule_parts = explode(' ', $subject['schedule']);
        $days_part = $schedule_parts[0] ?? '';
        $time_part = $schedule_parts[1] ?? '';
        
        $day_codes = explode('/', $days_part);
        foreach ($day_codes as $code) {
            $full_day = '';
            switch ($code) {
                case 'Mon': $full_day = 'Monday'; break;
                case 'Tue': $full_day = 'Tuesday'; break;
                case 'Wed': $full_day = 'Wednesday'; break;
                case 'Thu': $full_day = 'Thursday'; break;
                case 'Fri': $full_day = 'Friday'; break;
                case 'Sat': $full_day = 'Saturday'; break;
                case 'Sun': $full_day = 'Sunday'; break;
            }
            
            if ($full_day) {
                // Get teacher name
                $teacher_name = '';
                foreach ($teachers as $teacher) {
                    if ($teacher['user_id'] == $subject['teacher_id']) {
                        $teacher_name = $teacher['first_name'] . ' ' . $teacher['last_name'];
                        break;
                    }
                }
                
                $teachers_schedule[$full_day][] = [
                    'time' => $time_part,
                    'subject_name' => $subject['subject_name'],
                    'subject_code' => $subject['subject_code'],
                    'teacher_name' => $teacher_name,
                    'room' => $subject['room'] ?? 'TBA'
                ];
            }
        }
    }
}

// Prepare Sections Schedule Data
$sections_schedule = [];
foreach ($days as $day) {
    $sections_schedule[$day] = [];
}

// Get all sections and their schedules
foreach ($sections as $section) {
    $section_schedule = $sectionModel->getSectionSchedule($section['section_id']);
    foreach ($section_schedule as $item) {
        $sections_schedule[$item['day_of_week']][] = [
            'time' => date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])),
            'subject_name' => $item['subject_name'],
            'subject_code' => $item['subject_code'],
            'teacher_name' => $item['first_name'] . ' ' . $item['last_name'],
            'room' => $item['room']
        ];
    }
}

// Prepare Rooms Schedule Data
$rooms_schedule = [];
foreach ($days as $day) {
    $rooms_schedule[$day] = [];
}

// Get unique rooms from subjects
$all_rooms = [];
foreach ($all_subjects as $subject) {
    if (!empty($subject['room']) && !in_array($subject['room'], $all_rooms)) {
        $all_rooms[] = $subject['room'];
    }
}

// For each room, get its schedule
foreach ($all_rooms as $room) {
    $room_schedule = $scheduleModel->getRoomSchedule($room);
    foreach ($room_schedule as $item) {
        $rooms_schedule[$item['day_of_week']][] = [
            'time' => date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])),
            'subject_name' => $item['subject_name'],
            'subject_code' => $item['subject_code'],
            'teacher_name' => $item['first_name'] . ' ' . $item['last_name'],
            'room' => $item['room']
        ];
    }
}

// Initialize schedule array
$schedule = [];
foreach ($days as $day) {
    $schedule[$day] = [];
}

// Get data based on view
if ($view === 'teachers') {
    // Filter subjects by teacher
    $all_subjects = $subjectModel->getAllSubjects();
    $filtered_subjects = $all_subjects;
    if ($selected_teacher !== 'all') {
        $filtered_subjects = array_filter($all_subjects, function($subject) use ($selected_teacher) {
            return $subject['teacher_id'] == $selected_teacher;
        });
    }
    
    // Parse schedule from subjects
    foreach ($filtered_subjects as $subject) {
        if (!empty($subject['schedule'])) {
            $schedule_parts = explode(' ', $subject['schedule']);
            $days_part = $schedule_parts[0] ?? '';
            $time_part = $schedule_parts[1] ?? '';
            
            $day_codes = explode('/', $days_part);
            foreach ($day_codes as $code) {
                $full_day = '';
                switch ($code) {
                    case 'Mon': $full_day = 'Monday'; break;
                    case 'Tue': $full_day = 'Tuesday'; break;
                    case 'Wed': $full_day = 'Wednesday'; break;
                    case 'Thu': $full_day = 'Thursday'; break;
                    case 'Fri': $full_day = 'Friday'; break;
                    case 'Sat': $full_day = 'Saturday'; break;
                    case 'Sun': $full_day = 'Sunday'; break;
                }
                
                if ($full_day) {
                    // Get teacher name
                    $teacher_name = '';
                    foreach ($teachers as $teacher) {
                        if ($teacher['user_id'] == $subject['teacher_id']) {
                            $teacher_name = $teacher['first_name'] . ' ' . $teacher['last_name'];
                            break;
                        }
                    }
                    
                    $schedule[$full_day][] = [
                        'time' => $time_part,
                        'subject_name' => $subject['subject_name'],
                        'subject_code' => $subject['subject_code'],
                        'teacher_name' => $teacher_name,
                        'teacher_id' => $subject['teacher_id'],
                        'room' => $subject['room'] ?? 'TBA',
                        'type' => 'subject'
                    ];
                }
            }
        }
    }
} 
elseif ($view === 'sections' && $selected_section) {
    // Get section schedule
    $schedule_data = $sectionModel->getSectionSchedule($selected_section);
    foreach ($schedule_data as $item) {
        $schedule[$item['day_of_week']][] = [
            'time' => date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])),
            'subject_name' => $item['subject_name'],
            'subject_code' => $item['subject_code'],
            'teacher_name' => $item['first_name'] . ' ' . $item['last_name'],
            'room' => $item['room'],
            'type' => 'section'
        ];
    }
} 
elseif ($view === 'rooms' && $selected_room) {
    // Get room schedule
    $schedule_data = $scheduleModel->getRoomSchedule($selected_room, $academic_year, $semester);
    foreach ($schedule_data as $item) {
        $schedule[$item['day_of_week']][] = [
            'time' => date('h:i A', strtotime($item['start_time'])) . ' - ' . date('h:i A', strtotime($item['end_time'])),
            'subject_name' => $item['subject_name'],
            'subject_code' => $item['subject_code'],
            'teacher_name' => $item['first_name'] . ' ' . $item['last_name'],
            'room' => $item['room'],
            'type' => 'room'
        ];
    }
}

// Calculate stats
$total_classes = 0;
$teacher_ids = [];
foreach ($schedule as $day => $classes) {
    $total_classes += count($classes);
    foreach ($classes as $class) {
        if (isset($class['teacher_id'])) {
            $teacher_ids[$class['teacher_id']] = true;
        }
    }
}
$total_teachers_active = count($teacher_ids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Schedule - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .schedule-container {
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

        /* View Tabs */
        .view-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            background: white;
            padding: 10px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .view-tab {
            padding: 12px 25px;
            border-radius: 40px;
            text-decoration: none;
            color: #64748b;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-tab:hover {
            background: #f1f5f9;
        }

        .view-tab.active {
            background: #4f46e5;
            color: white;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            gap: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .filter-select, .filter-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
        }

        .week-navigation {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .week-nav-btn {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .week-nav-btn:hover {
            background: #4f46e5;
            color: white;
        }

        .current-week {
            background: #eef2ff;
            color: #4f46e5;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: 600;
        }

        .btn-apply {
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-apply:hover {
            background: #4338ca;
        }

        .reset-btn {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
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

        /* Schedule Grid */
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .day-column {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .day-header {
            background: #f8fafc;
            padding: 15px;
            text-align: center;
            border-bottom: 2px solid #eef2f6;
        }

        .day-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
        }

        .day-date {
            font-size: 13px;
            color: #64748b;
        }

        .day-content {
            padding: 15px;
            min-height: 500px;
        }

        .schedule-card {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .schedule-card.teacher { border-left-color: #4f46e5; }
        .schedule-card.section { border-left-color: #10b981; }
        .schedule-card.room { border-left-color: #f59e0b; }

        .schedule-card:hover {
            transform: translateX(3px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.2);
        }

        .card-time {
            font-size: 11px;
            color: #4f46e5;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
            color: #1e293b;
        }

        .card-teacher {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-room {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-code {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            padding: 2px 8px;
            border-radius: 30px;
            font-size: 9px;
            font-weight: 600;
            display: inline-block;
        }

        .empty-day {
            text-align: center;
            padding: 30px 10px;
            color: #94a3b8;
            font-size: 13px;
        }

        .empty-day i {
            font-size: 30px;
            margin-bottom: 10px;
            opacity: 0.3;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn-add {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 25px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        /* List View */
        .list-view {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .list-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-btn {
            padding: 8px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .class-list-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eef2f6;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .class-list-item:hover {
            border-color: #4f46e5;
            background: #f8fafc;
        }

        .list-day {
            width: 100px;
            font-weight: 600;
            color: #4f46e5;
        }

        .list-time {
            width: 150px;
            font-size: 14px;
        }

        .list-info {
            flex: 1;
        }

        .list-title {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .list-details {
            font-size: 12px;
            color: #64748b;
        }

        .list-room {
            width: 120px;
            text-align: right;
            font-weight: 500;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }

        .badge-teacher { background: #eef2ff; color: #4f46e5; }
        .badge-section { background: #dcfce7; color: #166534; }
        .badge-room { background: #fff3cd; color: #856404; }

        @media (max-width: 1024px) {
            .schedule-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .schedule-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .filter-bar {
                flex-direction: column;
            }
            .view-tabs {
                flex-wrap: wrap;
            }
            .class-list-item {
                flex-wrap: wrap;
            }
        }

        /* View containers */
.view-container {
    display: none;
}

.view-container.active {
    display: block;
}

#teachers-view {
    display: block; /* Teachers view visible by default */
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
            <li><a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> Master Schedule</a></li>
            <li><a href="sections.php"><i class="fas fa-layer-group"></i> Sections</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="schedule-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Master Schedule</h1>
                <div class="action-buttons">
                    <a href="add-schedule.php" class="btn-add">
                        <i class="fas fa-plus"></i> Add Schedule
                    </a>
                </div>
            </div>

            <!-- View Tabs -->
            <div class="view-tabs">
                <a href="#" onclick="switchView('teachers'); return false;" class="view-tab active">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
                <a href="#" onclick="switchView('sections'); return false;" class="view-tab">
                    <i class="fas fa-layer-group"></i> Sections
                </a>
                <a href="#" onclick="switchView('rooms'); return false;" class="view-tab">
                    <i class="fas fa-door-open"></i> Rooms
                </a>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <?php if ($view === 'teachers'): ?>
                    <div class="filter-group">
                        <label><i class="fas fa-chalkboard-teacher"></i> Select Teacher</label>
                        <select class="filter-select" id="teacherSelect" onchange="filterByTeacher(this.value)">
                            <option value="all">All Teachers</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['user_id']; ?>" 
                                    <?php echo $selected_teacher == $teacher['user_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php elseif ($view === 'sections'): ?>
                    <div class="filter-group">
                        <label><i class="fas fa-layer-group"></i> Select Section</label>
                        <select class="filter-select" id="sectionSelect" onchange="filterBySection(this.value)">
                            <option value="">Choose Section</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['section_id']; ?>" 
                                    <?php echo $selected_section == $section['section_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['section_name']); ?> (Year <?php echo $section['year_level']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php elseif ($view === 'rooms'): ?>
                    <div class="filter-group">
                        <label><i class="fas fa-door-open"></i> Room</label>
                        <input type="text" class="filter-input" id="roomInput" 
                               value="<?php echo htmlspecialchars($selected_room); ?>" 
                               placeholder="Enter room number">
                    </div>
                    <div class="filter-group">
                        <label>Academic Year</label>
                        <select class="filter-select" id="academicYear">
                            <option value="2024-2025" <?php echo $academic_year == '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                            <option value="2025-2026" <?php echo $academic_year == '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Semester</label>
                        <select class="filter-select" id="semester">
                            <option value="1st Semester" <?php echo $semester == '1st Semester' ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2nd Semester" <?php echo $semester == '2nd Semester' ? 'selected' : ''; ?>>2nd Semester</option>
                            <option value="Summer" <?php echo $semester == 'Summer' ? 'selected' : ''; ?>>Summer</option>
                        </select>
                    </div>
                    <button class="btn-apply" onclick="filterByRoom()">Apply</button>
                <?php endif; ?>

                <div class="filter-group">
                    <label>Week</label>
                    <div class="week-navigation">
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['date' => date('Y-m-d', strtotime('-1 week', strtotime($week_start)))])); ?>" class="week-nav-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <span class="current-week">
                            <?php echo date('M j', strtotime($week_start)); ?> - <?php echo date('M j', strtotime($week_end)); ?>
                        </span>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['date' => date('Y-m-d', strtotime('+1 week', strtotime($week_start)))])); ?>" class="week-nav-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <?php if (($view === 'teachers' && $selected_teacher != 'all') || 
                          ($view === 'sections' && $selected_section) || 
                          ($view === 'rooms' && $selected_room)): ?>
                    <a href="schedule.php?view=<?php echo $view; ?>" class="reset-btn">
                        <i class="fas fa-times"></i> Clear Filter
                    </a>
                <?php endif; ?>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_classes; ?></h3>
                        <p>Total Classes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #10b981;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_teachers_active; ?></h3>
                        <p>Active Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3cd; color: #f59e0b;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($sections); ?></h3>
                        <p>Sections</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #cffafe; color: #06b6d4;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo round($total_classes / 7); ?></h3>
                        <p>Avg Classes/Day</p>
                    </div>
                </div>
            </div>

<!-- Schedule Views Container -->
<div class="schedule-views">
    <!-- Teachers View -->
    <div id="teachers-view" class="view-container active">
        <div class="schedule-grid">
            <?php foreach ($days as $index => $day): 
                $day_date = date('Y-m-d', strtotime("$week_start + $index days"));
            ?>
                <div class="day-column">
                    <div class="day-header">
                        <div class="day-name"><?php echo $day; ?></div>
                        <div class="day-date"><?php echo date('M j', strtotime($day_date)); ?></div>
                    </div>
                    <div class="day-content">
                        <?php if (empty($teachers_schedule[$day])): ?>
                            <div class="empty-day">
                                <i class="fas fa-calendar-times"></i>
                                <p>No classes</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($teachers_schedule[$day] as $class): ?>
                                <div class="schedule-card">
                                    <div class="card-time">
                                        <i class="far fa-clock"></i> <?php echo $class['time']; ?>
                                    </div>
                                    <div class="card-title">
                                        <?php echo htmlspecialchars($class['subject_name']); ?>
                                    </div>
                                    <div class="card-teacher">
                                        <i class="fas fa-chalkboard-teacher"></i> 
                                        <?php echo htmlspecialchars($class['teacher_name']); ?>
                                    </div>
                                    <div class="card-room">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($class['room']); ?>
                                    </div>
                                    <span class="card-code"><?php echo $class['subject_code']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Sections View -->
    <div id="sections-view" class="view-container">
        <div class="schedule-grid">
            <?php foreach ($days as $index => $day): 
                $day_date = date('Y-m-d', strtotime("$week_start + $index days"));
            ?>
                <div class="day-column">
                    <div class="day-header">
                        <div class="day-name"><?php echo $day; ?></div>
                        <div class="day-date"><?php echo date('M j', strtotime($day_date)); ?></div>
                    </div>
                    <div class="day-content">
                        <?php if (empty($sections_schedule[$day])): ?>
                            <div class="empty-day">
                                <i class="fas fa-calendar-times"></i>
                                <p>No classes</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($sections_schedule[$day] as $class): ?>
                                <div class="schedule-card section">
                                    <div class="card-time">
                                        <i class="far fa-clock"></i> <?php echo $class['time']; ?>
                                    </div>
                                    <div class="card-title">
                                        <?php echo htmlspecialchars($class['subject_name']); ?>
                                    </div>
                                    <div class="card-teacher">
                                        <i class="fas fa-chalkboard-teacher"></i> 
                                        <?php echo htmlspecialchars($class['teacher_name']); ?>
                                    </div>
                                    <div class="card-room">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($class['room']); ?>
                                    </div>
                                    <span class="card-code"><?php echo $class['subject_code']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Rooms View -->
    <div id="rooms-view" class="view-container">
        <div class="schedule-grid">
            <?php foreach ($days as $index => $day): 
                $day_date = date('Y-m-d', strtotime("$week_start + $index days"));
            ?>
                <div class="day-column">
                    <div class="day-header">
                        <div class="day-name"><?php echo $day; ?></div>
                        <div class="day-date"><?php echo date('M j', strtotime($day_date)); ?></div>
                    </div>
                    <div class="day-content">
                        <?php if (empty($rooms_schedule[$day])): ?>
                            <div class="empty-day">
                                <i class="fas fa-calendar-times"></i>
                                <p>No classes</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($rooms_schedule[$day] as $class): ?>
                                <div class="schedule-card room">
                                    <div class="card-time">
                                        <i class="far fa-clock"></i> <?php echo $class['time']; ?>
                                    </div>
                                    <div class="card-title">
                                        <?php echo htmlspecialchars($class['subject_name']); ?>
                                    </div>
                                    <div class="card-teacher">
                                        <i class="fas fa-chalkboard-teacher"></i> 
                                        <?php echo htmlspecialchars($class['teacher_name']); ?>
                                    </div>
                                    <div class="card-room">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($class['room']); ?>
                                    </div>
                                    <span class="card-code"><?php echo $class['subject_code']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

                <?php 
                $all_classes = [];
                foreach ($schedule as $day => $classes) {
                    foreach ($classes as $class) {
                        $class['day'] = $day;
                        $all_classes[] = $class;
                    }
                }
                
                // Sort by day
                usort($all_classes, function($a, $b) use ($days) {
                    return array_search($a['day'], $days) - array_search($b['day'], $days);
                });
                ?>
                
                <?php if (empty($all_classes)): ?>
                    <p style="text-align: center; color: #64748b; padding: 30px;">No classes scheduled</p>
                <?php else: ?>
                    <?php foreach ($all_classes as $class): ?>
                        <div class="class-list-item">
                            <div class="list-day"><?php echo $class['day']; ?></div>
                            <div class="list-time"><?php echo $class['time']; ?></div>
                            <div class="list-info">
                                <div class="list-title"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                                <div class="list-details">
                                    <?php echo $class['subject_code']; ?> • 
                                    <?php echo htmlspecialchars($class['teacher_name']); ?>
                                </div>
                            </div>
                            <div class="list-room">
                                <?php echo htmlspecialchars($class['room']); ?>
                                <span class="badge badge-<?php echo $class['type'] ?? 'teacher'; ?>">
                                    <?php echo ucfirst($class['type'] ?? 'class'); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        
    </main>

    

    <script>
        function filterByTeacher(teacherId) {
            window.location.href = 'schedule.php?view=teachers&teacher_id=' + teacherId + '&date=<?php echo $current_date; ?>';
        }
        
        function filterBySection(sectionId) {
            if (sectionId) {
                window.location.href = 'schedule.php?view=sections&section_id=' + sectionId + '&date=<?php echo $current_date; ?>';
            }
        }
        
        function filterByRoom() {
            const room = document.getElementById('roomInput').value;
            const academicYear = document.getElementById('academicYear').value;
            const semester = document.getElementById('semester').value;
            
            if (room) {
                window.location.href = 'schedule.php?view=rooms&room=' + encodeURIComponent(room) + 
                                      '&academic_year=' + academicYear + '&semester=' + semester + 
                                      '&date=<?php echo $current_date; ?>';
            }
        }
        
        function toggleView(view) {
            const grid = document.getElementById('gridView');
            const list = document.getElementById('listView');
            const btns = document.querySelectorAll('.view-btn');
            
            if (view === 'grid') {
                grid.style.display = 'grid';
                list.style.display = 'none';
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
            } else {
                grid.style.display = 'none';
                list.style.display = 'block';
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
            }
        }
        

        // Simple tab switching without page reload
        function switchView(view) {
            // Hide all views
            document.getElementById('teachers-view').style.display = 'none';
            document.getElementById('sections-view').style.display = 'none';
            document.getElementById('rooms-view').style.display = 'none';
            
            // Remove active class from all tabs
            document.querySelectorAll('.view-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected view
            document.getElementById(view + '-view').style.display = 'block';
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Update URL without reloading
            history.pushState({}, '', '?view=' + view);
        }

        // Auto-dismiss messages after 3 seconds
setTimeout(function() {
    document.querySelectorAll('.alert-success, .alert-error, .alert').forEach(function(alert) {
        if (alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    alert.style.display = 'none';
                }
            }, 500);
        }
    });
}, 3000);

        
        

    </script>
</body>
</html>