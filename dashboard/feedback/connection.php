<?php
//old
// $host = 'localhost';
// $dbname = 'beatme_pmc_database'; 
// $username = 'beatme_pmc';
// $password = '&r(x0xzIuoOS';


//new 
$host = '160.187.5.190';
$dbname = 'pmcbeatlemeco_db'; 
$username = 'pmcbeatlemeco_user';
$password = 'Aksh@9412';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
// echo "Connected successfully";

///live database 
// $host = '101.53.136.253';
// $dbname = 'baris_db'; 
// $username = 'beatle_live';
// $password = 'Htrahdis@9876';

// $conn = new mysqli($host, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// $conn->set_charset("utf8");
// echo "Connected successfully";



?>
