<?php
include("../connection/connection.php");

$sql ="CREATE TABLE IF NOT EXISTS participants (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

$query = $connection->prepare($sql);
$query->execute();
echo "participants table created! successfully\n";

?>