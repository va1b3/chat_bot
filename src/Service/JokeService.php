<?php

use App\Service\AbstractService;

class JokeService extends AbstractService {
    
    private string $url = 'http://rzhunemogu.ru/Rand.aspx?CType=1';
    private string $joke;

    #[\Override]
    public function response(): void {
        $this->getJoke();
        $this->sendMessage($this->joke);
    }
    
    #[\Override]
    public function test(): string {
        $this->getJoke();
        return $this->joke;
    }

    /*
     * URL returns XML
     * Windows-1251 encoding
     */
    private function getJoke(): void {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $this->joke = strip_tags(iconv('windows-1251', 'utf-8', curl_exec($curl)));
        curl_close($curl);
    }
}
