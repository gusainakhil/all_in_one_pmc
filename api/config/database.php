<?php
class Database {
    private $host = "160.187.5.190";
    private $dbname = "pmcbeatlemeco_db";
    private $username = "pmcbeatlemeco_user";
    private $password = "Aksh@9412";
    public $conn;

    public function connect() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", 
                                  $this->username, 
                                  $this->password,
                                  array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            echo json_encode([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ]);
            exit;
        }
    }
}
