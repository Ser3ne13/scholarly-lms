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

// Check if this teacher owns this lesson
if ($subject['teacher_id'] != $_SESSION['user_id']) {
    header('Location: subjects.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson - <?php echo htmlspecialchars($lesson['title']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        .lesson-form {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .form-section {
            background: white;
            border-radius: var(--border-radius-xl);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--dark-100);
            color: var(--dark-700);
        }
        
        .current-file {
            background: var(--dark-50);
            padding: 15px;
            border-radius: var(--border-radius-lg);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .file-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-600);
            font-size: 20px;
        }
        
        .file-info {
            flex: 1;
        }
        
        .file-name {
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .file-meta {
            font-size: 12px;
            color: var(--dark-500);
        }
        
        .remove-file {
            color: var(--danger-500);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s;
        }
        
        .remove-file:hover {
            background: var(--danger-100);
        }
        
        .file-upload-area {
            border: 2px dashed var(--primary-300);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            text-align: center;
            background: var(--primary-50);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary-600);
            background: var(--primary-100);
        }
        
        .file-upload-area i {
            font-size: 40px;
            color: var(--primary-500);
            margin-bottom: 10px;
        }
        
        .video-preview {
            margin-top: 15px;
            padding: 15px;
            background: var(--dark-50);
            border-radius: var(--border-radius-lg);
        }
        
        .video-preview iframe {
            width: 100%;
            height: 315px;
            border-radius: var(--border-radius-md);
        }
        
        .btn-save {
            padding: 15px 40px;
            font-size: 16px;
        }
        
        .btn-danger {
            background: var(--danger-500);
            color: white;
        }
        
        .btn-danger:hover {
            background: var(--danger-600);
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
        <div class="page-header">
            <h1>Edit Lesson</h1>
            <div style="display: flex; gap: 10px;">
                <a href="view-lesson.php?id=<?php echo $lesson_id; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> View Lesson
                </a>
                <a href="view-subject.php?id=<?php echo $lesson['subject_id']; ?>" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Subject
                </a>
            </div>
        </div>

        <div class="lesson-form">
            <form action="/mywebsite10/controllers/LessonController.php?action=update" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $lesson['subject_id']; ?>">
                
                <!-- Basic Info Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-info-circle" style="margin-right: 10px;"></i>
                        Basic Information
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Lesson Title <span style="color: var(--danger-500);">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Lesson Content</label>
                        <textarea id="content" name="content" class="form-control"><?php echo htmlspecialchars($lesson['content']); ?></textarea>
                    </div>
                </div>
                
                <!-- Video Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-video" style="margin-right: 10px;"></i>
                        Video
                    </div>
                    
                    <div class="form-group">
                        <label for="video_url">YouTube Video URL</label>
                        <input type="url" id="video_url" name="video_url" class="form-control" 
                               value="<?php echo htmlspecialchars($lesson['video_url'] ?? ''); ?>" 
                               placeholder="https://www.youtube.com/watch?v=..." onchange="previewVideo()">
                    </div>
                    
                    <?php if (!empty($lesson['video_url'])): ?>
                        <div id="videoPreview" class="video-preview">
                            <?php
                            $video_url = $lesson['video_url'];
                            if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
                                $video_id = substr($video_url, strpos($video_url, 'v=') + 2);
                                $embed_url = "https://www.youtube.com/embed/$video_id";
                            } elseif (strpos($video_url, 'youtu.be/') !== false) {
                                $video_id = substr($video_url, strpos($video_url, 'youtu.be/') + 9);
                                $embed_url = "https://www.youtube.com/embed/$video_id";
                            } else {
                                $embed_url = $video_url;
                            }
                            ?>
                            <iframe src="<?php echo $embed_url; ?>" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <div id="videoPreview" class="video-preview" style="display: none;">
                            <iframe id="videoIframe" src="" frameborder="0" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- File Upload Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-paperclip" style="margin-right: 10px;"></i>
                        Attachments
                    </div>
                    
                    <?php if (!empty($lesson['file_path'])): ?>
                        <div class="current-file" id="currentFile">
                            <div class="file-icon">
                                <?php
                                $ext = pathinfo($lesson['file_path'], PATHINFO_EXTENSION);
                                $icon = 'fa-file';
                                if ($ext == 'pdf') $icon = 'fa-file-pdf';
                                elseif ($ext == 'doc' || $ext == 'docx') $icon = 'fa-file-word';
                                elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png') $icon = 'fa-file-image';
                                elseif ($ext == 'mp4') $icon = 'fa-file-video';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="file-info">
                                <div class="file-name"><?php echo basename($lesson['file_path']); ?></div>
                                <div class="file-meta">Current file</div>
                            </div>
                            <div class="remove-file" onclick="removeCurrentFile()" title="Remove this file">
                                <i class="fas fa-times"></i>
                            </div>
                            <input type="hidden" name="remove_file" id="removeFile" value="0">
                        </div>
                    <?php endif; ?>
                    
                    <div class="file-upload-area" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h4>Click to upload new file</h4>
                        <p>or drag and drop (replaces existing file)</p>
                        <input type="file" id="fileInput" name="lesson_file" style="display: none;">
                        <div class="file-info">
                            <i class="fas fa-file-pdf"></i> PDF |
                            <i class="fas fa-file-word"></i> DOC/DOCX |
                            <i class="fas fa-file-image"></i> JPG/PNG |
                            <i class="fas fa-file-video"></i> MP4
                        </div>
                        <div class="file-info">Maximum file size: 10MB</div>
                    </div>
                    
                    <div id="selectedFile" style="margin-top: 15px; display: none;">
                        <div style="display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--dark-50); border-radius: var(--border-radius-md);">
                            <i class="fas fa-file"></i>
                            <span id="fileName"></span>
                            <button type="button" onclick="removeNewFile()" style="margin-left: auto; background: none; border: none; color: var(--danger-500); cursor: pointer;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div style="display: flex; gap: 15px; justify-content: flex-end;">
                    <a href="view-lesson.php?id=<?php echo $lesson_id; ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-save">
                        <i class="fas fa-save"></i> Update Lesson
                    </button>
                </div>
            </form>
            
            <!-- Delete Form -->
            <form action="/mywebsite10/controllers/LessonController.php?action=delete" method="POST" style="margin-top: 20px;" onsubmit="return confirm('Are you sure you want to delete this lesson? This cannot be undone.')">
                <input type="hidden" name="lesson_id" value="<?php echo $lesson_id; ?>">
                <input type="hidden" name="subject_id" value="<?php echo $lesson['subject_id']; ?>">
                <button type="submit" class="btn btn-danger" style="width: 100%;">
                    <i class="fas fa-trash"></i> Delete Lesson
                </button>
            </form>
        </div>
    </main>

    <script>
        // Initialize CKEditor
        CKEDITOR.replace('content', {
            height: 400,
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'styles', items: ['Format', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                { name: 'tools', items: ['Maximize'] }
            ]
        });
        
        // File upload handling
        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (this.files.length > 0) {
                document.getElementById('selectedFile').style.display = 'block';
                document.getElementById('fileName').textContent = this.files[0].name;
            }
        });
        
        function removeNewFile() {
            document.getElementById('fileInput').value = '';
            document.getElementById('selectedFile').style.display = 'none';
        }
        
        function removeCurrentFile() {
            if (confirm('Remove the current file?')) {
                document.getElementById('currentFile').style.display = 'none';
                document.getElementById('removeFile').value = '1';
            }
        }
        
        // Video preview
        function previewVideo() {
            const url = document.getElementById('video_url').value;
            const preview = document.getElementById('videoPreview');
            const iframe = document.getElementById('videoIframe');
            
            if (url) {
                let embedUrl = url;
                if (url.includes('youtube.com/watch?v=')) {
                    const videoId = url.split('v=')[1].split('&')[0];
                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                } else if (url.includes('youtu.be/')) {
                    const videoId = url.split('youtu.be/')[1];
                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                }
                
                iframe.src = embedUrl;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                iframe.src = '';
            }
        }
    </script>
</body>
</html>