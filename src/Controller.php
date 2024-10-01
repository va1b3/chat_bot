<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\InteractionService;
use App\Service\NotFoundService;
use App\Service\ChatgptService;
use Dotenv\Dotenv;

/*
 * Loads environment variables from .env to $_ENV
 */
Dotenv::createImmutable(__DIR__ . '/../')->load();


/*
 * Sets default fimezone from .env
 */
date_default_timezone_set($_ENV['TIMEZONE']);


/*
 * Gets request via php://input stream
 */
$request =(new Request)->getContent();
parse_str($request, $data);


/*
 * Checks for required request fields
 * Message: message -> text
 * ChatID:  message -> chat -> id
 * Type:    message -> entities -> 0 -> type
 */
if (!isset($data['message']['text']) || !isset($data['message']['chat']['id']) 
        || !isset($data['message']['entities'][0]['type'])) {
    $response = new Response('Bad Request', 400);
    $response->send();
    exit;  
}


/*
 * Service loader for any type of interaction
 */
switch ($data['message']['entities'][0]['type']):
    /*
     * Command execution
     * Message starts with "/"
     * Requires searched service from /Service
     */
    case 'bot_command':
        $message = trim($data['message']['text'], '/');
        $serviceName = ucfirst(explode(' ', $message)[0]);
        $servicePath = __DIR__ . '/Service/' . $serviceName . 'Service.php';    
        if (file_exists($servicePath)) {  
            require_once $servicePath;
            $service = new ($serviceName . 'Service')($message, $data['message']['chat']['id']);
        } else {
            $service = new NotFoundService($message, $data['message']['chat']['id']);
        }
        break;
    /*
     * ChatGPT interaction
     * Message starts with "@"
     */
    case 'mention':
        $message = trim($data['message']['text'], '@');
        $service = new ChatgptService($message, $data['message']['chat']['id']);
        break;
    /*
     * Participation in conversation
     */
    default:
        $service = new InteractionService($data['message']['text'], 
                $data['message']['chat']['id']);
endswitch;


/*
 * Service response
 * Method test() to check output
 */
# echo $service->test();
$service->response();
