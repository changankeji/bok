<?php
/**
 * 文章管理类
 * 处理文章的增删改查、分类管理等功能
 */

class Post {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * 获取所有已发布的文章（分页）
     * @param int $page 页码
     * @param int $limit 每页数量
     * @param int $categoryId 分类ID（可选）
     * @return array
     */
    public function getPublishedPosts($page = 1, $limit = POSTS_PER_PAGE, $categoryId = null) {
        $offset = ($page - 1) * $limit;
        $whereClause = "WHERE p.status = 'published'";
        $params = [];

        if ($categoryId) {
            $whereClause .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                       u.username as author_name
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                $whereClause 
                ORDER BY p.published_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql, $params);
    }

    /**
     * 获取文章总数
     * @param int $categoryId 分类ID（可选）
     * @return int
     */
    public function getTotalPosts($categoryId = null) {
        $whereClause = "WHERE status = 'published'";
        $params = [];

        if ($categoryId) {
            $whereClause .= " AND category_id = ?";
            $params[] = $categoryId;
        }

        $result = $this->db->queryOne("SELECT COUNT(*) as total FROM posts $whereClause", $params);
        return $result['total'];
    }

    /**
     * 根据ID获取文章详情
     * @param int $id 文章ID
     * @return array|false
     */
    public function getPostById($id) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                       u.username as author_name
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * 根据slug获取文章详情
     * @param string $slug 文章slug
     * @return array|false
     */
    public function getPostBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                       u.username as author_name
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.slug = ? AND p.status = 'published'";
        return $this->db->queryOne($sql, [$slug]);
    }

    /**
     * 创建新文章
     * @param array $data 文章数据
     * @return int|false 成功返回文章ID，失败返回false
     */
    public function createPost($data) {
        try {
            $sql = "INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, author_id, status, published_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $publishedAt = ($data['status'] === 'published') ? date('Y-m-d H:i:s') : null;
            
            $result = $this->db->execute($sql, [
                $data['title'],
                $data['slug'],
                $data['content'],
                $data['excerpt'],
                $data['featured_image'] ?? null,
                $data['category_id'],
                $data['author_id'],
                $data['status'],
                $publishedAt
            ]);

            return $result > 0 ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 更新文章
     * @param int $id 文章ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updatePost($id, $data) {
        try {
            $sql = "UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, 
                    featured_image = ?, category_id = ?, status = ?, updated_at = NOW()";
            
            $params = [
                $data['title'],
                $data['slug'],
                $data['content'],
                $data['excerpt'],
                $data['featured_image'] ?? null,
                $data['category_id'],
                $data['status']
            ];

            // 如果状态改为published且之前未发布，设置发布时间
            if ($data['status'] === 'published') {
                $currentPost = $this->getPostById($id);
                if (!$currentPost['published_at']) {
                    $sql .= ", published_at = NOW()";
                }
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            return $this->db->execute($sql, $params) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除文章
     * @param int $id 文章ID
     * @return bool
     */
    public function deletePost($id) {
        return $this->db->execute("DELETE FROM posts WHERE id = ?", [$id]) > 0;
    }

    /**
     * 获取所有文章（管理后台用）
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getAllPosts($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT p.*, c.name as category_name, u.username as author_name
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        return $this->db->query($sql, [$limit, $offset]);
    }

    /**
     * 搜索文章
     * @param string $keyword 关键词
     * @param int $page 页码
     * @param int $limit 每页数量
     * @return array
     */
    public function searchPosts($keyword, $page = 1, $limit = POSTS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                       u.username as author_name
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)
                ORDER BY p.published_at DESC 
                LIMIT ? OFFSET ?";
        
        $searchTerm = "%$keyword%";
        return $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    }

    /**
     * 生成文章slug
     * @param string $title 文章标题
     * @param int $excludeId 排除的文章ID（用于更新时）
     * @return string
     */
    public function generateSlug($title, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM posts WHERE slug = ?";
            $params = [$slug];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $this->db->queryOne($sql, $params);
            
            if (!$existing) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
?>
