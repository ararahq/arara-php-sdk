<?php

declare(strict_types=1);

namespace Arara;

use Arara\Resources\Messages;
use Arara\Resources\Organizations;
use Arara\Resources\Templates;
use Arara\Resources\Users;
use GuzzleHttp\Client;

final class Arara
{
    public readonly Messages $messages;
    public readonly Templates $templates;
    public readonly Users $users;
    public readonly Organizations $organizations;

    public function __construct(Config $config, ?Client $http = null)
    {
        $client = $http ?? new Client([
            'base_uri' => "{$config->baseUrl}/api/{$config->apiVersion}/",
            'timeout' => $config->timeout,
            'headers' => [
                'Authorization' => "Bearer {$config->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Arara-PHP-SDK/1.7.1',
            ],
        ]);

        $this->messages = new Messages($client);
        $this->templates = new Templates($client);
        $this->users = new Users($client);
        $this->organizations = new Organizations($client);
    }
}
