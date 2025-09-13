<?php
/**
 * 博客系统配置文件
 * 包含数据库连接配置和其他系统设置
 */

// 数据库配置
define('DB_HOST', 'localhost');
define('DB_NAME', 'gw_hlkjliehua_as');
define('DB_USER', 'gw_hlkjliehua_as');
define('DB_PASS', 'Qk3JaxyRyje3GN4p');
define('DB_CHARSET', 'utf8mb4');

// 网站配置
define('SITE_NAME', '我的个人博客');
define('SITE_URL', 'http://localhost');
define('SITE_DESCRIPTION', '分享技术文章、生活感悟和学习笔记');

// 分页配置
define('POSTS_PER_PAGE', 6);

// 文件上传配置
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// 安全配置
define('SESSION_TIMEOUT', 3600); // 1小时

// 功能开关
define('ALLOW_REGISTRATION', true); // 允许用户注册
define('ALLOW_USER_POSTS', true); // 允许用户发布文章

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告设置（生产环境应关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
