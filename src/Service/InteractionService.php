<?php

namespace App\Service;

use App\Service\AbstractService;

class InteractionService extends AbstractService {
    
    private string $urlFact = 'https://randstuff.ru/fact/generate/';
    private string $urlConfirm = 'https://yesno.wtf/api?force=';
    private int $chanceFact = 5;
    private int $chanceConfirm = 10;
    private array $intros = [
        'Хммм...любопытно 🤔. ',
        'А вы знали что...',
        'ХЕХЕ, забавный факт: ',
        'Никогда бы не подумал. ',
        '🤓🤓🤓'. PHP_EOL];

    #[\Override]
    public function response(): void {
        $answer = $this->getAnswer();
        if (!is_null($answer)) {
            if (stripos('https', $answer) === false) {
                $this->sendMessage($answer);
            } else {
                $this->sendPhoto($answer);
            }
        }
    }

    #[\Override]
    public function test(): string {
        $answer = $this->getAnswer();
        return is_null($answer) ? '' : $answer;
    }

    private function getAnswer(): string|null {
        $confirm = null;
        if (preg_match('/\bда\b/ui', $this->message)) {          
            $confirm = 'yes';
        } elseif (preg_match('/\bнет\b/ui', $this->message)) {
            $confirm = 'no';
        }
        if (is_null($confirm)) {   
             return rand(1, 100) <= $this->chanceFact 
                    ? $this->intros[rand(0, count($this->intros) - 1)] . $this->getFact() 
                    : null;
        } else {
            return rand(1, 100) <= $this->chanceConfirm 
                    ? $this->getConfirm($confirm)
                    : null; 
        }
    }

    private function getConfirm(string $confirm): string {
        $curl = curl_init($this->urlConfirm . $confirm);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($curl), true);
        return $response['image'];
    }
    
    private function getFact(): string {
        $curl = curl_init($this->urlFact);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest']);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response['fact']['text'];
    }
}
