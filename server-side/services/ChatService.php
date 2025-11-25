<?php
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../models/conversations.php"; 
require_once __DIR__ . "/../models/messages.php";
require_once __DIR__ . "/../models/participants.php";

class ChatService {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getPrivateConversation(int $userOneId, int $userTwoId) {
        $sql = "SELECT p1.conversation_id 
                FROM participants p1 
                JOIN participants p2 ON p1.conversation_id = p2.conversation_id 
                WHERE p1.user_id = ? AND p2.user_id = ? 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $userOneId, $userTwoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (int)$row['conversation_id'];
        }

        $this->db->begin_transaction();
        try {
            $this->db->query("INSERT INTO conversations (chat_type) VALUES ('private')");
            $convId = $this->db->insert_id;

            $stmt = $this->db->prepare("INSERT INTO participants (conversation_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $convId, $userOneId);
            $stmt->execute();
            $stmt->bind_param("ii", $convId, $userTwoId);
            $stmt->execute();

            $this->db->commit();
            return $convId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function sendMessage(int $senderId, int $conversationId, string $text) {
        $sql = "INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iis", $conversationId, $senderId, $text);
        
        if ($stmt->execute()) {
            $id = $this->db->insert_id;
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

    public function getMessages(int $conversationId) {
        $sql = "SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = new Message($row);
        }
        return $messages;
    }

    public function markAsDelivered(int $conversationId, int $userId) {
        $sql = "UPDATE messages SET delivered_at = NOW() 
                WHERE conversation_id = ? AND sender_id != ? AND delivered_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        $stmt->execute();
    }

    public function markAsRead(int $conversationId, int $userId) {
        $sql = "UPDATE messages SET read_at = NOW() 
                WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $conversationId, $userId);
        $stmt->execute();
    }
    
    public function getUnreadMessagesText(int $conversationId, int $currentUserId){
        $sql = "SELECT message FROM messages 
                WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL 
                ORDER BY created_at ASC";
                
        $stmt = $this->db->prepare($sql);
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