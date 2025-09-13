<?php
/**
 * 前端设置管理页面
 * 管理网站基本设置
 */

require_once 'auth.php';

// 处理设置更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name'] ?? '');
    $siteUrl = trim($_POST['site_url'] ?? '');
    $siteDescription = trim($_POST['site_description'] ?? '');
    $postsPerPage = (int)($_POST['posts_per_page'] ?? 6);
    $allowRegistration = isset($_POST['allow_registration']) ? 1 : 0;
    $allowUserPosts = isset($_POST['allow_user_posts']) ? 1 : 0;
    
    // 验证输入
    if (empty($siteName)) {
        $error = '网站名称不能为空';
    } elseif ($postsPerPage < 1 || $postsPerPage > 50) {
        $error = '每页文章数必须在1-50之间';
    } else {
        // 更新配置文件
        $configContent = "<?php
/**
 * 博客系统配置文件
 * 包含数据库连接配置和其他系统设置
 */

// 数据库配置
define('DB_HOST', '" . DB_HOST . "');
define('DB_NAME', '" . DB_NAME . "');
define('DB_USER', '" . DB_USER . "');
define('DB_PASS', '" . DB_PASS . "');
define('DB_CHARSET', '" . DB_CHARSET . "');

// 网站配置
define('SITE_NAME', '" . addslashes($siteName) . "');
define('SITE_URL', '" . addslashes($siteUrl) . "');
define('SITE_DESCRIPTION', '" . addslashes($siteDescription) . "');

// 分页配置
define('POSTS_PER_PAGE', " . $postsPerPage . ");

// 文件上传配置
define('UPLOAD_PATH', '" . UPLOAD_PATH . "');
define('MAX_FILE_SIZE', " . MAX_FILE_SIZE . ");

// 安全配置
define('SESSION_TIMEOUT', " . SESSION_TIMEOUT . ");

// 功能开关
define('ALLOW_REGISTRATION', " . ($allowRegistration ? 'true' : 'false') . ");
define('ALLOW_USER_POSTS', " . ($allowUserPosts ? 'true' : 'false') . ");

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告设置（生产环境应关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>";
        
        if (file_put_contents('../config.php', $configContent)) {
            $_SESSION['success'] = '设置保存成功';
            header('Location: settings.php');
            exit;
        } else {
            $error = '设置保存失败，请检查文件权限';
        }
    }
}

// 获取当前设置
$currentSettings = [
    'site_name' => SITE_NAME,
    'site_url' => SITE_URL,
    'site_description' => SITE_DESCRIPTION,
    'posts_per_page' => POSTS_PER_PAGE,
    'allow_registration' => defined('ALLOW_REGISTRATION') ? ALLOW_REGISTRATION : true,
    'allow_user_posts' => defined('ALLOW_USER_POSTS') ? ALLOW_USER_POSTS : true
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>前端设置 - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i>管理后台
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>首页
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="posts.php">
                            <i class="fas fa-file-alt me-1"></i>文章管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-folder me-1"></i>分类管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cog me-1"></i>前端设置
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($currentUser['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>查看网站
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>退出登录
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- 侧边栏 -->
            <div class="col-md-3 col-lg-2 bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-home me-2"></i>首页
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="posts.php">
                                <i class="fas fa-file-alt me-2"></i>文章管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-folder me-2"></i>分类管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="settings.php">
                                <i class="fas fa-cog me-2"></i>前端设置
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>用户管理
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 主要内容 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">前端设置</h1>
                </div>

                <!-- 消息提示 -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 设置表单 -->
                <form method="POST">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">基本设置</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">网站名称 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($currentSettings['site_name']); ?>" 
                                               required maxlength="100">
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_url" class="form-label">网站URL</label>
                                        <input type="url" class="form-control" id="site_url" name="site_url" 
                                               value="<?php echo htmlspecialchars($currentSettings['site_url']); ?>" 
                                               placeholder="http://your-domain.com">
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_description" class="form-label">网站描述</label>
                                        <textarea class="form-control" id="site_description" name="site_description" 
                                                  rows="3" maxlength="500"><?php echo htmlspecialchars($currentSettings['site_description']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="posts_per_page" class="form-label">每页文章数</label>
                                        <input type="number" class="form-control" id="posts_per_page" name="posts_per_page" 
                                               value="<?php echo $currentSettings['posts_per_page']; ?>" 
                                               min="1" max="50" required>
                                        <div class="form-text">首页和分类页每页显示的文章数量</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="mb-0">功能设置</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" 
                                               <?php echo $currentSettings['allow_registration'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_registration">
                                            <strong>允许用户注册</strong>
                                            <div class="form-text">开启后用户可以在前端注册账号</div>
                                        </label>
                                    </div>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="allow_user_posts" name="allow_user_posts" 
                                               <?php echo $currentSettings['allow_user_posts'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_user_posts">
                                            <strong>允许用户发布文章</strong>
                                            <div class="form-text">开启后注册用户可以发布文章（需要审核）</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">操作</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>保存设置
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-arrow-left me-1"></i>返回首页
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">说明</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-2">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            网站名称会显示在浏览器标题栏
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            网站URL用于生成绝对链接
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            用户发布的文章默认为草稿状态
                                        </li>
                                        <li>
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            管理员可以审核并发布用户文章
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
