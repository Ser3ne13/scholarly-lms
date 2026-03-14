<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';
require_once '../../models/Section.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}

// Get section data from session (set by SectionController)
$section = $_SESSION['edit_section'] ?? null;

if (!$section) {
    header('Location: sections.php');
    exit();
}

// Clear from session
unset($_SESSION['edit_section']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Section - <?php echo htmlspecialchars($section['section_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
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

        .section-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .section-icon {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .section-info h2 {
            color: white;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .section-info p {
            opacity: 0.9;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #334155;
        }

        .form-group label i {
            color: #4f46e5;
            margin-right: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .btn-danger {
            background: #ef4444;
            margin-top: 10px;
        }

        .btn-danger:hover {
            background: #dc2626;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .info-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-card {
                padding: 25px;
            }
            
            .section-header {
                flex-direction: column;
                text-align: center;
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
        <div class="form-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Edit Section</h1>
                <a href="sections.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Sections
                </a>
            </div>

            <!-- Section Header -->
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="section-info">
                    <h2><?php echo htmlspecialchars($section['section_name']); ?></h2>
                    <p>Year <?php echo $section['year_level']; ?> • <?php echo $section['academic_year']; ?></p>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="form-card">
                <form action="/mywebsite10/controllers/SectionController.php?action=update" method="POST" id="editSectionForm">
                    <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Section Name</label>
                        <input type="text" name="section_name" class="form-control" 
                               value="<?php echo htmlspecialchars($section['section_name']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Academic Year</label>
                            <select name="academic_year" class="form-control" required>
                                <option value="2024-2025" <?php echo $section['academic_year'] == '2024-2025' ? 'selected' : ''; ?>>2024-2025</option>
                                <option value="2025-2026" <?php echo $section['academic_year'] == '2025-2026' ? 'selected' : ''; ?>>2025-2026</option>
                                <option value="2026-2027" <?php echo $section['academic_year'] == '2026-2027' ? 'selected' : ''; ?>>2026-2027</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-graduation-cap"></i> Year Level</label>
                            <select name="year_level" class="form-control" required>
                                <option value="1" <?php echo $section['year_level'] == 1 ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2" <?php echo $section['year_level'] == 2 ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3" <?php echo $section['year_level'] == 3 ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4" <?php echo $section['year_level'] == 4 ? 'selected' : ''; ?>>4th Year</option>
                                <option value="5" <?php echo $section['year_level'] == 5 ? 'selected' : ''; ?>>5th Year</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Section
                    </button>
                </form>

                <!-- Delete Button (Separate Form) -->
                <form action="/mywebsite10/controllers/SectionController.php?action=delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?')">
                    <input type="hidden" name="section_id" value="<?php echo $section['section_id']; ?>">
                    <button type="submit" class="btn-submit btn-danger">
                        <i class="fas fa-trash"></i> Delete Section
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('editSectionForm').addEventListener('submit', function(e) {
            const sectionName = document.querySelector('input[name="section_name"]').value.trim();
            
            if (!sectionName) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>