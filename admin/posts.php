<?php
/**
 * 文章管理页面
 * 显示文章列表，支持编辑、删除、发布等操作
 */

require_once 'auth.php';
require_once '../classes/Post.php';
require_once '../classes/Category.php';

$post = new Post();
$category = new Category();

// 处理删除操作
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($post->deletePost($id)) {
        $_SESSION['success'] = '文章删除成功';
    } else {
        $_SESSION['error'] = '文章删除失败';
    }
    header('Location: posts.php');
    exit;
}

// 处理状态切换
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $currentPost = $post->getPostById($id);
    if ($currentPost) {
        $newStatus = $currentPost['status'] === 'published' ? 'draft' : 'published';
        $post->updatePost($id, ['status' => $newStatus]);
        $_SESSION['success'] = '文章状态更新成功';
    }
    header('Location: posts.php');
    exit;
}

// 获取参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// 获取数据
$categories = $category->getAllCategories();
$posts = $post->getAllPosts($page, 10);
$totalPosts = count($post->getAllPosts(1, 9999));
$totalPages = ceil($totalPosts / 10);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文章管理 - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link active" href="posts.php">
                            <i class="fas fa-file-alt me-1"></i>文章管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-folder me-1"></i>分类管理
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
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
                            <a class="nav-link active" href="posts.php">
                                <i class="fas fa-file-alt me-2"></i>文章管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-folder me-2"></i>分类管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
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
                    <h1 class="h2">文章管理</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="post-edit.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>写文章
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 消息提示 -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 筛选和搜索 -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">状态</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">全部状态</option>
                                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>已发布</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>草稿</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">分类</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">全部分类</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">搜索</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索文章标题或内容...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>搜索
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 文章列表 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">文章列表 (共 <?php echo $totalPosts; ?> 篇)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>标题</th>
                                        <th>分类</th>
                                        <th>作者</th>
                                        <th>状态</th>
                                        <th>发布时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $article): ?>
                                    <tr>
                                        <td>
                                            <a href="post-edit.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($article['category_name']): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">未分类</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($article['author_name']); ?></td>
                                        <td>
                                            <?php if ($article['status'] === 'published'): ?>
                                                <span class="badge bg-success">已发布</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">草稿</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($article['published_at']): ?>
                                                <?php echo date('Y-m-d H:i', strtotime($article['published_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">未发布</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="post-edit.php?id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-outline-primary" title="编辑">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../post.php?slug=<?php echo $article['slug']; ?>" 
                                                   target="_blank" class="btn btn-outline-info" title="预览">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="?action=toggle_status&id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $article['status'] === 'published' ? 'warning' : 'success'; ?>" 
                                                   title="<?php echo $article['status'] === 'published' ? '设为草稿' : '发布'; ?>"
                                                   onclick="return confirm('确定要<?php echo $article['status'] === 'published' ? '设为草稿' : '发布'; ?>这篇文章吗？')">
                                                    <i class="fas fa-<?php echo $article['status'] === 'published' ? 'eye-slash' : 'check'; ?>"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-outline-danger" title="删除"
                                                   onclick="return confirm('确定要删除这篇文章吗？此操作不可恢复！')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- 分页 -->
                        <?php if ($totalPages > 1): ?>
                        <nav aria-label="文章分页">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>">
                                        上一页
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>">
                                        下一页
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
