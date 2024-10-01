<?php

namespace App\Service;

abstract class AbstractService {

    public string $message;
    private int $chatID;
    
    public function __construct(string $message, int|string $chatID) {
        $this->message = $message;
        $this->chatID = $chatID;
    }

    abstract public function response(): void;
    abstract public function test(): string;

    public function sendMessage(string $text): int|bool {
        $response = $this->telegramRequest(
                ['chat_id' => $this->chatID, 'text' => $text],
                'sendMessage'
        );
        return $response['result']['message_id'] ?? false;
    }

    public function sendPhoto(string $photo): int|bool {
        $response = $this->telegramRequest(
                ['chat_id' => $this->chatID, 'photo' => $photo], 
                'sendPhoto'
        );
        return $response['result']['message_id'] ?? false;
    }

    public function pinChatMessage(int|string $messageID): void {
        $this->telegramRequest(
                ['chat_id' => $this->chatID, 'message_id' => $messageID], 
                'pinChatMessage'
        );
    }

    public function unpinAllChatMessages(): void {
        $this->telegramRequest(
                ['chat_id' => $this->chatID], 
                'unpinAllChatMessages'
        );
    }
    
    private function telegramRequest(array $query, string $method): array {
        $curl = curl_init('https://api.telegram.org/bot' . $_ENV['TG_TOKEN'] . 
                '/' . $method . '?' . http_build_query($query));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response;
    }
}
