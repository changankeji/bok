<?php
/**
 * 文章编辑页面
 * 支持创建新文章和编辑现有文章
 */

require_once 'auth.php';
require_once '../classes/Post.php';
require_once '../classes/Category.php';

$post = new Post();
$category = new Category();

$isEdit = isset($_GET['id']);
$article = null;
$categories = $category->getAllCategories();

// 如果是编辑模式，获取文章信息
if ($isEdit) {
    $id = (int)$_GET['id'];
    $article = $post->getPostById($id);
    if (!$article) {
        $_SESSION['error'] = '文章不存在';
        header('Location: posts.php');
        exit;
    }
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $status = $_POST['status'] ?? 'draft';
    $featuredImage = trim($_POST['featured_image'] ?? '');

    // 验证必填字段
    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        // 生成slug
        $slug = $post->generateSlug($title, $isEdit ? $article['id'] : null);
        
        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'category_id' => $categoryId,
            'status' => $status,
            'featured_image' => $featuredImage,
            'author_id' => $currentUser['id']
        ];

        if ($isEdit) {
            // 更新文章
            if ($post->updatePost($article['id'], $data)) {
                $_SESSION['success'] = '文章更新成功';
                header('Location: posts.php');
                exit;
            } else {
                $error = '文章更新失败';
            }
        } else {
            // 创建新文章
            $newId = $post->createPost($data);
            if ($newId) {
                $_SESSION['success'] = '文章创建成功';
                header('Location: posts.php');
                exit;
            } else {
                $error = '文章创建失败';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? '编辑文章' : '写文章'; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
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
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-1"></i>用户管理
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
                    <h1 class="h2"><?php echo $isEdit ? '编辑文章' : '写文章'; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="posts.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>返回列表
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 错误提示 -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 编辑表单 -->
                <form method="POST" id="postForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">文章内容</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">文章标题 <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>" 
                                               required maxlength="255">
                                    </div>

                                    <div class="mb-3">
                                        <label for="content" class="form-label">文章内容 <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">文章摘要</label>
                                        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="文章摘要，用于首页和列表页显示..."><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">发布设置</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">状态</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo ($article['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>草稿</option>
                                            <option value="published" <?php echo ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>发布</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">分类</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">选择分类</option>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($article['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">特色图片</label>
                                        <input type="url" class="form-control" id="featured_image" name="featured_image" 
                                               value="<?php echo htmlspecialchars($article['featured_image'] ?? ''); ?>" 
                                               placeholder="图片URL">
                                        <div class="form-text">输入图片的完整URL地址</div>
                                    </div>

                                    <?php if ($isEdit && $article['created_at']): ?>
                                    <div class="mb-3">
                                        <label class="form-label">创建时间</label>
                                        <p class="form-control-plaintext"><?php echo date('Y-m-d H:i:s', strtotime($article['created_at'])); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($isEdit && $article['updated_at']): ?>
                                    <div class="mb-3">
                                        <label class="form-label">更新时间</label>
                                        <p class="form-control-plaintext"><?php echo date('Y-m-d H:i:s', strtotime($article['updated_at'])); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">操作</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            <?php echo $isEdit ? '更新文章' : '发布文章'; ?>
                                        </button>
                                        <a href="posts.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>取消
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-zh-CN.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // 初始化富文本编辑器
            $('#content').summernote({
                height: 400,
                lang: 'zh-CN',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // 自动生成摘要
            $('#content').on('summernote.change', function() {
                var content = $(this).summernote('code');
                var text = $(content).text();
                if (text.length > 0 && $('#excerpt').val() === '') {
                    $('#excerpt').val(text.substring(0, 200) + (text.length > 200 ? '...' : ''));
                }
            });
        });
    </script>
</body>
</html>
