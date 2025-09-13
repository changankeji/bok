<?php
/**
 * 分类管理页面
 * 显示分类列表，支持添加、编辑、删除分类
 */

require_once 'auth.php';
require_once '../classes/Category.php';
require_once '../classes/Post.php';

$category = new Category();
$post = new Post();

// 处理删除操作
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($category->deleteCategory($id)) {
        $_SESSION['success'] = '分类删除成功';
    } else {
        $_SESSION['error'] = '分类删除失败，该分类下还有文章';
    }
    header('Location: categories.php');
    exit;
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isEdit = isset($_POST['id']) && !empty($_POST['id']);
    $id = $isEdit ? (int)$_POST['id'] : null;

    if (empty($name)) {
        $error = '分类名称不能为空';
    } else {
        // 生成slug
        $slug = $category->generateSlug($name, $id);
        
        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ];

        if ($isEdit) {
            // 更新分类
            if ($category->updateCategory($id, $data)) {
                $_SESSION['success'] = '分类更新成功';
                header('Location: categories.php');
                exit;
            } else {
                $error = '分类更新失败';
            }
        } else {
            // 创建新分类
            $newId = $category->createCategory($data);
            if ($newId) {
                $_SESSION['success'] = '分类创建成功';
                header('Location: categories.php');
                exit;
            } else {
                $error = '分类创建失败';
            }
        }
    }
}

// 获取编辑的分类信息
$editCategory = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editCategory = $category->getCategoryById((int)$_GET['edit']);
}

// 获取所有分类
$categories = $category->getCategoriesWithPostCount();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link active" href="categories.php">
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
                            <a class="nav-link" href="posts.php">
                                <i class="fas fa-file-alt me-2"></i>文章管理
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="categories.php">
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
                    <h1 class="h2">分类管理</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="fas fa-plus me-1"></i>添加分类
                            </button>
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

                <!-- 错误提示 -->
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 分类列表 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">分类列表 (共 <?php echo count($categories); ?> 个)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-folder fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">暂无分类</h4>
                            <p class="text-muted">点击上方"添加分类"按钮创建第一个分类</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>分类名称</th>
                                        <th>Slug</th>
                                        <th>描述</th>
                                        <th>文章数量</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($cat['slug']); ?></code>
                                        </td>
                                        <td>
                                            <?php if ($cat['description']): ?>
                                                <?php echo htmlspecialchars($cat['description']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">无描述</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $cat['post_count']; ?></span>
                                        </td>
                                        <td>
                                            <?php echo date('Y-m-d H:i', strtotime($cat['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)"
                                                        title="编辑">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?action=delete&id=<?php echo $cat['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   title="删除"
                                                   onclick="return confirm('确定要删除这个分类吗？\n\n注意：如果该分类下有文章，将无法删除！')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 分类编辑模态框 -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="categoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">添加分类</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="categoryId" name="id">
                        
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">分类名称 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" name="name" required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">分类描述</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="3" 
                                      placeholder="输入分类描述..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>保存
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        function editCategory(category) {
            document.getElementById('categoryModalTitle').textContent = '编辑分类';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description || '';
            
            var modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        }

        // 重置表单
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModalTitle').textContent = '添加分类';
            document.getElementById('categoryId').value = '';
        });

        // 自动生成slug
        document.getElementById('categoryName').addEventListener('input', function() {
            var name = this.value;
            var slug = name.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            // 这里可以显示生成的slug，但实际保存时会重新生成
        });
    </script>
</body>
</html>
