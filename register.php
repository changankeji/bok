<?php
/**
 * 用户注册页面
 * 允许普通用户注册账号
 */

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

session_start();

// 如果已经登录，重定向到首页
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // 验证输入
    if (empty($username) || empty($email) || empty($password)) {
        $error = '请填写所有必填字段';
    } elseif (strlen($username) < 3) {
        $error = '用户名至少需要3个字符';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '请输入有效的邮箱地址';
    } elseif (strlen($password) < 6) {
        $error = '密码至少需要6个字符';
    } elseif ($password !== $confirmPassword) {
        $error = '两次输入的密码不一致';
    } else {
        // 尝试注册用户
        if ($user->register($username, $email, $password, 'editor')) {
            $success = '注册成功！请登录您的账号。';
            // 延迟重定向到登录页
            header('refresh:2;url=login.php');
        } else {
            $error = '注册失败，用户名或邮箱可能已被使用';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册 - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-card p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">用户注册</h3>
                        <p class="text-muted">创建您的账号，开始写作之旅</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        <p class="mb-0 mt-2">正在跳转到登录页面...</p>
                    </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">用户名 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                       required minlength="3" maxlength="50">
                            </div>
                            <div class="form-text">至少3个字符，支持字母、数字和下划线</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">邮箱地址 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required maxlength="100">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">密码 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6">
                            </div>
                            <div class="form-text">至少6个字符</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">确认密码 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required minlength="6">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>注册账号
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0">
                            已有账号？<a href="login.php" class="text-decoration-none">立即登录</a>
                        </p>
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i>返回首页
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 密码确认验证
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('密码不匹配');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
