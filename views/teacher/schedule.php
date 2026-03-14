<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Subject.php';
require_once '../../models/User.php';

$subjectModel = new Subject();
$userModel = new User();

// Get all subjects taught by this teacher
$subjects = $subjectModel->getTeacherSubjects($_SESSION['user_id']);

// Get current week dates
$current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$current_timestamp = strtotime($current_date);
$week_start = date('Y-m-d', strtotime('monday this week', $current_timestamp));
$week_end = date('Y-m-d', strtotime('sunday this week', $current_timestamp));

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Organize subjects by day
$schedule = [];
foreach ($days as $day) {
    $schedule[$day] = [];
}

foreach ($subjects as $subject) {
    if (!empty($subject['schedule'])) {
        // Parse schedule string (e.g., "Mon/Wed 10:00-11:30")
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
            
            if ($full_day && isset($schedule[$full_day])) {
                $subject['time'] = $time_part;
                $schedule[$full_day][] = $subject;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .schedule-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .schedule-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .week-navigation {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .week-nav-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .week-nav-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .current-week {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 30px;
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
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-icon.primary { background: #eef2ff; color: #4f46e5; }
        .stat-icon.success { background: #dcfce7; color: #10b981; }
        .stat-icon.warning { background: #fff3cd; color: #f59e0b; }
        .stat-icon.info { background: #cffafe; color: #06b6d4; }

        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #64748b;
            font-size: 14px;
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
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #eef2f6;
        }

        .day-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .day-date {
            font-size: 14px;
            color: #64748b;
        }

        .day-content {
            padding: 15px;
            min-height: 400px;
        }

        .schedule-item {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }

        .schedule-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.2);
        }

        .item-time {
            font-size: 12px;
            color: #4f46e5;
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .item-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .item-code {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .item-room {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .empty-day {
            text-align: center;
            padding: 30px 15px;
            color: #94a3b8;
            font-size: 14px;
        }

        .empty-day i {
            font-size: 40px;
            margin-bottom: 10px;
            opacity: 0.3;
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

        .subject-list-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eef2f6;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .subject-list-item:hover {
            border-color: #4f46e5;
            background: #f8fafc;
        }

        .list-item-time {
            width: 150px;
            font-weight: 600;
            color: #4f46e5;
        }

        .list-item-info {
            flex: 1;
        }

        .list-item-title {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .list-item-details {
            font-size: 13px;
            color: #64748b;
        }

        .list-item-room {
            width: 100px;
            text-align: right;
            color: #64748b;
        }

        @media (max-width: 1024px) {
            .schedule-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 768px) {
            .schedule-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .schedule-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
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
            <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="schedule.php" class="active"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="quizzes.php"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="analytics.php"><i class="fas fa-chart-line"></i> Analytics</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="schedule-container">
            <!-- Schedule Header -->
            <div class="schedule-header">
                <div>
                    <h1>My Teaching Schedule</h1>
                    <p>Week of <?php echo date('F j, Y', strtotime($week_start)); ?> - <?php echo date('F j, Y', strtotime($week_end)); ?></p>
                </div>
                
                <div class="week-navigation">
                    <a href="?date=<?php echo date('Y-m-d', strtotime('-1 week', strtotime($week_start))); ?>" class="week-nav-btn">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <a href="schedule.php" class="week-nav-btn">
                        <i class="fas fa-calendar"></i> Today
                    </a>
                    <a href="?date=<?php echo date('Y-m-d', strtotime('+1 week', strtotime($week_start))); ?>" class="week-nav-btn">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($subjects); ?></h3>
                        <p>Total Subjects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $total_hours = 0;
                        foreach ($subjects as $subject) {
                            if (!empty($subject['schedule'])) {
                                $total_hours += 2; // Assuming 2 hours per session
                            }
                        }
                        ?>
                        <h3><?php echo $total_hours; ?>h</h3>
                        <p>Weekly Hours</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $total_students = 0;
                        foreach ($subjects as $subject) {
                            $total_students += $subjectModel->getEnrolledStudentsCount($subject['subject_id']);
                        }
                        ?>
                        <h3><?php echo $total_students; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $scheduled_days = 0;
                        foreach ($schedule as $day => $classes) {
                            if (!empty($classes)) $scheduled_days++;
                        }
                        ?>
                        <h3><?php echo $scheduled_days; ?></h3>
                        <p>Teaching Days</p>
                    </div>
                </div>
            </div>

            <!-- Schedule Grid -->
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
                            <?php if (empty($schedule[$day])): ?>
                                <div class="empty-day">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>No classes</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($schedule[$day] as $class): ?>
                                    <div class="schedule-item">
                                        <div class="item-time">
                                            <i class="far fa-clock"></i> <?php echo $class['time']; ?>
                                        </div>
                                        <div class="item-title"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                                        <div class="item-code"><?php echo $class['subject_code']; ?></div>
                                        <div class="item-room">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo $class['room'] ?? 'TBA'; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- List View -->
            <div class="list-view">
                <div class="list-header">
                    <h3><i class="fas fa-list"></i> All Scheduled Classes</h3>
                    <div class="view-toggle">
                        <button class="view-btn active" onclick="toggleView('grid')">Grid</button>
                        <button class="view-btn" onclick="toggleView('list')">List</button>
                    </div>
                </div>

                <div id="listView">
                    <?php 
                    $all_classes = [];
                    foreach ($schedule as $day => $classes) {
                        foreach ($classes as $class) {
                            $class['day'] = $day;
                            $all_classes[] = $class;
                        }
                    }
                    
                    // Sort by day and time
                    usort($all_classes, function($a, $b) use ($days) {
                        $day_order = array_search($a['day'], $days) - array_search($b['day'], $days);
                        if ($day_order == 0) {
                            return strcmp($a['time'], $b['time']);
                        }
                        return $day_order;
                    });
                    ?>
                    
                    <?php foreach ($all_classes as $class): ?>
                        <div class="subject-list-item">
                            <div class="list-item-time">
                                <i class="far fa-calendar"></i> <?php echo $class['day']; ?><br>
                                <small><?php echo $class['time']; ?></small>
                            </div>
                            <div class="list-item-info">
                                <div class="list-item-title"><?php echo htmlspecialchars($class['subject_name']); ?></div>
                                <div class="list-item-details">
                                    <?php echo $class['subject_code']; ?> • 
                                    <?php echo $subjectModel->getEnrolledStudentsCount($class['subject_id']); ?> students
                                </div>
                            </div>
                            <div class="list-item-room">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $class['room'] ?? 'TBA'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleView(view) {
            const grid = document.querySelector('.schedule-grid');
            const list = document.getElementById('listView');
            const btns = document.querySelectorAll('.view-btn');
            
            if (view === 'grid') {
                grid.style.display = 'grid';
                list.style.display = 'block';
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
            } else {
                grid.style.display = 'none';
                list.style.display = 'block';
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
            }
        }
    </script>
</body>
</html>