<?php
require_once(__DIR__ . "/Model.php");
//hussein el mazbouh
class Message extends Model {
    private int $id;
    private int $conversation_id;
    private int $sender_id;
    private string $message;
    private string $created_at;
    private ?string $delivered_at; 
    private ?string $read_at;    

    protected static string $table = "messages";

    public function __construct(array $data) {
        $this->id = (int)$data["id"];
        $this->conversation_id = (int)$data["conversation_id"];
        $this->sender_id = (int)$data["sender_id"];
        $this->message = $data["message"];
        $this->created_at = $data["created_at"];
        $this->delivered_at = isset($data["delivered_at"]) ? $data["delivered_at"] : null;
        $this->read_at = isset($data["read_at"]) ? $data["read_at"] : null;
    }

    public function getID() { 
        return $this->id; 
    }
    public function getConversationID() { 
        return $this->conversation_id; 
    }
    public function getSenderID() { 
        return $this->sender_id; 
    }
    public function getMessage() { 
        return $this->message; 
    }
    public function getCreatedAt() { 
        return $this->created_at; 
    }
    public function getDeliveredAt() { 
        return $this->delivered_at; 
    }
    public function getReadAt() { 
        return $this->read_at; 
    }
    public function getStatus() {
        if ($this->read_at) return "read";
        if ($this->delivered_at) return "delivered";
        return "sent";
    }

    public function toArray() {
        return [
            "id" => $this->id,
            "conversation_id" => $this->conversation_id,
            "sender_id" => $this->sender_id,
            "message" => $this->message,
            "created_at" => $this->created_at,
            "delivered_at" => $this->delivered_at,
            "read_at" => $this->read_at
        ];
    }
}
?>