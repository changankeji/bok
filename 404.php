<?php
/**
 * 404错误页面
 */

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Category.php';

$category = new Category();
$categories = $category->getCategoriesWithPostCount();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>页面未找到 - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-blog me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">首页</a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?category=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex">
                    <a href="admin/login.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-in-alt me-1"></i>管理后台
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 404内容 -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="error-page">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <h1 class="display-1 fw-bold text-primary">404</h1>
                    <h2 class="h3 mb-3">页面未找到</h2>
                    <p class="text-muted mb-4">
                        抱歉，您访问的页面不存在或已被删除。
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>返回首页
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>返回上页
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted"><?php echo SITE_DESCRIPTION; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
