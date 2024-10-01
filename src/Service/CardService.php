<?php

use App\Service\AbstractService;

class CardService extends AbstractService {
    
    private string $url = 'https://deckofcardsapi.com/api/deck/new/draw/?count=1';
    private string $card;

    #[\Override]
    public function response(): void {
        $this->getCard();
        $this->sendPhoto($this->card);
    }
    
    #[\Override]
    public function test(): string {
        $this->getCard();
        return $this->card;
    }
    
    private function getCard(): void {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        $this->card = $response['cards'][0]['image'] ?? ':(';
    }
}
