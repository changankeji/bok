<?php
/**
 * 博客首页
 * 显示文章列表、分类筛选、搜索功能
 */

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Post.php';
require_once 'classes/Category.php';

session_start();

$post = new Post();
$category = new Category();

// 获取参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 获取数据
$categories = $category->getCategoriesWithPostCount();

if ($search) {
    $posts = $post->searchPosts($search, $page, POSTS_PER_PAGE);
    $totalPosts = count($post->searchPosts($search, 1, 9999)); // 获取搜索结果总数
} else {
    $posts = $post->getPublishedPosts($page, POSTS_PER_PAGE, $categoryId);
    $totalPosts = $post->getTotalPosts($categoryId);
}

$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// 获取当前分类信息
$currentCategory = null;
if ($categoryId) {
    $currentCategory = $category->getCategoryById($categoryId);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
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

    <!-- 主要内容 -->
    <div class="container my-5">
        <div class="row">
            <!-- 侧边栏 -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>搜索文章</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="搜索文章...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
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
                                   class="text-decoration-none d-flex justify-content-between align-items-center
                                   <?php echo ($categoryId == $cat['id']) ? 'text-primary fw-bold' : ''; ?>">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="badge bg-primary"><?php echo $cat['post_count']; ?></span>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 文章列表 -->
            <div class="col-lg-9">
                <?php if ($search): ?>
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i>
                    搜索 "<?php echo htmlspecialchars($search); ?>" 的结果：
                </div>
                <?php elseif ($currentCategory): ?>
                <div class="alert alert-primary">
                    <i class="fas fa-folder me-2"></i>
                    分类：<?php echo htmlspecialchars($currentCategory['name']); ?>
                </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">暂无文章</h4>
                    <p class="text-muted">
                        <?php if ($search): ?>
                        没有找到相关文章，请尝试其他关键词。
                        <?php else: ?>
                        该分类下还没有文章。
                        <?php endif; ?>
                    </p>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($posts as $article): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($article['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="post.php?slug=<?php echo $article['slug']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars($article['excerpt']); ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <div>
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($article['author_name']); ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('Y-m-d', strtotime($article['published_at'])); ?>
                                        </div>
                                    </div>
                                    <?php if ($article['category_name']): ?>
                                    <div class="mt-2">
                                        <a href="index.php?category=<?php echo $article['category_id']; ?>" 
                                           class="badge bg-primary text-decoration-none">
                                            <?php echo htmlspecialchars($article['category_name']); ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 分页 -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="文章分页">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                上一页
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                下一页
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
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
    <script src="assets/js/main.js"></script>
</body>
</html>
