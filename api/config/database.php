

<?php
class Database {
    private $host = "localhost";
    private $dbname = "beatme_pmc_database";
    private $username = "beatme_pmc";
    private $password = "&r(x0xzIuoOS";
    public $conn;

    public function connect() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
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
