<?php
require_once __DIR__ . "/../../services/ChatService.php";
require_once __DIR__ . "/../../services/AIService.php";
require_once __DIR__ . "/../../middleware/AuthMiddleware.php";
//hussein el mazbouh
class AiController {
    private ChatService $chatService;
    private AIService $aiService;

    public function __construct() {
        $this->chatService = new ChatService();
        $this->aiService = new AIService();
    }

    public function getSummary() {
        $userId = authenticateParams();
        $convId = (int)$_POST['conversation_id'];

        //Get Text
        $msgs = $this->chatService->getUnreadMessagesText($convId, $userId);

        if (empty($msgs)) {
            echo json_encode(["summary" => "No unread messages."]);
            return;
        }

        $text = implode("\n", $msgs);

        //Call AI
        echo $this->aiService->getSummary($text);
    }
}
?>