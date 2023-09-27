<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BitrixService
{
    private $httpClient;
    private $bitrixWebhookUrl;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->bitrixWebhookUrl = "";
    }

    public function call(string $method,array $data):array
    {
        if(empty($this->bitrixWebhookUrl)){
            throw new BadRequestException("Bitrix reject connection.");
        }
        $response = $this->httpClient->request(
            'POST', $this->bitrixWebhookUrl."/".$method, $data);

        return $response->toArray();
    }
}
