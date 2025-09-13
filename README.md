# 现代化博客系统

一个使用 PHP + MySQL 构建的现代化博客系统，具有响应式设计和完整的管理后台。

## 功能特性

### 前端功能
- 🏠 **首页展示** - 文章列表、分类筛选、搜索功能
- 📖 **文章详情** - 完整的文章阅读体验，支持代码高亮
- 📱 **响应式设计** - 完美支持手机、平板、电脑访问
- 🔍 **搜索功能** - 支持文章标题和内容搜索
- 🏷️ **分类管理** - 文章按分类展示和筛选
- ⚡ **现代化UI** - 使用Bootstrap 5构建的美观界面

### 后台管理
- 🔐 **用户认证** - 安全的登录系统
- 📝 **文章管理** - 创建、编辑、删除、发布文章
- 📂 **分类管理** - 管理文章分类
- 👥 **用户管理** - 管理系统用户
- 📊 **数据统计** - 文章数量、分类统计等
- 💾 **自动保存** - 编辑时自动保存草稿

## 技术栈

- **后端**: PHP 7.4+, MySQL 5.7+
- **前端**: HTML5, CSS3, JavaScript (ES6+)
- **框架**: Bootstrap 5, Summernote
- **数据库**: MySQL
- **其他**: PDO, Font Awesome

## 安装说明

### 环境要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Web服务器 (Apache/Nginx)
- 支持 PDO MySQL 扩展

### 安装步骤

1. **下载代码**
   ```bash
   git clone [repository-url]
   cd blog-system
   ```

2. **配置数据库**
   - 创建MySQL数据库
   - 导入 `database.sql` 文件
   - 修改 `config.php` 中的数据库配置

3. **配置Web服务器**
   - 将代码部署到Web服务器根目录
   - 确保PHP可以访问MySQL数据库

4. **设置权限**
   ```bash
   chmod 755 uploads/
   chmod 644 config.php
   ```

5. **访问系统**
   - 前端: `http://your-domain.com/`
   - 后台: `http://your-domain.com/admin/`

### 默认管理员账号
- 用户名: `admin`
- 密码: `admin123`

## 文件结构

```
blog-system/
├── admin/                  # 后台管理
│   ├── login.php          # 登录页面
│   ├── dashboard.php      # 仪表板
│   ├── posts.php          # 文章管理
│   ├── post-edit.php      # 文章编辑
│   ├── categories.php     # 分类管理
│   ├── auth.php           # 认证中间件
│   └── logout.php         # 退出登录
├── assets/                # 静态资源
│   ├── css/              # 样式文件
│   │   ├── style.css     # 前端样式
│   │   └── admin.css     # 后台样式
│   └── js/               # JavaScript文件
│       ├── main.js       # 前端脚本
│       └── admin.js      # 后台脚本
├── classes/              # PHP类文件
│   ├── Database.php      # 数据库连接类
│   ├── User.php          # 用户管理类
│   ├── Post.php          # 文章管理类
│   └── Category.php      # 分类管理类
├── uploads/              # 上传文件目录
├── config.php            # 配置文件
├── index.php             # 首页
├── post.php              # 文章详情页
├── 404.php               # 404错误页
├── database.sql          # 数据库结构
└── README.md             # 说明文档
```

## 数据库结构

### 用户表 (users)
- `id` - 用户ID (主键)
- `username` - 用户名
- `email` - 邮箱
- `password` - 密码 (加密)
- `role` - 角色 (admin/editor)
- `created_at` - 创建时间
- `updated_at` - 更新时间

### 分类表 (categories)
- `id` - 分类ID (主键)
- `name` - 分类名称
- `slug` - URL别名
- `description` - 分类描述
- `created_at` - 创建时间

### 文章表 (posts)
- `id` - 文章ID (主键)
- `title` - 文章标题
- `slug` - URL别名
- `content` - 文章内容
- `excerpt` - 文章摘要
- `featured_image` - 特色图片
- `category_id` - 分类ID (外键)
- `author_id` - 作者ID (外键)
- `status` - 状态 (draft/published)
- `published_at` - 发布时间
- `created_at` - 创建时间
- `updated_at` - 更新时间

## 配置说明

### 数据库配置 (config.php)
```php
define('DB_HOST', 'localhost');        // 数据库主机
define('DB_NAME', 'blog_system');      // 数据库名
define('DB_USER', 'root');             // 数据库用户名
define('DB_PASS', '');                 // 数据库密码
```

### 网站配置
```php
define('SITE_NAME', '我的个人博客');     // 网站名称
define('SITE_URL', 'http://localhost'); // 网站URL
define('POSTS_PER_PAGE', 6);           // 每页文章数
```

## 使用说明

### 创建文章
1. 登录管理后台
2. 点击"写文章"按钮
3. 填写文章标题、内容、摘要
4. 选择分类和状态
5. 点击"发布文章"

### 管理分类
1. 进入"分类管理"页面
2. 点击"添加分类"按钮
3. 填写分类名称和描述
4. 保存分类

### 自定义样式
- 修改 `assets/css/style.css` 自定义前端样式
- 修改 `assets/css/admin.css` 自定义后台样式

## 安全说明

- 所有用户输入都经过验证和过滤
- 使用PDO预处理语句防止SQL注入
- 密码使用PHP password_hash()加密
- 会话管理包含超时机制
- 文件上传限制文件类型和大小

## 浏览器支持

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## 更新日志

### v1.0.0 (2024-01-01)
- 初始版本发布
- 基础博客功能
- 管理后台
- 响应式设计

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个项目。

## 联系方式

如有问题，请通过以下方式联系：
- 邮箱: [your-email@example.com]
- GitHub: [your-github-username]

---

**注意**: 这是一个演示项目，在生产环境中使用前请确保进行充分的安全测试和配置优化。
