<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Scholarly</title>
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
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite;
        }
        
        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }
        
        .shape:nth-child(2) {
            width: 400px;
            height: 400px;
            bottom: -200px;
            right: -200px;
            animation-delay: -5s;
        }
        
        .shape:nth-child(3) {
            width: 200px;
            height: 200px;
            bottom: 50px;
            left: 50px;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 450px;
            padding: 50px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
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
            margin-bottom: 40px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .links {
            text-align: center;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #764ba2;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #c33;
            animation: shake 0.5s;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #28a745;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .demo-credentials {
            margin-top: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 13px;
            color: #666;
        }
        
        .demo-credentials strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>

    <div class="login-container">
        <div class="logo">
            <h1>Scholarly</h1>
            <p>Smart Learning Management System</p>
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="/mywebsite10/controllers/login_process.php" method="POST">
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="links">
                <a href="register.php"><i class="fas fa-user-plus"></i> Create Account</a>
                <a href="#"><i class="fas fa-key"></i> Forgot Password?</a>
            </div>
        </form>

        <div class="demo-credentials">
            <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong>
            <p><i class="fas fa-chalkboard-teacher"></i> Teacher: teacher@test.com / password123</p>
            <p><i class="fas fa-user-graduate"></i> Student: student@test.com / password123</p>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>