<?php
require_once(__DIR__ . "/Model.php");

class Participant extends Model {
    private int $id;
    private int $conversation_id;
    private int $user_id;
    private string $joined_at;

    protected static string $table = "participants";

    public function __construct(array $data) {
        $this->id = (int)$data["id"];
        $this->conversation_id = (int)$data["conversation_id"];
        $this->user_id = (int)$data["user_id"];
        $this->joined_at = $data["joined_at"];
    }

    public function getID() { 
        return $this->id; 
    }
    public function getConversationID() { 
        return $this->conversation_id; 
    }
    public function getUserID() { 
        return $this->user_id; 
    }
    public function getJoinedAt() { 
        return $this->joined_at; 
    }

    public function toArray() {
        return [
            "id" => $this->id,
            "conversation_id" => $this->conversation_id,
            "user_id" => $this->user_id,
            "joined_at" => $this->joined_at
        ];
    }
}
?>