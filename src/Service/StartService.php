<?php

use App\Service\AbstractService;

class StartService extends AbstractService {
    
    private string $answer = 'https://http.cat/200';

    #[\Override]
    public function response(): void {
        $this->sendPhoto($this->answer);
    }

    #[\Override]
    public function test(): string {
        return $this->answer;
    }
}
