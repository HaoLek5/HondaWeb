<?php
class Database {
    private $host = "localhost";
    private $db_name = "quanlyxemay";
    private $username = "root";
    private $password = "Tnminhngoc412@";
    private $port = 3306;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password, $this -> port);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Lỗi kết nối: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
