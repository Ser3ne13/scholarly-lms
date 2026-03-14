<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Scholarly</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 550px;
            padding: 40px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 32px;
            font-weight: 700;
        }
        
        .role-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .role-card {
            flex: 1;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-card i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .role-card h4 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .role-card p {
            font-size: 12px;
            color: #666;
        }
        
        .role-card.active {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.2);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin: 20px 0;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-link {
            text-align: center;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .strength-bar.weak { width: 33.33%; background: #ff4444; }
        .strength-bar.medium { width: 66.66%; background: #ffbb33; }
        .strength-bar.strong { width: 100%; background: #00C851; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>Join Scholarly</h1>
            <p>Create your account</p>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="role-selector">
            <div class="role-card active" data-role="student" onclick="selectRole('student')">
                <i class="fas fa-user-graduate"></i>
                <h4>Student</h4>
                <p>Join courses and learn</p>
            </div>
            <div class="role-card" data-role="teacher" onclick="selectRole('teacher')">
                <i class="fas fa-chalkboard-teacher"></i>
                <h4>Teacher</h4>
                <p>Create and manage courses</p>
            </div>
        </div>

        <form action="/mywebsite10/controllers/register_process.php" method="POST" id="registerForm">
            <input type="hidden" name="role" id="selectedRole" value="student">

            <div class="form-row">
                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                </div>
            </div>

            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                </div>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
            </div>

            <button type="submit" class="btn-register" id="registerBtn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script>
        function selectRole(role) {
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('active');
            });
            document.querySelector(`[data-role="${role}"]`).classList.add('active');
            document.getElementById('selectedRole').value = role;
        }

        const password = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        password.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            
            if (pwd.length >= 8) strength++;
            if (/[A-Z]/.test(pwd)) strength++;
            if (/[a-z]/.test(pwd)) strength++;
            if (/[0-9]/.test(pwd)) strength++;
            
            strengthBar.className = 'strength-bar';
            if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });
    </script>
</body>
</html>