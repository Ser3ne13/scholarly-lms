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
    <title>Create Lesson - <?php echo htmlspecialchars($subject['subject_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }
        
        .create-lesson-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Header Card */
        .header-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #eef2f6;
        }
        
        .header-left h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .subject-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .subject-badge i {
            font-size: 14px;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-back:hover {
            background: #e2e8f0;
            transform: translateX(-3px);
        }
        
        /* Form Card */
        .form-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid #eef2f6;
        }
        
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eef2f6;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .section-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .section-title h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .section-title p {
            color: #64748b;
            font-size: 14px;
            margin-top: 4px;
        }
        
        /* Form Fields */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #334155;
        }
        
        .form-group label i {
            color: #4f46e5;
            margin-right: 6px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.2s;
            background: #f8fafc;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .form-text {
            font-size: 13px;
            color: #64748b;
            margin-top: 6px;
        }
        
        /* File Upload Area */
        .upload-area {
            border: 3px dashed #cbd5e1;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            border-color: #4f46e5;
            background: #eef2ff;
        }
        
        .upload-area i {
            font-size: 48px;
            color: #4f46e5;
            margin-bottom: 15px;
        }
        
        .upload-area h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .upload-area p {
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .file-types {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 13px;
            color: #475569;
        }
        
        .file-types span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .file-types i {
            font-size: 14px;
            margin: 0;
            color: #4f46e5;
        }
        
        /* Selected File */
        .selected-file {
            margin-top: 20px;
            padding: 15px;
            background: #eef2ff;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #c7d2fe;
        }
        
        .file-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #4f46e5;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .file-size {
            font-size: 12px;
            color: #64748b;
        }
        
        .remove-file {
            width: 36px;
            height: 36px;
            background: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .remove-file:hover {
            background: #ef4444;
            color: white;
        }
        
        /* Video Preview */
        .video-preview {
            margin-top: 20px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .video-preview iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #eef2f6;
        }
        
        .btn {
            padding: 14px 32px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }
        
        /* Character Count */
        .char-count {
            text-align: right;
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-card {
                padding: 25px;
            }
            
            .header-card {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .video-preview iframe {
                height: 250px;
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
            <li><a href="#"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="#"><i class="fas fa-calendar"></i> Schedule</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Quizzes</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Assignments</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="create-lesson-container">
            <!-- Header Card -->
            <div class="header-card">
                <div class="header-left">
                    <h1><i class="fas fa-plus-circle" style="color: #4f46e5; margin-right: 10px;"></i>Create New Lesson</h1>
                    <div class="subject-badge">
                        <i class="fas fa-book-open"></i>
                        <?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)
                    </div>
                </div>
                <a href="view-subject.php?id=<?php echo $subject_id; ?>" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Subject
                </a>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form action="/mywebsite10/controllers/LessonController.php?action=create" method="POST" enctype="multipart/form-data" id="lessonForm">
                    <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                    
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <h2>Basic Information</h2>
                                <p>Tell students what this lesson is about</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Lesson Title <span style="color: #ef4444;">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   placeholder="e.g., Introduction to Variables, The Water Cycle, etc." 
                                   required maxlength="200">
                            <div class="char-count"><span id="titleCount">0</span>/200</div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Lesson Content</label>
                            <textarea id="content" name="content" class="form-control" rows="10"></textarea>
                            <div class="form-text">Write your lesson content using the rich text editor. You can add images, lists, and formatting.</div>
                        </div>
                    </div>
                    
                    <!-- Video Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div>
                                <h2>Video (Optional)</h2>
                                <p>Add a YouTube video to enhance your lesson</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fab fa-youtube"></i> YouTube Video URL</label>
                            <input type="url" id="video_url" name="video_url" class="form-control" 
                                   placeholder="https://www.youtube.com/watch?v=..." oninput="previewVideo()">
                            <div class="form-text">Paste any YouTube link - it will be automatically converted to an embedded video.</div>
                        </div>
                        
                        <div id="videoPreview" class="video-preview" style="display: none;">
                            <iframe id="videoIframe" src="" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                    
                    <!-- Attachments Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            <div>
                                <h2>Attachments (Optional)</h2>
                                <p>Upload worksheets, presentations, or additional materials</p>
                            </div>
                        </div>
                        
                        <div class="upload-area" onclick="document.getElementById('fileInput').click()" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h3>Click to upload files</h3>
                            <p>or drag and drop (Max: 10MB per file)</p>
                            <div class="file-types">
                                <span><i class="fas fa-file-pdf"></i> PDF</span>
                                <span><i class="fas fa-file-word"></i> DOC/DOCX</span>
                                <span><i class="fas fa-file-image"></i> JPG/PNG</span>
                                <span><i class="fas fa-file-video"></i> MP4</span>
                            </div>
                            <input type="file" id="fileInput" name="lesson_file" style="display: none;" onchange="handleFileSelect(this)">
                        </div>
                        
                        <div id="selectedFileContainer" style="display: none;">
                            <div class="selected-file">
                                <div class="file-icon" id="fileIcon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="file-details">
                                    <div class="file-name" id="selectedFileName"></div>
                                    <div class="file-size" id="selectedFileSize"></div>
                                </div>
                                <button type="button" class="remove-file" onclick="removeSelectedFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="view-subject.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-check"></i> Create Lesson
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Initialize CKEditor
        CKEDITOR.replace('content', {
            height: 400,
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'align', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
                { name: 'document', items: ['Source'] }
            ],
            removeButtons: '',
            format_tags: 'p;h1;h2;h3;h4;h5;h6;pre;address;div',
            removeDialogTabs: 'image:advanced;link:advanced'
        });
        
        // Character counter for title
        const titleInput = document.getElementById('title');
        const titleCount = document.getElementById('titleCount');
        
        titleInput.addEventListener('input', function() {
            titleCount.textContent = this.value.length;
        });
        
        // Video preview function
        function previewVideo() {
            const url = document.getElementById('video_url').value;
            const preview = document.getElementById('videoPreview');
            const iframe = document.getElementById('videoIframe');
            
            if (url) {
                let embedUrl = url;
                
                // YouTube
                if (url.includes('youtube.com/watch?v=')) {
                    const videoId = url.split('v=')[1].split('&')[0];
                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                } 
                // YouTu.be
                else if (url.includes('youtu.be/')) {
                    const videoId = url.split('youtu.be/')[1].split('?')[0];
                    embedUrl = 'https://www.youtube.com/embed/' + videoId;
                }
                // YouTube embed
                else if (url.includes('youtube.com/embed/')) {
                    embedUrl = url;
                }
                
                iframe.src = embedUrl;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                iframe.src = '';
            }
        }
        
        // File upload handling
        function handleFileSelect(input) {
            const container = document.getElementById('selectedFileContainer');
            const fileName = document.getElementById('selectedFileName');
            const fileSize = document.getElementById('selectedFileSize');
            const fileIcon = document.getElementById('fileIcon');
            
            if (input.files.length > 0) {
                const file = input.files[0];
                const ext = file.name.split('.').pop().toLowerCase();
                const size = (file.size / 1024).toFixed(2);
                
                fileName.textContent = file.name;
                fileSize.textContent = size + ' KB';
                
                // Set icon based on file type
                let icon = 'fa-file';
                if (ext === 'pdf') icon = 'fa-file-pdf';
                else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word';
                else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) icon = 'fa-file-image';
                else if (['mp4', 'mov', 'avi'].includes(ext)) icon = 'fa-file-video';
                else if (['ppt', 'pptx'].includes(ext)) icon = 'fa-file-powerpoint';
                else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel';
                else if (ext === 'txt') icon = 'fa-file-alt';
                else if (ext === 'zip' || ext === 'rar') icon = 'fa-file-archive';
                
                fileIcon.innerHTML = `<i class="fas ${icon}"></i>`;
                
                container.style.display = 'block';
                document.getElementById('uploadArea').style.display = 'none';
            }
        }
        
        function removeSelectedFile() {
            document.getElementById('fileInput').value = '';
            document.getElementById('selectedFileContainer').style.display = 'none';
            document.getElementById('uploadArea').style.display = 'block';
        }
        
        // Form validation
        document.getElementById('lessonForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a lesson title');
                return;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;
        });
        
        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#4f46e5';
            this.style.background = '#eef2ff';
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#cbd5e1';
            this.style.background = '#f8fafc';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#cbd5e1';
            this.style.background = '#f8fafc';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('fileInput').files = files;
                handleFileSelect(document.getElementById('fileInput'));
            }
        });
    </script>
</body>
</html>