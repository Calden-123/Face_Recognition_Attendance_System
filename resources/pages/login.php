<?php

//handle user login logics 



$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors['password'] = 'Password cannot be empty';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        exit();
    }
    if ($userType == "administrator") {
        $stmt = $pdo->prepare("SELECT * FROM tbladmin WHERE emailAddress = :email");
    } elseif ($userType == "lecture") {
        $stmt = $pdo->prepare("SELECT * FROM tbllecture WHERE emailAddress = :email");
    }
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

  
    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user'] = [
            'id' => $user['Id'],
            'email' => $user['emailAddress'],
            'name' => $user['firstName'],
            'role' => $userType,
        ];

        header('Location: home');
        exit();
    } else {
        $errors['login'] = 'Invalid email or password';
        $_SESSION['errors'] = $errors;
    }
}
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
}


function display_error($error, $is_main = false)
{
    global $errors;
    if (isset($errors["{$error}"])) {

        echo '<div class="' . ($is_main ? 'error-main' : 'error') . '">
                  <p>' . $errors["{$error}"] . '</p>
           </div>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Attendance Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }

        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .welcome-text p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 30px;
            position: relative;
            z-index: 2;
        }

        .features {
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .feature i {
            font-size: 1.2rem;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature span {
            font-size: 1rem;
        }

        .login-right {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #4f46e5;
        }

        .logo span {
            color: #7c3aed;
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 30px;
            text-align: center;
        }

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group select,
        .input-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .input-group select:focus,
        .input-group input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .recover {
            text-align: right;
            margin-bottom: 25px;
        }

        .recover a {
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .recover a:hover {
            color: #7c3aed;
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
            color: #9ca3af;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            color: #4b5563;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .social-btn.google:hover {
            background: #db4437;
            color: white;
            border-color: #db4437;
        }

        .social-btn.facebook:hover {
            background: #4267B2;
            color: white;
            border-color: #4267B2;
        }

        .error-main {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }

        .error {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        @media (max-width: 900px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }

            .login-left {
                padding: 30px;
                text-align: center;
            }

            .login-left::before,
            .login-left::after {
                display: none;
            }

            .feature {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .login-right {
                padding: 30px 20px;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-left">
            <div class="welcome-text">
                <h1>Welcome Back</h1>
                <p>Sign in to access your Attendance Management System dashboard and manage your classes efficiently.</p>
            </div>
            <div class="features">
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Track attendance in real-time</span>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <span>Manage students and classes</span>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure and reliable platform</span>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form">
                <div class="logo">
                    <h2>Attendify<span></span></h2>
                </div>
                <h1 class="form-title">Sign In</h1>
                <?php display_error('login', true); ?>
                
                <form method="POST" action="">
                    <div class="input-group">
                        <i class="fas fa-user-tie"></i>
                        <select name="user_type" required>
                            <option value="">Select User Type</option>
                            <option value="lecture">Lecture</option>
                            <option value="administrator">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" id="email" placeholder="Email Address" required>
                        <?php display_error('email'); ?>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <i class="fas fa-eye password-toggle" id="eye"></i>
                        <?php display_error('password'); ?>
                    </div>
                    
                    <div class="recover">
                        <a href="#">Forgot Password?</a>
                    </div>
                    
                    <input type="submit" class="btn" value="Sign In" name="login">
                </form>
                
                <div class="divider">
                    <span>Or continue with</span>
                </div>
                
                <div class="social-login">
                    <div class="social-btn google">
                        <i class="fab fa-google"></i>
                    </div>
                    <div class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye');
            
            eyeIcon.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        });
    </script>
</body>

</html>