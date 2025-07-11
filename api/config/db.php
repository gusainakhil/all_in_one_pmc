<?php
class Database {
    private $host = "160.187.5.190";
    private $dbname = "pmcbeatlemeco_db";
    private $username = "pmcbeatlemeco_user";
    private $password = "Aksh@9412";
    public $conn;
    
    

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=".$this->host.";dbname=".$this->db_name, 
                                  $this->username, 
                                  $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
