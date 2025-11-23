<?php
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../models/conversation.php";
require_once __DIR__ . "/../models/message.php";
require_once __DIR__ . "/../models/participant.php";

class ChatService {

    public function getPrivateConversation(int $userOneId, int $userTwoId): int {
        global $connection; 

        //Check if conversation exists 
        $sql = "SELECT p1.conversation_id 
                FROM participants p1 
                JOIN participants p2 ON p1.conversation_id = p2.conversation_id 
                WHERE p1.user_id = ? AND p2.user_id = ? 
                LIMIT 1";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $userOneId, $userTwoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (int)$row['conversation_id'];
        }

        // If no chat exists, create one 
        $connection->query("START TRANSACTION");
        try {
            $connection->query("INSERT INTO conversations (chat_type) VALUES ('private')");
            $convId = $connection->insert_id;

            $stmt = $connection->prepare("INSERT INTO participants (conversation_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $convId, $userOneId);
            $stmt->execute();
            
            $stmt->bind_param("ii", $convId, $userTwoId);
            $stmt->execute();

            $connection->query("COMMIT");
            return $convId;

        } catch (Exception $e) {
            $connection->query("ROLLBACK");
            throw $e;
        }
    }

    //Send a Message
    public function sendMessage(int $senderId, int $conversationId, string $text): ?Message {
        global $connection;

        $sql = "INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("iis", $conversationId, $senderId, $text);
        
        if ($stmt->execute()) {
            $id = $connection->insert_id;
            return new Message([
                "id" => $id,
                "conversation_id" => $conversationId,
                "sender_id" => $senderId,
                "message" => $text,
                "created_at" => date('Y-m-d H:i:s'),
                "delivered_at" => null,
                "read_at" => null
            ]);
        }
        return null;
    }

    //Fetch Chat History
    public function getMessages(int $conversationId): array {
        global $connection;

        $sql = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = new Message($row);
        }
        return $messages;
    }

    // Mark messages as Delivered
    public function markAsDelivered(int $conversationId, int $userId) {
        global $connection;
        $sql = "UPDATE messages SET delivered_at = NOW() 
                WHERE conversation_id = ? 
                AND sender_id != ? 
                AND delivered_at IS NULL";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        $stmt->execute();
    }

    //Mark messages as Read (Blue Tick)
    public function markAsRead(int $conversationId, int $userId) {
        global $connection;

        $sql = "UPDATE messages SET read_at = NOW() 
                WHERE conversation_id = ? 
                AND sender_id != ? 
                AND read_at IS NULL";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        $stmt->execute();
    }
    
    //Get unread text for AI
    public function getUnreadMessagesText(int $conversationId, int $currentUserId): array {
        global $connection;

        $sql = "SELECT message FROM messages 
                WHERE conversation_id = ? 
                AND sender_id != ? 
                AND read_at IS NULL 
                ORDER BY created_at ASC";
                
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $currentUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $texts = [];
        while ($row = $result->fetch_assoc()) {
            $texts[] = $row['message'];
        }
        return $texts;
    }
}
?>