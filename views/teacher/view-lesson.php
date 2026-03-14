<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Lesson.php';
require_once '../../models/Subject.php';

$lesson_id = $_GET['id'] ?? 0;

$lessonModel = new Lesson();
$subjectModel = new Subject();

$lesson = $lessonModel->getLesson($lesson_id);

if (!$lesson) {
    header('Location: subjects.php');
    exit();
}

$subject = $subjectModel->getSubject($lesson['subject_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        /* Lesson Viewer Specific Styles */
        .lesson-viewer {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: var(--primary-600);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            background: white;
            border-radius: 30px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }
        
        .breadcrumb a:hover {
            background: var(--primary-600);
            color: white;
            transform: translateX(-3px);
        }
        
        .breadcrumb span {
            color: var(--dark-400);
        }
        
        /* Hero Section */
        .lesson-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 50px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }
        
        .lesson-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        .lesson-hero::after {
            content: '📚';
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(-10deg);
        }
        
        .lesson-tag {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .lesson-hero h1 {
            color: white;
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
            max-width: 80%;
            font-family: 'Playfair Display', serif;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }
        
        .lesson-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 40px;
            backdrop-filter: blur(5px);
            font-size: 14px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .meta-item i {
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Video Container */
        .video-wrapper {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            position: relative;
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .video-label {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-600);
            z-index: 10;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(5px);
        }
        
        /* Content Card */
        .content-card {
            background: white;
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            position: relative;
        }
        
        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 30px;
            right: 30px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-500), var(--secondary-500));
            border-radius: 0 0 4px 4px;
        }
        
        .lesson-content {
            line-height: 1.9;
            color: var(--dark-700);
            font-size: 16px;
        }
        
        .lesson-content h1 { font-size: 2.2rem; margin: 40px 0 20px; color: var(--dark-900); }
        .lesson-content h2 { font-size: 1.8rem; margin: 35px 0 20px; color: var(--dark-800); }
        .lesson-content h3 { font-size: 1.4rem; margin: 30px 0 15px; color: var(--dark-800); }
        .lesson-content p { margin-bottom: 20px; }
        .lesson-content ul, .lesson-content ol { margin: 20px 0 20px 30px; }
        .lesson-content li { margin-bottom: 8px; }
        .lesson-content img { 
            max-width: 100%; 
            border-radius: 15px; 
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .lesson-content blockquote {
            border-left: 4px solid var(--primary-500);
            background: var(--primary-50);
            padding: 20px 30px;
            margin: 30px 0;
            border-radius: 0 15px 15px 0;
            font-style: italic;
            color: var(--dark-600);
        }
        
        /* Attachments Section */
        .attachments-section {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-800);
        }
        
        .section-title i {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary-500), var(--secondary-500));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .attachment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .attachment-card {
            background: var(--dark-50);
            border-radius: 20px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
            border: 1px solid var(--dark-100);
            position: relative;
            overflow: hidden;
        }
        
        .attachment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--primary-300);
        }
        
        .attachment-icon {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
        
        .attachment-icon.pdf { color: #dc2626; }
        .attachment-icon.doc { color: #2563eb; }
        .attachment-icon.image { color: #059669; }
        .attachment-icon.video { color: #7c3aed; }
        
        .attachment-details {
            flex: 1;
        }
        
        .attachment-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-800);
        }
        
        .attachment-meta {
            font-size: 12px;
            color: var(--dark-500);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .attachment-size {
            background: white;
            padding: 2px 8px;
            border-radius: 20px;
        }
        
        .download-btn {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-600);
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }
        
        .download-btn:hover {
            background: var(--primary-600);
            color: white;
            transform: scale(1.1);
        }
        
        /* Navigation */
        .lesson-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            gap: 20px;
        }
        
        .nav-btn {
            flex: 1;
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-decoration: none;
            color: var(--dark-700);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
            border: 1px solid var(--dark-100);
            position: relative;
            overflow: hidden;
        }
        
        .nav-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--primary-400);
        }
        
        .nav-btn.prev:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .nav-btn.next:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }
        
        .nav-icon {
            width: 50px;
            height: 50px;
            background: var(--dark-50);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s;
        }
        
        .nav-btn:hover .nav-icon {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .nav-content {
            flex: 1;
        }
        
        .nav-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .nav-title {
            font-weight: 700;
            font-size: 16px;
        }
        
        .nav-btn.disabled {
            opacity: 0.5;
            pointer-events: none;
            background: var(--dark-50);
        }
        
        /* Floating Action Buttons */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 100;
        }
        
        .fab {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-600);
            text-decoration: none;
            font-size: 24px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            position: relative;
        }
        
        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            background: var(--primary-600);
            color: white;
        }
        
        .fab.edit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .fab.delete {
            background: white;
            color: #dc2626;
        }
        
        .fab.delete:hover {
            background: #dc2626;
            color: white;
        }
        
        .tooltip {
            position: absolute;
            right: 70px;
            background: var(--dark-800);
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s;
        }
        
        .fab:hover .tooltip {
            opacity: 1;
            right: 80px;
        }
        
        /* Empty State */
        .empty-content {
            text-align: center;
            padding: 60px;
            background: var(--dark-50);
            border-radius: 30px;
        }
        
        .empty-content i {
            font-size: 80px;
            color: var(--dark-300);
            margin-bottom: 20px;
        }
        
        .empty-content h3 {
            color: var(--dark-600);
            margin-bottom: 10px;
        }
        
        .empty-content p {
            color: var(--dark-400);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .lesson-hero {
                padding: 30px;
            }
            
            .lesson-hero h1 {
                font-size: 2.2rem;
                max-width: 100%;
            }
            
            .content-card {
                padding: 30px;
            }
            
            .lesson-navigation {
                flex-direction: column;
            }
            
            .nav-btn {
                width: 100%;
            }
            
            .attachment-grid {
                grid-template-columns: 1fr;
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

    <main class="main-content">
        <div class="lesson-viewer">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="subjects.php"><i class="fas fa-book"></i> My Subjects</a>
                <i class="fas fa-chevron-right" style="color: var(--dark-300); font-size: 12px;"></i>
                <a href="view-subject.php?id=<?php echo $lesson['subject_id']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></a>
                <i class="fas fa-chevron-right" style="color: var(--dark-300); font-size: 12px;"></i>
                <span><?php echo htmlspecialchars($lesson['title']); ?></span>
            </div>
            
            <!-- Hero Section -->
            <div class="lesson-hero">
                <div class="lesson-tag">
                    <i class="far fa-file-alt"></i> LESSON <?php echo $lesson['order_number']; ?>
                </div>
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                
                <div class="lesson-meta">
                    <div class="meta-item">
                        <i class="far fa-calendar-alt"></i>
                        <?php echo date('F j, Y', strtotime($lesson['created_at'])); ?>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        Last updated <?php echo date('F j, Y', strtotime($lesson['created_at'])); ?>
                    </div>
                    <?php if (!empty($lesson['file_path'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-paperclip"></i>
                            1 attachment
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Video Section -->
            <?php if (!empty($lesson['video_url'])): ?>
                <div class="video-wrapper">
                    <div class="video-label">
                        <i class="fas fa-play"></i> Lesson Video
                    </div>
                    <div class="video-container">
                        <?php
                        $video_url = $lesson['video_url'];
                        if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
                            $video_id = substr($video_url, strpos($video_url, 'v=') + 2);
                            $video_id = substr($video_id, 0, strpos($video_id, '&') ?: strlen($video_id));
                            $embed_url = "https://www.youtube.com/embed/$video_id";
                        } elseif (strpos($video_url, 'youtu.be/') !== false) {
                            $video_id = substr($video_url, strpos($video_url, 'youtu.be/') + 9);
                            $embed_url = "https://www.youtube.com/embed/$video_id";
                        } else {
                            $embed_url = $video_url;
                        }
                        ?>
                        <iframe src="<?php echo $embed_url; ?>" allowfullscreen></iframe>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Content Section -->
            <div class="content-card">
                <div class="lesson-content">
                    <?php
                    if (!empty($lesson['content'])) {
                        echo $lesson['content'];
                    } else {
                        echo '<div class="empty-content">
                                <i class="fas fa-file-alt"></i>
                                <h3>No content yet</h3>
                                <p>This lesson doesn\'t have any written content.</p>
                              </div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Attachments Section -->
            <?php if (!empty($lesson['file_path'])): ?>
                <div class="attachments-section">
                    <div class="section-title">
                        <i class="fas fa-paperclip"></i>
                        Lesson Materials
                    </div>
                    
                    <div class="attachment-grid">
                        <?php
                        $file_name = basename($lesson['file_path']);
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $file_size = file_exists(ROOT_PATH . $lesson['file_path']) ? filesize(ROOT_PATH . $lesson['file_path']) : 0;
                        $file_size_formatted = $file_size ? round($file_size / 1024, 2) . ' KB' : 'Unknown size';
                        
                        $icon_class = 'fa-file';
                        $icon_color = '';
                        if ($file_ext == 'pdf') { 
                            $icon_class = 'fa-file-pdf'; 
                            $icon_color = 'pdf';
                        } elseif ($file_ext == 'doc' || $file_ext == 'docx') { 
                            $icon_class = 'fa-file-word'; 
                            $icon_color = 'doc';
                        } elseif ($file_ext == 'jpg' || $file_ext == 'jpeg' || $file_ext == 'png' || $file_ext == 'gif') { 
                            $icon_class = 'fa-file-image'; 
                            $icon_color = 'image';
                        } elseif ($file_ext == 'mp4' || $file_ext == 'mov' || $file_ext == 'avi') { 
                            $icon_class = 'fa-file-video'; 
                            $icon_color = 'video';
                        } elseif ($file_ext == 'ppt' || $file_ext == 'pptx') { 
                            $icon_class = 'fa-file-powerpoint'; 
                        } elseif ($file_ext == 'xls' || $file_ext == 'xlsx') { 
                            $icon_class = 'fa-file-excel'; 
                        }
                        ?>
                        
                        <div class="attachment-card">
                            <div class="attachment-icon <?php echo $icon_color; ?>">
                                <i class="fas <?php echo $icon_class; ?>"></i>
                            </div>
                            <div class="attachment-details">
                                <div class="attachment-name"><?php echo $file_name; ?></div>
                                <div class="attachment-meta">
                                    <span><?php echo strtoupper($file_ext); ?> File</span>
                                    <span class="attachment-size"><?php echo $file_size_formatted; ?></span>
                                </div>
                            </div>
                            <a href="/mywebsite10/<?php echo $lesson['file_path']; ?>" class="download-btn" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Lesson Navigation -->
            <?php
            $all_lessons = $lessonModel->getSubjectLessons($lesson['subject_id']);
            $current_index = -1;
            foreach ($all_lessons as $index => $l) {
                if ($l['lesson_id'] == $lesson_id) {
                    $current_index = $index;
                    break;
                }
            }
            
            $prev_lesson = ($current_index > 0) ? $all_lessons[$current_index - 1] : null;
            $next_lesson = ($current_index < count($all_lessons) - 1) ? $all_lessons[$current_index + 1] : null;
            ?>
            
            <div class="lesson-navigation">
                <?php if ($prev_lesson): ?>
                    <a href="view-lesson.php?id=<?php echo $prev_lesson['lesson_id']; ?>" class="nav-btn prev">
                        <div class="nav-icon">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-label">Previous Lesson</div>
                            <div class="nav-title"><?php echo htmlspecialchars($prev_lesson['title']); ?></div>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="nav-btn disabled">
                        <div class="nav-icon">
                            <i class="fas fa-arrow-left"></i>
                        </div>
                        <div class="nav-content">
                            <div class="nav-label">Previous Lesson</div>
                            <div class="nav-title">No previous lesson</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($next_lesson): ?>
                    <a href="view-lesson.php?id=<?php echo $next_lesson['lesson_id']; ?>" class="nav-btn next">
                        <div class="nav-content" style="text-align: right;">
                            <div class="nav-label">Next Lesson</div>
                            <div class="nav-title"><?php echo htmlspecialchars($next_lesson['title']); ?></div>
                        </div>
                        <div class="nav-icon">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="nav-btn disabled">
                        <div class="nav-content" style="text-align: right;">
                            <div class="nav-label">Next Lesson</div>
                            <div class="nav-title">No next lesson</div>
                        </div>
                        <div class="nav-icon">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Floating Action Buttons -->
        <div class="fab-container">
            <a href="edit-lesson.php?id=<?php echo $lesson_id; ?>" class="fab edit">
                <i class="fas fa-pencil-alt"></i>
                <span class="tooltip">Edit Lesson</span>
            </a>
            
            <form action="/mywebsite10/controllers/LessonController.php?action=delete" method="POST" style="display: inline;" onsubmit="return confirmDelete(event)">
                <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $lesson['subject_id']; ?>">
                <button type="submit" class="fab delete" style="border: none;">
                    <i class="fas fa-trash"></i>
                    <span class="tooltip">Delete Lesson</span>
                </button>
            </form>
        </div>
    </main>

    <script>
    function confirmDelete(event) {
        event.preventDefault();
        
        if (confirm('Are you sure you want to delete this lesson? This action cannot be undone.')) {
            // If confirmed, submit the form
            event.target.submit();
        }
        
        return false;
    }
    </script>
</body>
</html>