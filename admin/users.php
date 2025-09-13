<?php
/**
 * 用户管理页面
 * 管理系统中的用户
 */

require_once 'auth.php';
require_once '../classes/User.php';

$user = new User();

// 处理删除操作
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id != $currentUser['id']) { // 不能删除自己
        if ($user->deleteUser($id)) {
            $_SESSION['success'] = '用户删除成功';
        } else {
            $_SESSION['error'] = '用户删除失败';
        }
    } else {
        $_SESSION['error'] = '不能删除自己的账号';
    }
    header('Location: users.php');
    exit;
}

// 处理角色更新
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id != $currentUser['id']) { // 不能修改自己的角色
        $targetUser = $user->getUserById($id);
        if ($targetUser) {
            $newRole = $targetUser['role'] === 'admin' ? 'editor' : 'admin';
            if ($user->updateUser($id, ['role' => $newRole])) {
                $_SESSION['success'] = '用户角色更新成功';
            } else {
                $_SESSION['error'] = '用户角色更新失败';
            }
        }
    } else {
        $_SESSION['error'] = '不能修改自己的角色';
    }
    header('Location: users.php');
    exit;
}

// 获取所有用户
$users = $user->getAllUsers();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-1"></i>前端设置
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">
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
                            <a class="nav-link active" href="users.php">
                                <i class="fas fa-users me-2"></i>用户管理
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 主要内容 -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">用户管理</h1>
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

                <!-- 用户列表 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">用户列表 (共 <?php echo count($users); ?> 个用户)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>用户名</th>
                                        <th>邮箱</th>
                                        <th>角色</th>
                                        <th>注册时间</th>
                                        <th>最后更新</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $userData): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($userData['username']); ?></strong>
                                            <?php if ($userData['id'] == $currentUser['id']): ?>
                                                <span class="badge bg-info">当前用户</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($userData['email']); ?></td>
                                        <td>
                                            <?php if ($userData['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">管理员</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">编辑者</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($userData['created_at'])); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($userData['updated_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($userData['id'] != $currentUser['id']): ?>
                                                <a href="?action=toggle_role&id=<?php echo $userData['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $userData['role'] === 'admin' ? 'warning' : 'success'; ?>" 
                                                   title="<?php echo $userData['role'] === 'admin' ? '降为编辑者' : '提升为管理员'; ?>"
                                                   onclick="return confirm('确定要<?php echo $userData['role'] === 'admin' ? '降为编辑者' : '提升为管理员'; ?>吗？')">
                                                    <i class="fas fa-<?php echo $userData['role'] === 'admin' ? 'user-minus' : 'user-plus'; ?>"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $userData['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   title="删除用户"
                                                   onclick="return confirm('确定要删除这个用户吗？\n\n注意：删除用户将同时删除该用户发布的所有文章！')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php else: ?>
                                                <span class="text-muted small">当前用户</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
