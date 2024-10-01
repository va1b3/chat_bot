<?php

namespace App;

class Translator {
    
    private string $url = 'https://translate.googleapis.com/translate_a/single?'
            . 'client=gtx&dj=1&dt=t&hl=ru&ie=UTF-8&kc=7&oe=UTF-8'
            . '&otf=1&sl=en&source=bubble&ssel=0&tl=ru&tsel=0&q=';
    private string $text = '';
    
    public function __construct(string $text) {
        $this->text = $text;
    }
    
    public function translate(): string {
        $curl = curl_init($this->url . urlencode($this->text));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        return $response['sentences'][0]['trans'];
    }
}
