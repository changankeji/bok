<?php
/**
 * 用户管理类
 * 处理用户认证、注册、登录等功能
 */

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * 用户登录
     * @param string $username 用户名或邮箱
     * @param string $password 密码
     * @return array|false 成功返回用户信息，失败返回false
     */
    public function login($username, $password) {
        // 先查找用户
        $user = $this->db->queryOne("SELECT * FROM users WHERE username = ? OR email = ?", [$username, $username]);
        
        if ($user && password_verify($password, $user['password'])) {
            // 更新最后登录时间
            $this->db->execute("UPDATE users SET updated_at = NOW() WHERE id = ?", [$user['id']]);
            return $user;
        }
        return false;
    }

    /**
     * 用户注册
     * @param string $username 用户名
     * @param string $email 邮箱
     * @param string $password 密码
     * @param string $role 角色
     * @return bool 成功返回true，失败返回false
     */
    public function register($username, $email, $password, $role = 'editor') {
        try {
            // 检查用户名和邮箱是否已存在
            $existingUser = $this->db->queryOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existingUser) {
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
            $result = $this->db->execute($sql, [$username, $email, $hashedPassword, $role]);
            return $result > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据ID获取用户信息
     * @param int $id 用户ID
     * @return array|false
     */
    public function getUserById($id) {
        return $this->db->queryOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    /**
     * 更新用户信息
     * @param int $id 用户ID
     * @param array $data 更新数据
     * @return bool
     */
    public function updateUser($id, $data) {
        $allowedFields = ['username', 'email', 'role'];
        $updateFields = [];
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updateFields[] = "$field = ?";
                $values[] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, $values) > 0;
    }

    /**
     * 检查用户是否为管理员
     * @param int $userId 用户ID
     * @return bool
     */
    public function isAdmin($userId) {
        $user = $this->getUserById($userId);
        return $user && $user['role'] === 'admin';
    }

    /**
     * 获取所有用户
     * @return array
     */
    public function getAllUsers() {
        return $this->db->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    }

    /**
     * 删除用户
     * @param int $id 用户ID
     * @return bool
     */
    public function deleteUser($id) {
        return $this->db->execute("DELETE FROM users WHERE id = ?", [$id]) > 0;
    }
}
?>
