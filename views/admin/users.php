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

require_once '../../models/User.php';
$userModel = new User();
$users = $userModel->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Scholarly Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/mywebsite10/assets/css/modern-design.css">
    <style>
        .users-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
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
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
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

        .users-table {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 20px;
            background: #f8fafc;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #eef2f6;
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f6;
        }

        tr:hover {
            background: #f8fafc;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-teacher { background: #dbeafe; color: #1e40af; }
        .role-student { background: #dcfce7; color: #166534; }

        .status-badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .edit-btn { background: #eef2ff; color: #4f46e5; }
        .edit-btn:hover { background: #4f46e5; color: white; }

        .delete-btn { background: #fee2e2; color: #ef4444; }
        .delete-btn:hover { background: #ef4444; color: white; }

        .toggle-btn { background: #f1f5f9; color: #64748b; }
        .toggle-btn:hover { background: #10b981; color: white; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
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
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar"></i> Master Schedule</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li style="margin-top: 20px;"><a href="/mywebsite10/controllers/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="users-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Manage Users</h1>
                <a href="create-user.php" class="btn-create">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <?php
                $total_users = count($users);
                $total_admins = 0;
                $total_teachers = 0;
                $total_students = 0;
                
                foreach ($users as $user) {
                    if ($user['role'] === 'admin') $total_admins++;
                    elseif ($user['role'] === 'teacher') $total_teachers++;
                    elseif ($user['role'] === 'student') $total_students++;
                }
                ?>
                
                <div class="stat-card">
                    <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                    <div>
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <h3><?php echo $total_admins; ?></h3>
                        <p>Admins</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div>
                        <h3><?php echo $total_teachers; ?></h3>
                        <p>Teachers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning"><i class="fas fa-user-graduate"></i></div>
                    <div>
                        <h3><?php echo $total_students; ?></h3>
                        <p>Students</p>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search by name or email...">
                    <button onclick="searchUsers()" style="padding: 12px 24px; background: #4f46e5; color: white; border: none; border-radius: 12px; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="filter-select" id="roleFilter" onchange="filterByRole()">
                    <option value="all">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
                <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="users-table">
                <div class="table-responsive">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row" 
                                    data-role="<?php echo $user['role']; ?>" 
                                    data-status="<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>"
                                    data-name="<?php echo strtolower($user['first_name'] . ' ' . $user['last_name']); ?>"
                                    data-email="<?php echo strtolower($user['email']); ?>">
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar-small">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="/mywebsite10/controllers/AdminController.php?action=edit_user&id=<?php echo $user['user_id']; ?>" class="action-btn edit-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <a href="/mywebsite10/controllers/AdminController.php?action=toggle_status&id=<?php echo $user['user_id']; ?>&status=<?php echo $user['is_active'] ? 'inactive' : 'active'; ?>" 
                                                   class="action-btn toggle-btn" 
                                                   title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>"
                                                   onclick="return confirm('Are you sure?')">
                                                    <i class="fas <?php echo $user['is_active'] ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                </a>
                                                
                                                <a href="/mywebsite10/controllers/AdminController.php?action=delete_user&id=<?php echo $user['user_id']; ?>" 
                                                   class="action-btn delete-btn" 
                                                   title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <button class="page-btn" onclick="changePage(-1)"><i class="fas fa-chevron-left"></i></button>
                    <span id="pageInfo">Page 1</span>
                    <button class="page-btn" onclick="changePage(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Search and filter functionality
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('.user-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                
                const matchesSearch = searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = roleFilter === 'all' || role === roleFilter;
                const matchesStatus = statusFilter === 'all' || status === statusFilter;
                
                row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
            });
        }
        
        function searchUsers() {
            filterTable();
        }
        
        function filterByRole() {
            filterTable();
        }
        
        function filterByStatus() {
            filterTable();
        }
        
        document.getElementById('searchInput').addEventListener('keyup', filterTable);

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