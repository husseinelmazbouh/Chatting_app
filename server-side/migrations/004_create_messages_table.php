<?php
include("../connection/connection.php");

$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT(11) NOT NULL,
    sender_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,      
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (sender_id) REFERENCES users(id)
)";

$query = $connection->prepare($sql);
$query->execute();
echo "messages table created! successfully\n";
?>