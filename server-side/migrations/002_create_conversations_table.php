<?php
include("../connection/connection.php");

$sql = "CREATE TABLE IF NOT EXISTS conversations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) DEFAULT NULL,        
    chat_type VARCHAR(50) DEFAULT 'private', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$query = $connection->prepare($sql);
$query->execute();
echo "massage table created! successfully\n";

?>