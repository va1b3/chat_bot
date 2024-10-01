<?php

namespace App\Service;

use App\Service\AbstractService;

class ChatgptService extends AbstractService {
    
    private string $url = 'https://api.openai.com/v1/chat/completions';
    private string $model = 'gpt-3.5-turbo';
    private int $messageLenght = 4096;

    #[\Override]
    public function response(): void {
        $answer = $this->getAnswer();
        $length = strlen($answer);
        for ($i = 0; $i < $length; $i += $this->messageLenght) {
            sendMessage(substr($answer, $i, $this->messageLenght));
        }
    }

    #[\Override]
    public function test(): string {
        return $this->getAnswer();
    }
    
    private function getAnswer(): string {
        $answer = $this->chatGptRequest([
            'model' => $this->model, 
            'messages' => [
                'role' => 'user', 
                'content' => $this->message, 
                'presence_penalty' => -1.1]]);
        return isset($answer['choices'][0]['message']['content']) 
            ? $answer['choices'][0]['message']['content'] 
            : '-';
    }

    private function chatGptRequest(array $data): array {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, 
                ['Content-Type: application/json', 
                'Authorization: Bearer ' . $_ENV['CHATGPT_APIKEY']]);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response;
    }
}
