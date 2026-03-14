<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

require_once '../../models/Lesson.php';
require_once '../../models/Subject.php';
require_once '../../models/LessonProgress.php';

$lesson_id = $_GET['id'] ?? 0;

$lessonModel = new Lesson();
$subjectModel = new Subject();
$progressModel = new LessonProgress();

$lesson = $lessonModel->getLesson($lesson_id);

if (!$lesson) {
    header('Location: subjects.php');
    exit();
}

$subject = $subjectModel->getSubject($lesson['subject_id']);
$is_completed = $progressModel->isCompleted($_SESSION['user_id'], $lesson_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?> - Scholarly</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .lesson-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Header */
        .lesson-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            position: relative;
        }
        
        .lesson-header h1 {
            color: white;
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        
        .lesson-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }
        
        .lesson-meta i {
            margin-right: 5px;
            opacity: 0.8;
        }
        
        .completed-badge {
            position: absolute;
            top: 30px;
            right: 30px;
            background: #10b981;
            color: white;
            padding: 8px 20px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Video */
        .video-wrapper {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Content */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            line-height: 1.8;
        }
        
        /* Attachments */
        .attachments-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .attachment-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #eef2f6;
            margin-bottom: 10px;
        }
        
        .attachment-icon {
            width: 50px;
            height: 50px;
            background: #eef2ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 24px;
            margin-right: 15px;
        }
        
        .attachment-info {
            flex: 1;
        }
        
        .attachment-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .attachment-size {
            font-size: 12px;
            color: #64748b;
        }
        
        .btn-download {
            background: #4f46e5;
            color: white;
            padding: 8px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Complete Button */
        .complete-section {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn-complete {
            background: #10b981;
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-complete:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .btn-complete:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }
        
        .completed-message {
            background: #d1fae5;
            color: #065f46;
            padding: 20px;
            border-radius: 40px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Navigation */
        .lesson-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px;
        }
        
        .nav-btn {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-decoration: none;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #eef2f6;
            transition: all 0.3s;
        }
        
        .nav-btn:hover:not(.disabled) {
            border-color: #4f46e5;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
        }
        
        .nav-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4f46e5;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .lesson-header {
                padding: 25px;
            }
            
            .lesson-header h1 {
                font-size: 1.8rem;
            }
            
            .completed-badge {
                position: static;
                margin-bottom: 15px;
                display: inline-block;
            }
            
            .lesson-navigation {
                flex-direction: column;
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
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i> My Progress</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="lesson-container">
            <a href="view-subject.php?id=<?php echo $lesson['subject_id']; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($subject['subject_name']); ?>
            </a>

            <!-- Lesson Header -->
            <div class="lesson-header">
                <?php if ($is_completed): ?>
                    <div class="completed-badge">
                        <i class="fas fa-check-circle"></i> Completed
                    </div>
                <?php endif; ?>
                
                <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
                <div class="lesson-meta">
                    <span><i class="far fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($lesson['created_at'])); ?></span>
                    <span><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($subject['subject_name']); ?></span>
                </div>
            </div>

            <!-- Video -->
            <?php if (!empty($lesson['video_url'])): ?>
                <div class="video-wrapper">
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

            <!-- Content -->
            <div class="content-card">
                <?php if (!empty($lesson['content'])): ?>
                    <?php echo $lesson['content']; ?>
                <?php else: ?>
                    <p style="color: #64748b; text-align: center;">No content available for this lesson.</p>
                <?php endif; ?>
            </div>

            <!-- Attachments -->
            <?php if (!empty($lesson['file_path'])): ?>
                <div class="attachments-section">
                    <div class="section-title">
                        <i class="fas fa-paperclip" style="color: #4f46e5;"></i>
                        <span>Lesson Materials</span>
                    </div>
                    
                    <?php
                    $file_name = basename($lesson['file_path']);
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $file_size = file_exists(ROOT_PATH . $lesson['file_path']) ? filesize(ROOT_PATH . $lesson['file_path']) : 0;
                    $file_size_formatted = $file_size ? round($file_size / 1024, 2) . ' KB' : 'Unknown';
                    
                    $icon = 'fa-file';
                    if ($file_ext == 'pdf') $icon = 'fa-file-pdf';
                    elseif (in_array($file_ext, ['doc', 'docx'])) $icon = 'fa-file-word';
                    elseif (in_array($file_ext, ['jpg', 'jpeg', 'png'])) $icon = 'fa-file-image';
                    elseif ($file_ext == 'mp4') $icon = 'fa-file-video';
                    ?>
                    
                    <div class="attachment-item">
                        <div class="attachment-icon">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="attachment-info">
                            <div class="attachment-name"><?php echo $file_name; ?></div>
                            <div class="attachment-size"><?php echo $file_size_formatted; ?></div>
                        </div>
                        <a href="/mywebsite10/<?php echo $lesson['file_path']; ?>" class="btn-download" download>
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Complete Button -->
            <div class="complete-section">
                <?php if ($is_completed): ?>
                    <div class="completed-message">
                        <i class="fas fa-check-circle"></i> You've completed this lesson!
                    </div>
                <?php else: ?>
                    <form action="/mywebsite10/controllers/LessonProgressController.php" method="POST">
                        <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo $lesson['subject_id']; ?>">
                        <button type="submit" name="action" value="complete" class="btn-complete">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Navigation -->
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
                    <a href="view-lesson.php?id=<?php echo $prev_lesson['lesson_id']; ?>" class="nav-btn">
                        <i class="fas fa-arrow-left" style="color: #4f46e5;"></i>
                        <div>
                            <div style="font-size: 12px; color: #64748b;">Previous</div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($prev_lesson['title']); ?></div>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="nav-btn disabled">
                        <i class="fas fa-arrow-left" style="color: #cbd5e1;"></i>
                        <div>
                            <div style="font-size: 12px;">Previous</div>
                            <div style="font-weight: 600;">No previous lesson</div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($next_lesson): ?>
                    <a href="view-lesson.php?id=<?php echo $next_lesson['lesson_id']; ?>" class="nav-btn" style="text-align: right;">
                        <div style="flex: 1;">
                            <div style="font-size: 12px; color: #64748b;">Next</div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($next_lesson['title']); ?></div>
                        </div>
                        <i class="fas fa-arrow-right" style="color: #4f46e5;"></i>
                    </a>
                <?php else: ?>
                    <div class="nav-btn disabled" style="text-align: right;">
                        <div>
                            <div style="font-size: 12px;">Next</div>
                            <div style="font-weight: 600;">No next lesson</div>
                        </div>
                        <i class="fas fa-arrow-right" style="color: #cbd5e1;"></i>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>