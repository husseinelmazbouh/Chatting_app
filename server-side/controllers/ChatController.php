<?php
require_once __DIR__ . "/../services/ChatService.php";
require_once __DIR__ . "/../connection/connection.php";
require_once __DIR__ . "/../middleware/AuthMiddleware.php";
require_once __DIR__ . "/../services/ResponseService.php";

class ChatController {
    private ChatService $chatService;

    public function __construct() {
        global $connection;
        $this->chatService = new ChatService($connection);
    }

    public function openChat() {
        $userId = authenticateParams(); 
        $targetId = (int)$_POST['target_user_id'];
        
        $id = $this->chatService->getPrivateConversation($userId, $targetId);
        echo ResponseService::response(200, ["conversation_id" => $id]);
    }

    public function sendMessage() {
        $userId = authenticateParams();
        $convId = (int)$_POST['conversation_id'];
        $msg = trim($_POST['message']);

        if(empty($msg)) {
            echo ResponseService::response(400, "Empty message");
            return;
        }

        $res = $this->chatService->sendMessage($userId, $convId, $msg);
        echo ResponseService::response(200, ["data" => $res->toArray()]);
    }

    public function getMessages() {
        $userId = authenticateParams();
        $convId = (int)$_GET['conversation_id'];

        $this->chatService->markAsDelivered($convId, $userId);
        $msgs = $this->chatService->getMessages($convId);

        $data = [];
        foreach($msgs as $m) {
            $arr = $m->toArray();
            $arr['is_mine'] = ($m->getSenderID() == $userId);
            $arr['status_text'] = $m->getStatus();
            $data[] = $arr;
        }
        echo ResponseService::response(200, ["data" => $data]);
    }

    public function markRead() {
        $userId = authenticateParams();
        $convId = (int)$_POST['conversation_id'];
        
        $this->chatService->markAsRead($convId, $userId);
        echo ResponseService::response(200, "Marked as read");
    }
}
?>