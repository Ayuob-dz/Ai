<?php
if (!defined('ACCESS_ALLOWED')) {
    die('Access Denied');
}

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            die("فشل الاتصال بقاعدة البيانات: " . $this->connection->connect_error);
        }
        $this->connection->set_charset("utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

$conn = Database::getInstance()->getConnection();
?>
