<?php
/**
 * 数据库连接类
 * 使用PDO进行数据库操作，提供安全的数据库访问接口
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $pdo;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    /**
     * 获取PDO连接实例
     * @return PDO
     */
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return $this->pdo;
    }

    /**
     * 执行查询并返回结果
     * @param string $sql SQL查询语句
     * @param array $params 参数数组
     * @return array
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * 执行查询并返回单行结果
     * @param string $sql SQL查询语句
     * @param array $params 参数数组
     * @return array|false
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * 执行插入、更新或删除操作
     * @param string $sql SQL语句
     * @param array $params 参数数组
     * @return int 受影响的行数
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * 获取最后插入的ID
     * @return string
     */
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * 开始事务
     */
    public function beginTransaction() {
        $this->getConnection()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit() {
        $this->getConnection()->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->getConnection()->rollback();
    }
}
?>
