<?php
/**
 * 管理员认证中间件
 * 检查用户是否已登录，未登录则重定向到登录页
 */

require_once '../config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';

session_start();

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 检查会话是否过期
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// 更新最后活动时间
$_SESSION['last_activity'] = time();

$user = new User();
$currentUser = $user->getUserById($_SESSION['user_id']);

if (!$currentUser) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
