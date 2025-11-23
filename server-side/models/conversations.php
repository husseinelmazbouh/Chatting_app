<?php
require_once(__DIR__ . "/Model.php");
//hussein el mazbouh
class Conversation extends Model {
    private int $id;
    private ?string $title;     
    private string $chat_type;   
    private string $created_at;
    protected static string $table = "conversations";
    public function __construct(array $data) {
        $this->id = (int)$data["id"];
        $this->title = $data["title"] ?? null;
        $this->chat_type = $data["chat_type"] ?? 'private';
        $this->created_at = $data["created_at"];
    }
    public function getID() { 
        return $this->id; 
    }
    public function getTitle() { 
        return $this->title; 
    }
    public function getChatType() { 
        return $this->chat_type; 
    }
    public function getCreatedAt() { 
        return $this->created_at; 
    }
    public function toArray() {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "chat_type" => $this->chat_type,
            "created_at" => $this->created_at
        ];
    }
}
?>