<?php
require_once(__DIR__ . "/config.php");

class AIService {
    private string $apiKey;
    public function __construct() {
        global $config; 
        $this->apiKey = $config['openai_api_key'] ?? '';
    }
    public function getSummary(string $messageHistory): string {
        //Input Check
        if (empty(trim($messageHistory))) {
            return "No messages available to summarize.";
        }
        $prompt = "Summarize the following chat conversation concisely in 3 sentences or less:\n\n" . $messageHistory;
        return $this->callOpenAI($prompt);
    }
    
    private function callOpenAI(string $prompt): string {
        //Config Check
        if (empty($this->apiKey)) {
            return "Error:API Key is missing.";
        }
        $url = "https://api.openai.com/v1/chat/completions";
        
        $data = [
            "model" => "gpt-3.5-turbo", 
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant. Keep summaries safe, clean, and concise."],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.5,
            "max_tokens" => 500
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $this->validateResponse($response, $httpCode);
    }

    private function validateResponse(string $rawResponse, int $httpCode): string {
        $decoded = json_decode($rawResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Error: Invalid response format from AI.";
        }

        if (!isset($decoded['choices'][0]['message']['content'])) {
            return json_encode(['error' => "Error: AI returned an empty response."]);
        }
        $content = $decoded['choices'][0]['message']['content'];

        if (trim($content) === "") {
            return json_encode(['error' => "AI could not generate a summary."]);
        }
        return json_encode(['summary' => $content]);
    }
}
?>