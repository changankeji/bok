<?php
/**
 * 用户写文章页面
 * 允许登录用户发布文章
 */

require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/Post.php';
require_once 'classes/Category.php';

session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$post = new Post();
$category = new Category();

// 获取分类列表
$categories = $category->getAllCategories();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $featuredImage = trim($_POST['featured_image'] ?? '');

    // 验证必填字段
    if (empty($title) || empty($content)) {
        $error = '标题和内容不能为空';
    } else {
        // 生成slug
        $slug = $post->generateSlug($title);
        
        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'category_id' => $categoryId,
            'status' => 'draft', // 用户发布的文章默认为草稿
            'featured_image' => $featuredImage,
            'author_id' => $_SESSION['user_id']
        ];

        // 创建新文章
        $newId = $post->createPost($data);
        if ($newId) {
            $success = '文章保存成功！管理员审核后将发布。';
            // 清空表单
            $_POST = [];
        } else {
            $error = '文章保存失败，请重试';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>写文章 - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
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
                    <li class="nav-item">
                        <a class="nav-link active" href="write.php">写文章</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php">
                                <i class="fas fa-home me-1"></i>返回首页
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

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-edit me-2"></i>写文章
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" id="postForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">文章标题 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                                       required maxlength="255" placeholder="请输入文章标题">
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">文章内容 <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label">文章摘要</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" 
                                          placeholder="文章摘要，用于首页和列表页显示..."><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">分类</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">选择分类</option>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                    <?php echo ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="featured_image" class="form-label">特色图片</label>
                                        <input type="url" class="form-control" id="featured_image" name="featured_image" 
                                               value="<?php echo htmlspecialchars($_POST['featured_image'] ?? ''); ?>" 
                                               placeholder="图片URL">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>注意：</strong>您发布的文章将作为草稿保存，需要管理员审核后才能发布。
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>取消
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>保存文章
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-zh-CN.min.js"></script>
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
