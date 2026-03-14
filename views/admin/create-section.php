<?php
session_start();

// Add cache control headers
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

require_once '../../includes/functions.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Location: /mywebsite10/views/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Section - Scholarly Admin</title>
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
                <h1>Create New Section</h1>
                <a href="sections.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Sections
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="form-card">
                <form action="/mywebsite10/controllers/SectionController.php?action=create" method="POST" id="createSectionForm">
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Section Name</label>
                        <input type="text" name="section_name" class="form-control" 
                               placeholder="e.g., Section A, BSIT-3A, Grade 10 - Einstein" required>
                        <div class="info-text">Give this section a descriptive name</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Academic Year</label>
                            <select name="academic_year" class="form-control" required>
                                <option value="">Select Academic Year</option>
                                <option value="2024-2025">2024-2025</option>
                                <option value="2025-2026">2025-2026</option>
                                <option value="2026-2027">2026-2027</option>
                            </select>
                        </div>

                       <div class="form-group">
                            <label><i class="fas fa-graduation-cap"></i> Year Level</label>
                            <select name="year_level" class="form-control" required>
                                <option value="">Select Year Level</option>
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
                        </div>
                    </div>

                    <div class="info-text" style="margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> You can assign students to this section after creation.
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create Section
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('createSectionForm').addEventListener('submit', function(e) {
            const sectionName = document.querySelector('input[name="section_name"]').value.trim();
            const academicYear = document.querySelector('select[name="academic_year"]').value;
            const yearLevel = document.querySelector('select[name="year_level"]').value;
            
            if (!sectionName || !academicYear || !yearLevel) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            submitBtn.disabled = true;
        });

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