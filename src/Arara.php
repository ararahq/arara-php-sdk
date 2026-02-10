<?php

declare(strict_types=1);

namespace Arara;

use GuzzleHttp\Client;

final class Arara
{
    private readonly Config $config;

    private readonly Client $client;

    public function __construct(Config $config, ?Client $http = null,)
    {
        $this->config = $config;
        $this->client = $http ?? new Client([
            'base_uri' => $config->baseUrl,
            'timeout' => $config->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $config->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function sendMessage(string $receiver, string $templateName, array $variables = []): array
    {
        $response = $this->client->post('/messages', [
            'json' => [
                'receiver' => $receiver,
                'templateName' => $templateName,
                'variables' => $variables,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}