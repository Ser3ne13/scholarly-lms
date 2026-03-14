<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/Section.php';
require_once '../../models/User.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

$sectionModel = new Section();
$userModel = new User();

// Get all sections
$sections = $sectionModel->getAllSections();

// Get all students for assignment
$students = $userModel->getAllStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sections Management - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        
        .sections-container {
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

        .btn-create {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
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

        .filter-bar {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 2;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
        }

        .filter-select {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            min-width: 150px;
        }

        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .section-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
            transition: all 0.3s;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.1);
            border-color: #4f46e5;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .section-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
        }

        .section-year {
            background: #eef2ff;
            color: #4f46e5;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .section-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            color: #64748b;
            font-size: 13px;
        }

        .section-meta i {
            color: #4f46e5;
            margin-right: 5px;
        }

        .students-preview {
            background: #f8fafc;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .students-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .student-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .student-tag {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            padding: 4px 12px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .student-tag i {
            color: #4f46e5;
            font-size: 10px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-view {
            background: #eef2ff;
            color: #4f46e5;
        }

        .btn-view:hover {
            background: #4f46e5;
            color: white;
        }

        .btn-edit {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-edit:hover {
            background: #f59e0b;
            color: white;
        }

        .btn-schedule {
            background: #dcfce7;
            color: #166534;
        }

        .btn-schedule:hover {
            background: #10b981;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 24px;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #1e293b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #64748b;
            margin-bottom: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-btn {
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 10px;
            cursor: pointer;
        }

        .page-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sections-grid {
                grid-template-columns: 1fr;
            }
            .filter-bar {
                flex-direction: column;
            }
        }

        /* Year Level Dividers */
.year-divider {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 40px 0 20px;
}

.year-divider h2 {
    font-size: 1.8rem;
    color: #1e293b;
    margin: 0;
}

.year-divider-line {
    flex: 1;
    height: 2px;
    background: linear-gradient(90deg, #e2e8f0, transparent);
}

.year-badge {
    background: #eef2ff;
    color: #4f46e5;
    padding: 5px 15px;
    border-radius: 30px;
    font-weight: 600;
}

/* Section Cards with colored borders */
.section-card.year-1 { border-top: 4px solid #4f46e5; }
.section-card.year-2 { border-top: 4px solid #10b981; }
.section-card.year-3 { border-top: 4px solid #f59e0b; }
.section-card.year-4 { border-top: 4px solid #ef4444; }
.section-card.year-5 { border-top: 4px solid #8b5cf6; }

/* Year navigation pills */
.year-nav {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
}

.year-nav:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}


/* View toggle buttons active state */
#gridViewBtn.active, #compactViewBtn.active, #listViewBtn.active {
    background: white !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    color: #4f46e5;
}

/* Compact View */
.compact-view .section-card {
    padding: 12px !important;
}

.compact-view .section-name {
    font-size: 1rem !important;
}

.compact-view .section-year {
    font-size: 10px !important;
    padding: 2px 8px !important;
}

.compact-view .section-meta span {
    font-size: 10px !important;
}

.compact-view .students-preview {
    padding: 6px !important;
}

.compact-view .student-tag {
    padding: 2px 6px !important;
    font-size: 9px !important;
}

.compact-view .action-btn {
    padding: 4px !important;
    font-size: 10px !important;
}

.compact-view .sections-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
    gap: 12px !important;
}

/* List View */
.list-view .sections-grid {
    display: none !important;
}

#sectionsListView {
    display: none;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.list-view #sectionsListView {
    display: block;
}

.section-list-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eef2f6;
    transition: all 0.2s;
}

.section-list-item:hover {
    background: #f8fafc;
}

.list-item-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.list-item-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 20px;
}

.list-item-name {
    font-weight: 600;
    min-width: 200px;
}

.list-item-level {
    font-size: 13px;
    color: #64748b;
    min-width: 100px;
}

.list-item-year {
    font-size: 13px;
    color: #64748b;
    min-width: 100px;
}

.list-item-students {
    font-size: 13px;
    color: #64748b;
    min-width: 80px;
}

.list-item-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.list-action-btn {
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
}

@media (max-width: 1024px) {
    .list-item-info {
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .list-item-name {
        min-width: 150px;
    }
}

       .list-level-section {
            margin-bottom: 30px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: white;
        }
        
        .list-level-header {
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 2px solid rgba(0,0,0,0.03);
        }
        
        .list-level-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s;
        }
        
        .list-level-title {
            font-size: 1.5rem;
            font-weight: 700;
            flex: 1;
        }
        
        .list-level-count {
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .list-item-enhanced {
            display: flex;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            position: relative;
            background: white;
        }
        
        .list-item-enhanced:last-child {
            border-bottom: none;
        }
        
        .list-item-enhanced:hover {
            background: #f8fafc;
            transform: translateX(5px);
            box-shadow: -5px 0 0 inset;
        }
        
        .list-item-avatar {
            width: 55px;
            height: 55px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 22px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        
        .list-item-enhanced:hover .list-item-avatar {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .list-item-details {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .list-item-name-section {
            min-width: 220px;
        }
        
        .list-item-name {
            font-weight: 700;
            font-size: 1.2rem;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .list-item-sub {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .list-item-badge {
            padding: 8px 18px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            min-width: 120px;
        }
        
        .list-item-stats {
            display: flex;
            gap: 15px;
            color: #475569;
            font-size: 14px;
        }
        
        .list-item-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 30px;
            background: #f8fafc;
            transition: all 0.2s;
        }
        
        .list-item-enhanced:hover .list-item-stat {
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .list-item-actions-enhanced {
            display: flex;
            gap: 12px;
            margin-left: auto;
        }
        
        .list-action-btn-enhanced {
            padding: 10px 18px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .list-action-btn-enhanced.view-btn {
            background: #eef2ff;
            color: #4f46e5;
        }
        
        .list-action-btn-enhanced.view-btn:hover {
            background: #4f46e5;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        
        .list-action-btn-enhanced.edit-btn {
            background: #f1f5f9;
            color: #475569;
        }
        
        .list-action-btn-enhanced.edit-btn:hover {
            background: #f59e0b;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        }
        
        .list-action-btn-enhanced.schedule-btn {
            background: #dcfce7;
            color: #166534;
        }
        
        .list-action-btn-enhanced.schedule-btn:hover {
            background: #10b981;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        
        @keyframes slideInLevel {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .list-level-section {
            animation: slideInLevel 0.4s ease forwards;
            animation-delay: calc(var(--level-index) * 0.1s);
            opacity: 0;
        }
        
        @keyframes slideInItem {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .list-item-enhanced {
            animation: slideInItem 0.3s ease forwards;
            animation-delay: calc(var(--item-index) * 0.05s);
            opacity: 0;
        }
        
        /* Empty state for levels with no sections */
        .level-empty {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
            background: #f8fafc;
            border-radius: 16px;
            margin: 20px;
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
        <div class="sections-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Sections Management</h1>
                <a href="create-section.php" class="btn-create">
                    <i class="fas fa-plus"></i> Create New Section
                </a>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Stats -->
            <?php
            $total_sections = count($sections);
            $total_students_in_sections = 0;
            $year_levels = [];
            
            foreach ($sections as $section) {
                $students_in_section = $sectionModel->getSectionStudents($section['section_id']);
                $total_students_in_sections += count($students_in_section);
                $year_levels[$section['year_level']] = true;
            }
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_sections; ?></h3>
                        <p>Total Sections</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #10b981;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_students_in_sections; ?></h3>
                        <p>Students in Sections</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3cd; color: #f59e0b;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($year_levels); ?></h3>
                        <p>Year Levels</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #cffafe; color: #06b6d4;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo round($total_students_in_sections / max($total_sections, 1)); ?></h3>
                        <p>Avg Students/Section</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search sections...">
                    <button onclick="searchSections()" style="padding: 12px 24px; background: #4f46e5; color: white; border: none; border-radius: 12px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="filter-select" id="yearFilter" onchange="filterByYear()">
                    <option value="all">All Grade Levels</option>
                    <option value="0">Kindergarten</option>
                    <option value="1">Grade 1</option>
                    <option value="2">Grade 2</option>
                    <option value="3">Grade 3</option>
                    <option value="4">Grade 4</option>
                    <option value="5">Grade 5</option>
                    <option value="6">Grade 6</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
                <select class="filter-select" id="academicYearFilter" onchange="filterByAcademicYear()">
                    <option value="all">All Academic Years</option>
                    <option value="2024-2025">2024-2025</option>
                    <option value="2025-2026">2025-2026</option>
                </select>
            </div>


            <!-- View Options -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px; background: #f1f5f9; padding: 5px; border-radius: 40px;">
        <button id="gridViewBtn" onclick="setView('grid')" 
                style="padding: 8px 16px; border: none; background: transparent; border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 500; transition: all 0.2s;">
            <i class="fas fa-th-large"></i> Grid
        </button>
        <button id="compactViewBtn" onclick="setView('compact')" 
                style="padding: 8px 16px; border: none; background: transparent; border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 500; transition: all 0.2s;">
            <i class="fas fa-th"></i> Compact
        </button>
        <button id="listViewBtn" onclick="setView('list')" 
                style="padding: 8px 16px; border: none; background: transparent; border-radius: 30px; cursor: pointer; display: flex; align-items: center; gap: 5px; font-weight: 500; transition: all 0.2s;">
            <i class="fas fa-list"></i> List
        </button>
    </div>
    <span style="color: #64748b; font-size: 14px;">
        <i class="fas fa-eye"></i> <?php echo count($sections); ?> sections
    </span>
</div>

<!-- Sections by School Level -->
<?php if (empty($sections)): ?>
    <div class="empty-state">
        <i class="fas fa-layer-group"></i>
        <h3>No Sections Created</h3>
        <p>Create your first section to organize students into groups.</p>
        <a href="create-section.php" class="btn-create">
            <i class="fas fa-plus"></i> Create Section
        </a>
    </div>
<?php else: ?>
    <?php
    // Define school levels and their year ranges
    $school_levels = [
        'Kindergarten' => [
            'years' => [0],
            'icon' => 'fas fa-child',
            'color' => '#f97316',
            'bg' => '#fff7ed',
            'display' => 'Kindergarten'
        ],
        'Elementary' => [
            'years' => [1, 2, 3, 4, 5, 6],
            'icon' => 'fas fa-pencil-alt',
            'color' => '#10b981',
            'bg' => '#d1fae5',
            'display' => 'Elementary'
        ],
        'Junior High School' => [
            'years' => [7, 8, 9, 10],
            'icon' => 'fas fa-book-open',
            'color' => '#3b82f6',
            'bg' => '#dbeafe',
            'display' => 'Junior High'
        ],
        'Senior High School' => [
            'years' => [11, 12],
            'icon' => 'fas fa-graduation-cap',
            'color' => '#8b5cf6',
            'bg' => '#ede9fe',
            'display' => 'Senior High'
        ]
    ];

    // Organize sections by school level
    $sections_by_level = [
        'Kindergarten' => [],
        'Elementary' => [],
        'Junior High School' => [],
        'Senior High School' => []
    ];
    
    foreach ($sections as $section) {
        $year = $section['year_level'];
        
        if ($year == 0) {
            $sections_by_level['Kindergarten'][] = $section;
        } elseif ($year >= 1 && $year <= 6) {
            $sections_by_level['Elementary'][] = $section;
        } elseif ($year >= 7 && $year <= 10) {
            $sections_by_level['Junior High School'][] = $section;
        } elseif ($year >= 11 && $year <= 12) {
            $sections_by_level['Senior High School'][] = $section;
        }
    }
    
    // Level statistics
    $total_kinder = count($sections_by_level['Kindergarten']);
    $total_elem = count($sections_by_level['Elementary']);
    $total_jhs = count($sections_by_level['Junior High School']);
    $total_shs = count($sections_by_level['Senior High School']);
    ?>
    
    <!-- Level Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div style="background: <?php echo $school_levels['Kindergarten']['bg']; ?>; border-radius: 20px; padding: 20px; border-left: 5px solid <?php echo $school_levels['Kindergarten']['color']; ?>;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="<?php echo $school_levels['Kindergarten']['icon']; ?>" style="font-size: 30px; color: <?php echo $school_levels['Kindergarten']['color']; ?>;"></i>
                <div>
                    <div style="font-size: 24px; font-weight: 700; color: #1e293b;"><?php echo $total_kinder; ?></div>
                    <div style="color: #64748b;">Kindergarten</div>
                </div>
            </div>
        </div>
        
        <div style="background: <?php echo $school_levels['Elementary']['bg']; ?>; border-radius: 20px; padding: 20px; border-left: 5px solid <?php echo $school_levels['Elementary']['color']; ?>;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="<?php echo $school_levels['Elementary']['icon']; ?>" style="font-size: 30px; color: <?php echo $school_levels['Elementary']['color']; ?>;"></i>
                <div>
                    <div style="font-size: 24px; font-weight: 700; color: #1e293b;"><?php echo $total_elem; ?></div>
                    <div style="color: #64748b;">Elementary</div>
                </div>
            </div>
        </div>
        
        <div style="background: <?php echo $school_levels['Junior High School']['bg']; ?>; border-radius: 20px; padding: 20px; border-left: 5px solid <?php echo $school_levels['Junior High School']['color']; ?>;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="<?php echo $school_levels['Junior High School']['icon']; ?>" style="font-size: 30px; color: <?php echo $school_levels['Junior High School']['color']; ?>;"></i>
                <div>
                    <div style="font-size: 24px; font-weight: 700; color: #1e293b;"><?php echo $total_jhs; ?></div>
                    <div style="color: #64748b;">Junior High</div>
                </div>
            </div>
        </div>
        
        <div style="background: <?php echo $school_levels['Senior High School']['bg']; ?>; border-radius: 20px; padding: 20px; border-left: 5px solid <?php echo $school_levels['Senior High School']['color']; ?>;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="<?php echo $school_levels['Senior High School']['icon']; ?>" style="font-size: 30px; color: <?php echo $school_levels['Senior High School']['color']; ?>;"></i>
                <div>
                    <div style="font-size: 24px; font-weight: 700; color: #1e293b;"><?php echo $total_shs; ?></div>
                    <div style="color: #64748b;">Senior High</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Level Navigation -->
    <div style="display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap;">
        <?php foreach ($school_levels as $level => $data): ?>
            <?php if (!empty($sections_by_level[$level])): ?>
                <a href="#level-<?php echo str_replace(' ', '-', strtolower($level)); ?>" 
                   style="padding: 12px 24px; background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>; border-radius: 40px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="<?php echo $data['icon']; ?>"></i>
                    <?php echo $data['display']; ?> (<?php echo count($sections_by_level[$level]); ?>)
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- GRID VIEW - Organized by level -->
    <div id="sectionsGrid" class="sections-grid-container" style="display: block;">
        <?php foreach ($school_levels as $level => $data): ?>
            <?php if (!empty($sections_by_level[$level])): ?>
                <!-- Level Divider -->
                <div id="level-<?php echo str_replace(' ', '-', strtolower($level)); ?>" style="margin-bottom: 30px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                        <div style="width: 40px; height: 40px; background: <?php echo $data['bg']; ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="<?php echo $data['icon']; ?>" style="font-size: 20px; color: <?php echo $data['color']; ?>;"></i>
                        </div>
                        <h3 style="font-size: 1.5rem; color: #1e293b; margin: 0;"><?php echo $data['display']; ?></h3>
                        <div style="flex: 1; height: 2px; background: linear-gradient(90deg, <?php echo $data['color']; ?>, transparent);"></div>
                        <span style="background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>; padding: 5px 15px; border-radius: 30px; font-weight: 600; font-size: 14px;">
                            <?php echo count($sections_by_level[$level]); ?> sections
                        </span>
                    </div>
                    
                    <!-- Sections Grid for this Level -->
                    <div class="sections-grid">
                        <?php foreach ($sections_by_level[$level] as $section): 
                            $students_in_section = $sectionModel->getSectionStudents($section['section_id']);
                            $student_count = count($students_in_section);
                        ?>
                            <div class="section-card" 
                                 data-name="<?php echo strtolower($section['section_name']); ?>"
                                 data-year="<?php echo $section['year_level']; ?>"
                                 data-academic="<?php echo $section['academic_year']; ?>"
                                 style="border-top: 4px solid <?php echo $data['color']; ?>;">
                                <div class="section-header">
                                    <h3 class="section-name"><?php echo htmlspecialchars($section['section_name']); ?></h3>
                                    <span class="section-year" style="background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>;">
                                        <?php 
                                        if ($section['year_level'] == 0) {
                                            echo 'Kinder';
                                        } else {
                                            echo 'Grade ' . $section['year_level'];
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="section-meta">
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo $section['academic_year']; ?></span>
                                    <span><i class="fas fa-users"></i> <?php echo $student_count; ?> Students</span>
                                </div>
                                
                                <div class="students-preview">
                                    <div class="students-title">
                                        <span>Students in this section</span>
                                        <span style="color: <?php echo $data['color']; ?>;"><?php echo $student_count; ?> total</span>
                                    </div>
                                    <div class="student-tags">
                                        <?php if (empty($students_in_section)): ?>
                                            <span style="color: #94a3b8; font-size: 12px;">No students assigned</span>
                                        <?php else: ?>
                                            <?php foreach (array_slice($students_in_section, 0, 5) as $student): ?>
                                                <span class="student-tag">
                                                    <i class="fas fa-user-graduate"></i>
                                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if ($student_count > 5): ?>
                                                <span class="student-tag">+<?php echo $student_count - 5; ?> more</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-actions">
                                    <a href="view-section.php?id=<?php echo $section['section_id']; ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="/mywebsite10/controllers/SectionController.php?action=edit&id=<?php echo $section['section_id']; ?>" class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="section-schedule.php?id=<?php echo $section['section_id']; ?>" class="action-btn btn-schedule">
                                        <i class="fas fa-calendar-alt"></i> Schedule
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

<!-- COMPACT VIEW - Same layout as grid but slightly smaller cards -->
<div id="compactView" class="compact-view-container" style="display: none;">
    <?php foreach ($school_levels as $level => $data): ?>
        <?php if (!empty($sections_by_level[$level])): ?>
            <!-- Level Divider -->
            <div style="margin-bottom: 30px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <div style="width: 40px; height: 40px; background: <?php echo $data['bg']; ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="<?php echo $data['icon']; ?>" style="font-size: 20px; color: <?php echo $data['color']; ?>;"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; color: #1e293b; margin: 0;"><?php echo $data['display']; ?></h3>
                    <div style="flex: 1; height: 2px; background: linear-gradient(90deg, <?php echo $data['color']; ?>, transparent);"></div>
                    <span style="background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>; padding: 5px 15px; border-radius: 30px; font-weight: 600; font-size: 14px;">
                        <?php echo count($sections_by_level[$level]); ?> sections
                    </span>
                </div>
                
                <!-- Compact Cards Grid - Same layout as grid but slightly smaller -->
                <div class="sections-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($sections_by_level[$level] as $section): 
                        $students_in_section = $sectionModel->getSectionStudents($section['section_id']);
                        $student_count = count($students_in_section);
                    ?>
                        <div class="section-card" 
                             data-name="<?php echo strtolower($section['section_name']); ?>"
                             data-year="<?php echo $section['year_level']; ?>"
                             data-academic="<?php echo $section['academic_year']; ?>"
                             style="border-top: 4px solid <?php echo $data['color']; ?>; padding: 20px;">
                            <div class="section-header">
                                <h3 class="section-name" style="font-size: 1.2rem;"><?php echo htmlspecialchars($section['section_name']); ?></h3>
                                <span class="section-year" style="background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>; font-size: 11px; padding: 3px 10px;">
                                    <?php 
                                    if ($section['year_level'] == 0) {
                                        echo 'Kinder';
                                    } else {
                                        echo 'Grade ' . $section['year_level'];
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="section-meta" style="font-size: 12px; gap: 15px; margin-bottom: 15px;">
                                <span><i class="fas fa-calendar-alt"></i> <?php echo $section['academic_year']; ?></span>
                                <span><i class="fas fa-users"></i> <?php echo $student_count; ?> Students</span>
                            </div>
                            
                            <div class="students-preview" style="padding: 12px; margin-bottom: 15px;">
                                <div class="students-title" style="font-size: 12px;">
                                    <span>Students</span>
                                    <span style="color: <?php echo $data['color']; ?>;"><?php echo $student_count; ?> total</span>
                                </div>
                                <div class="student-tags">
                                    <?php if (empty($students_in_section)): ?>
                                        <span style="color: #94a3b8; font-size: 11px;">No students assigned</span>
                                    <?php else: ?>
                                        <?php foreach (array_slice($students_in_section, 0, 3) as $student): ?>
                                            <span class="student-tag" style="padding: 2px 8px; font-size: 10px;">
                                                <i class="fas fa-user-graduate"></i>
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if ($student_count > 3): ?>
                                            <span class="student-tag" style="padding: 2px 8px; font-size: 10px;">+<?php echo $student_count - 3; ?> more</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-actions" style="gap: 8px; margin-top: 15px;">
                                <a href="view-section.php?id=<?php echo $section['section_id']; ?>" class="action-btn btn-view" style="padding: 8px; font-size: 12px;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/mywebsite10/controllers/SectionController.php?action=edit&id=<?php echo $section['section_id']; ?>" class="action-btn btn-edit" style="padding: 8px; font-size: 12px;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="section-schedule.php?id=<?php echo $section['section_id']; ?>" class="action-btn btn-schedule" style="padding: 8px; font-size: 12px;">
                                    <i class="fas fa-calendar-alt"></i> Schedule
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>


    <!-- LIST VIEW - Organized by level -->
    <div id="sectionsListView" class="list-view-container" style="display: none;">
    <?php 

    $level_index = 0;
    foreach ($school_levels as $level => $data): 
        $level_index++;
        if (!empty($sections_by_level[$level])): 
    ?>
            <!-- Separate section for each school level -->
            <div class="list-level-section" style="--level-index: <?php echo $level_index; ?>; margin-bottom: 30px;">
                <!-- Level Header -->
                <div class="list-level-header" style="background: <?php echo $data['bg']; ?>;">
                    <div class="list-level-icon" style="background: <?php echo $data['color']; ?>20; color: <?php echo $data['color']; ?>;">
                        <i class="<?php echo $data['icon']; ?>"></i>
                    </div>
                    <div class="list-level-title" style="color: <?php echo $data['color']; ?>;">
                        <?php echo $data['display']; ?>
                    </div>
                    <div class="list-level-count" style="background: <?php echo $data['color']; ?>; color: white;">
                        <?php echo count($sections_by_level[$level]); ?> Section<?php echo count($sections_by_level[$level]) > 1 ? 's' : ''; ?>
                    </div>
                </div>
                
                <!-- List Items for this Level -->
                <?php 
                $item_index = 0;
                foreach ($sections_by_level[$level] as $section): 
                    $students_in_section = $sectionModel->getSectionStudents($section['section_id']);
                    $student_count = count($students_in_section);
                    $item_index++;
                    
                    // Generate avatar letters
                    $name_parts = explode(' ', $section['section_name']);
                    $avatar = '';
                    foreach ($name_parts as $part) {
                        $avatar .= strtoupper(substr($part, 0, 1));
                    }
                    $avatar = substr($avatar, 0, 2);
                ?>
                    <div class="list-item-enhanced" style="--item-index: <?php echo $item_index; ?>;">
                        <div class="list-item-avatar" style="background: linear-gradient(135deg, <?php echo $data['color']; ?>, <?php echo $data['color']; ?>dd);">
                            <?php echo $avatar; ?>
                        </div>
                        
                        <div class="list-item-details">
                            <div class="list-item-name-section">
                                <div class="list-item-name"><?php echo htmlspecialchars($section['section_name']); ?></div>
                                <div class="list-item-sub">
                                    <i class="fas fa-calendar-alt" style="color: <?php echo $data['color']; ?>;"></i>
                                    <?php echo $section['academic_year']; ?>
                                </div>
                            </div>
                            
                            <div class="list-item-badge" style="background: <?php echo $data['bg']; ?>; color: <?php echo $data['color']; ?>;">
                                <i class="<?php echo $data['icon']; ?>"></i>
                                <?php 
                                if ($section['year_level'] == 0) {
                                    echo 'Kinder';
                                } else {
                                    echo 'Grade ' . $section['year_level'];
                                }
                                ?>
                            </div>
                            
                            <div class="list-item-stats">
                                <div class="list-item-stat">
                                    <i class="fas fa-users" style="color: <?php echo $data['color']; ?>;"></i>
                                    <?php echo $student_count; ?> Student<?php echo $student_count != 1 ? 's' : ''; ?>
                                </div>
                                <div class="list-item-stat">
                                    <i class="fas fa-clock" style="color: <?php echo $data['color']; ?>;"></i>
                                    0 Classes
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-item-actions-enhanced">
                            <a href="view-section.php?id=<?php echo $section['section_id']; ?>" class="list-action-btn-enhanced view-btn" title="View Section Details">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/mywebsite10/controllers/SectionController.php?action=edit&id=<?php echo $section['section_id']; ?>" class="list-action-btn-enhanced edit-btn" title="Edit Section">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="section-schedule.php?id=<?php echo $section['section_id']; ?>" class="list-action-btn-enhanced schedule-btn" title="Manage Class Schedule">
                                <i class="fas fa-calendar-alt"></i> Schedule
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Optional: Show empty state for levels with no sections -->
            <!-- Uncomment if you want to show empty levels
            <div class="list-level-section" style="opacity: 0.6; margin-bottom: 30px;">
                <div class="list-level-header" style="background: #f1f5f9;">
                    <div class="list-level-icon" style="background: #e2e8f0; color: #94a3b8;">
                        <i class="<?php echo $data['icon']; ?>"></i>
                    </div>
                    <div class="list-level-title" style="color: #64748b;">
                        <?php echo $data['display']; ?>
                    </div>
                    <div class="list-level-count" style="background: #cbd5e1; color: #475569;">
                        0 Sections
                    </div>
                </div>
                <div class="level-empty">
                    <i class="<?php echo $data['icon']; ?>" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
                    <p>No sections in <?php echo $data['display']; ?> yet</p>
                </div>
            </div>
            -->
        <?php endif; ?>
    <?php endforeach; ?>
</div>

    
    <!-- Summary by Level -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 40px; padding: 30px; background: white; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
        <?php foreach ($school_levels as $level => $data): ?>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: <?php echo $data['bg']; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                    <i class="<?php echo $data['icon']; ?>" style="font-size: 24px; color: <?php echo $data['color']; ?>;"></i>
                </div>
                <div style="font-size: 28px; font-weight: 700; color: <?php echo $data['color']; ?>;">
                    <?php echo count($sections_by_level[$level]); ?>
                </div>
                <div style="font-size: 14px; color: #64748b;"><?php echo $data['display']; ?> Sections</div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>



<script>
        function searchSections() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const yearFilter = document.getElementById('yearFilter').value;
            const academicFilter = document.getElementById('academicYearFilter').value;
            const cards = document.querySelectorAll('.section-card');
            
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const year = card.getAttribute('data-year');
                const academic = card.getAttribute('data-academic');
                
                const matchesSearch = name.includes(searchTerm);
                const matchesYear = yearFilter === 'all' || year === yearFilter;
                const matchesAcademic = academicFilter === 'all' || academic === academicFilter;
                
                card.style.display = matchesSearch && matchesYear && matchesAcademic ? 'block' : 'none';
            });
        }

        function filterByYear() {
            searchSections();
        }

        function filterByAcademicYear() {
            searchSections();
        }

        document.getElementById('searchInput').addEventListener('keyup', searchSections);

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

// View options
let currentView = localStorage.getItem('sectionsView') || 'grid';

function setView(view) {
    currentView = view;
    localStorage.setItem('sectionsView', view);
    
    const grid = document.getElementById('sectionsGrid');
    const compact = document.getElementById('compactView');
    const list = document.getElementById('sectionsListView');
    const gridBtn = document.getElementById('gridViewBtn');
    const compactBtn = document.getElementById('compactViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    // Hide all
    grid.style.display = 'none';
    if (compact) compact.style.display = 'none';
    if (list) list.style.display = 'none';
    
    // Remove active class from all buttons
    gridBtn.classList.remove('active');
    if (compactBtn) compactBtn.classList.remove('active');
    if (listBtn) listBtn.classList.remove('active');
    
    // Show selected view
    if (view === 'grid') {
        grid.style.display = 'block';
        gridBtn.classList.add('active');
    } else if (view === 'compact') {
        compact.style.display = 'block';
        compactBtn.classList.add('active');
    } else if (view === 'list') {
        list.style.display = 'block';
        listBtn.classList.add('active');
    }
}

// Initialize view on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('sectionsView') || 'grid';
    setView(savedView);
});


    </script>
</body>
</html>