<?php
//old
// $host = 'localhost';
// $dbname = 'beatme_pmc_database'; 
// $username = 'beatme_pmc';
// $password = '&r(x0xzIuoOS';


//new 
$host = '97.74.91.11';
$dbname = 'beatme_pmcf'; 
$username = 'beatme_pmcf';
$password = 'jmXadm0O!4]m';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
//echo "Connected successfully";

?>
