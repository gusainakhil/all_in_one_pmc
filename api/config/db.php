<?php
class Database {
    private $host = "localhost";
    private $db_name = "beatme_pmc_database";
    private $username = "beatme_pmc";
    private $password = "&r(x0xzIuoOS";
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
