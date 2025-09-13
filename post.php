<?php
/**
 * 文章详情页
 * 显示单篇文章的完整内容
 */

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Post.php';
require_once 'classes/Category.php';

session_start();

$post = new Post();
$category = new Category();

// 获取文章slug
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// 获取文章详情
$article = $post->getPostBySlug($slug);

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// 获取相关文章（同分类的其他文章）
$relatedPosts = [];
if ($article['category_id']) {
    $relatedPosts = $post->getPublishedPosts(1, 3, $article['category_id']);
    // 移除当前文章
    $relatedPosts = array_filter($relatedPosts, function($p) use ($article) {
        return $p['id'] != $article['id'];
    });
    $relatedPosts = array_slice($relatedPosts, 0, 2); // 只显示2篇相关文章
}

// 获取所有分类（用于侧边栏）
$categories = $category->getCategoriesWithPostCount();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($article['category_name'] ?? ''); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo SITE_URL . '/post.php?slug=' . $article['slug']; ?>">
    <?php if ($article['featured_image']): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($article['featured_image']); ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="write.php">
                                <i class="fas fa-edit me-1"></i>写文章
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>退出登录
                            </a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="btn-group me-2">
                        <a href="login.php" class="btn btn-outline-light">
                            <i class="fas fa-sign-in-alt me-1"></i>登录
                        </a>
                        <?php if (ALLOW_REGISTRATION): ?>
                        <a href="register.php" class="btn btn-outline-light">
                            <i class="fas fa-user-plus me-1"></i>注册
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <a href="admin/login.php" class="btn btn-outline-light">
                        <i class="fas fa-cog me-1"></i>管理后台
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 面包屑导航 -->
    <div class="bg-light py-2">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">首页</a></li>
                    <?php if ($article['category_name']): ?>
                    <li class="breadcrumb-item">
                        <a href="index.php?category=<?php echo $article['category_id']; ?>">
                            <?php echo htmlspecialchars($article['category_name']); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?php echo htmlspecialchars($article['title']); ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- 主要内容 -->
    <div class="container my-5">
        <div class="row">
            <!-- 文章内容 -->
            <div class="col-lg-8">
                <article class="card">
                    <?php if ($article['featured_image']): ?>
                    <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($article['title']); ?>"
                         style="height: 400px; object-fit: cover;">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <header class="mb-4">
                            <h1 class="card-title h2 mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>
                            
                            <div class="d-flex flex-wrap align-items-center text-muted small mb-3">
                                <div class="me-4">
                                    <i class="fas fa-user me-1"></i>
                                    作者：<?php echo htmlspecialchars($article['author_name']); ?>
                                </div>
                                <div class="me-4">
                                    <i class="fas fa-calendar me-1"></i>
                                    发布时间：<?php echo date('Y年m月d日', strtotime($article['published_at'])); ?>
                                </div>
                                <div class="me-4">
                                    <i class="fas fa-clock me-1"></i>
                                    阅读时间：约 <?php echo ceil(str_word_count(strip_tags($article['content'])) / 200); ?> 分钟
                                </div>
                                <?php if ($article['category_name']): ?>
                                <div>
                                    <a href="index.php?category=<?php echo $article['category_id']; ?>" 
                                       class="badge bg-primary text-decoration-none">
                                        <i class="fas fa-folder me-1"></i>
                                        <?php echo htmlspecialchars($article['category_name']); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </header>

                        <div class="article-content">
                            <?php echo $article['content']; ?>
                        </div>

                        <!-- 文章标签 -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="text-muted me-2">标签：</span>
                                <?php if ($article['category_name']): ?>
                                <a href="index.php?category=<?php echo $article['category_id']; ?>" 
                                   class="badge bg-secondary text-decoration-none me-2">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- 相关文章 -->
                <?php if (!empty($relatedPosts)): ?>
                <div class="mt-5">
                    <h4 class="mb-3">
                        <i class="fas fa-bookmark me-2"></i>相关文章
                    </h4>
                    <div class="row">
                        <?php foreach ($relatedPosts as $relatedPost): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <?php if ($relatedPost['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($relatedPost['featured_image']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>"
                                     style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="post.php?slug=<?php echo $relatedPost['slug']; ?>" 
                                           class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($relatedPost['title']); ?>
                                        </a>
                                    </h6>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars($relatedPost['excerpt']); ?>
                                    </p>
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('Y-m-d', strtotime($relatedPost['published_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 侧边栏 -->
            <div class="col-lg-4">
                <!-- 搜索框 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>搜索文章</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="搜索文章...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 分类列表 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-folder me-2"></i>文章分类</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <a href="index.php" class="text-decoration-none d-flex justify-content-between align-items-center">
                                    <span>全部文章</span>
                                    <span class="badge bg-secondary"><?php echo $post->getTotalPosts(); ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li class="mb-2">
                                <a href="index.php?category=<?php echo $cat['id']; ?>" 
                                   class="text-decoration-none d-flex justify-content-between align-items-center">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="badge bg-primary"><?php echo $cat['post_count']; ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- 最新文章 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>最新文章</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $latestPosts = $post->getPublishedPosts(1, 5);
                        foreach ($latestPosts as $latestPost):
                            if ($latestPost['id'] == $article['id']) continue; // 排除当前文章
                        ?>
                        <div class="d-flex mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <a href="post.php?slug=<?php echo $latestPost['slug']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($latestPost['title']); ?>
                                    </a>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('Y-m-d', strtotime($latestPost['published_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
