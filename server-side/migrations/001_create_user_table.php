<?php
include("../connection/connection.php");

$sql = "CREATE TABLE IF NOT EXISTS users(
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL, 
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, 
    is_online TINYINT(1) DEFAULT 0,                     
    last_seen TIMESTAMP NULL,                           
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$query = $connection->prepare($sql);
$query->execute();
echo "Users table created! successfully\n";
?>