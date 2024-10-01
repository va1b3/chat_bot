<?php

namespace App\Service;

use App\Service\AbstractService;

class NotFoundService extends AbstractService {
    
    private string $answer = 'https://http.cat/404';
    
    #[\Override]
    public function response(): void {
        $this->sendPhoto($this->answer);
    }

    #[\Override]
    public function test(): string {
        return $this->answer;
    }
}
