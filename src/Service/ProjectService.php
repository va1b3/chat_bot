<?php

use App\Service\AbstractService;
use App\Translator;

class ProjectService extends AbstractService {
    
    private string $url = 'https://itsthisforthat.com/api.php?json';
    private Translator $project;

    #[\Override]
    public function response(): void {
        $this->getProject();
        $this->sendMessage($this->project->translate());
    }

    #[\Override]
    public function test(): string {
        $this->getProject();
        return $this->project->translate();
    }
    
    private function getProject(): void {
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        $this->project = new Translator($response['this'] . ' for ' . $response['that']);
    }
}
