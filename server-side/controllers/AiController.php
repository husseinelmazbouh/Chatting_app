<?php
require_once __DIR__ . "/../services/ChatService.php";
require_once __DIR__ . "/../services/AIService.php"; 
require_once __DIR__ . "/../middleware/AuthMiddleware.php";
require_once __DIR__ . "/../services/ResponseService.php";

class AiController {
    private ChatService $chatService;
    private AIService $aiService;

    public function __construct() {
        global $connection;
        $this->chatService = new ChatService($connection);
        $this->aiService = new AIService();
    }

    public function getSummary() {
        $userId = authenticateParams();
        $convId = (int)$_POST['conversation_id'];

        $msgs = $this->chatService->getUnreadMessagesText($convId, $userId);

        if (empty($msgs)) {
            echo ResponseService::response(200, ["summary" => "No unread messages."]);
            return;
        }

        $text = implode("\n", $msgs);
        
        echo $this->aiService->getSummary($text);
    }
}
?>