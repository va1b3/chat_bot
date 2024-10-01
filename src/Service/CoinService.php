<?php

use App\Service\AbstractService;

class CoinService extends AbstractService {

    private string $heads = 'https://calculat.ru/wp-content/themes/EmptyCanvas/img/orel.png';
    private string $tails = 'https://calculat.ru/wp-content/themes/EmptyCanvas/img/reshka.png';
    private string $rand;

    #[\Override]
    public function response(): void {
        $this->getRandom();
        $this->sendPhoto($this->rand == 0 ? $this->heads : $this->tails);
    }
    
    #[\Override]
    public function test(): string {
        $this->getRandom();
        return $this->rand == 0 ? $this->heads : $this->tails;
    }
    
    public function getRandom(): void {
        
        $this->rand = random_int(1, 100) % 2;
    }
}
