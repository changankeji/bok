-- 博客系统数据库结构
-- 创建数据库
CREATE DATABASE IF NOT EXISTS blog_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_system;

-- 用户表
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 文章分类表
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 文章表
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    category_id INT,
    author_id INT NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 插入示例数据
-- 插入管理员用户 (密码: admin123)
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@blog.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin');

-- 插入示例分类
INSERT INTO categories (name, slug, description) VALUES 
('技术分享', 'tech', '分享最新的技术文章和编程经验'),
('生活随笔', 'life', '记录生活中的点点滴滴'),
('学习笔记', 'study', '整理学习过程中的重要知识点');

-- 插入示例文章
INSERT INTO posts (title, slug, content, excerpt, category_id, author_id, status, published_at) VALUES 
('欢迎来到我的博客', 'welcome-to-my-blog', '<h2>欢迎来到我的个人博客</h2><p>这是一个使用 PHP + MySQL 构建的现代化博客系统。在这里，我会分享技术文章、生活感悟和学习笔记。</p><p>博客系统具有以下特性：</p><ul><li>响应式设计，支持手机和电脑访问</li><li>文章分类管理</li><li>后台管理功能</li><li>现代化UI设计</li></ul>', '欢迎来到我的个人博客，这里会分享技术文章、生活感悟和学习笔记。', 1, 1, 'published', NOW()),
('PHP开发最佳实践', 'php-best-practices', '<h2>PHP开发最佳实践</h2><p>在PHP开发中，遵循最佳实践可以提高代码质量和维护性。</p><h3>1. 使用PDO进行数据库操作</h3><p>PDO提供了更好的安全性和跨数据库兼容性。</p><h3>2. 错误处理</h3><p>合理使用try-catch块处理异常。</p><h3>3. 代码规范</h3><p>遵循PSR标准，保持代码一致性。</p>', '分享PHP开发中的最佳实践，包括数据库操作、错误处理和代码规范。', 1, 1, 'published', NOW()),
('如何保持学习动力', 'how-to-maintain-learning-motivation', '<h2>如何保持学习动力</h2><p>学习是一个持续的过程，保持动力很重要。</p><h3>设定明确目标</h3><p>制定具体、可衡量的学习目标。</p><h3>建立学习习惯</h3><p>每天固定时间学习，形成习惯。</p><h3>记录学习成果</h3><p>记录学习过程和成果，看到进步。</p>', '分享如何在学习过程中保持动力的方法和技巧。', 3, 1, 'published', NOW());
