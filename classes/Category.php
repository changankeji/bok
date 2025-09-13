<?php
/**
 * 分类管理类
 * 处理文章分类的增删改查功能
 */

class Category {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * 获取所有分类
     * @return array
     */
    public function getAllCategories() {
        return $this->db->query("SELECT * FROM categories ORDER BY name ASC");
    }

    /**
     * 根据ID获取分类
     * @param int $id 分类ID
     * @return array|false
     */
    public function getCategoryById($id) {
        return $this->db->queryOne("SELECT * FROM categories WHERE id = ?", [$id]);
    }

    /**
     * 根据slug获取分类
     * @param string $slug 分类slug
     * @return array|false
     */
    public function getCategoryBySlug($slug) {
        return $this->db->queryOne("SELECT * FROM categories WHERE slug = ?", [$slug]);
    }

    /**
     * 创建新分类
     * @param array $data 分类数据
     * @return int|false 成功返回分类ID，失败返回false
     */
    public function createCategory($data) {
        try {
            $sql = "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)";
            $result = $this->db->execute($sql, [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null
            ]);
            return $result > 0 ? $this->db->lastInsertId() : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 更新分类
     * @param int $id 分类ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateCategory($id, $data) {
        try {
            $sql = "UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?";
            return $this->db->execute($sql, [
                $data['name'],
                $data['slug'],
                $data['description'] ?? null,
                $id
            ]) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除分类
     * @param int $id 分类ID
     * @return bool
     */
    public function deleteCategory($id) {
        try {
            // 检查是否有文章使用此分类
            $posts = $this->db->queryOne("SELECT COUNT(*) as count FROM posts WHERE category_id = ?", [$id]);
            if ($posts['count'] > 0) {
                return false; // 不能删除有文章的分类
            }
            
            return $this->db->execute("DELETE FROM categories WHERE id = ?", [$id]) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 生成分类slug
     * @param string $name 分类名称
     * @param int $excludeId 排除的分类ID（用于更新时）
     * @return string
     */
    public function generateSlug($name, $excludeId = null) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = trim($slug, '-');
        
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM categories WHERE slug = ?";
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

    /**
     * 获取分类及其文章数量
     * @return array
     */
    public function getCategoriesWithPostCount() {
        $sql = "SELECT c.*, COUNT(p.id) as post_count 
                FROM categories c 
                LEFT JOIN posts p ON c.id = p.category_id AND p.status = 'published'
                GROUP BY c.id 
                ORDER BY c.name ASC";
        return $this->db->query($sql);
    }
}
?>
