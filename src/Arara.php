<?php

declare(strict_types=1);

namespace Arara;

use Arara\Http\RetryPolicy;
use Arara\Resources\Messages;
use Arara\Resources\Organizations;
use Arara\Resources\Templates;
use Arara\Resources\Users;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

final class Arara
{
    public const VERSION = '1.8.1';

    public readonly Messages $messages;

    public readonly Templates $templates;

    public readonly Users $users;

    public readonly Organizations $organizations;

    public function __construct(Config $config, ?Client $http = null)
    {
        $client = $http ?? self::createClient($config);

        $this->messages = new Messages($client);
        $this->templates = new Templates($client);
        $this->users = new Users($client);
        $this->organizations = new Organizations($client);
    }

    private static function createClient(Config $config): Client
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            RetryPolicy::decider($config),
            RetryPolicy::delay($config),
        ));

        return new Client([
            'base_uri' => "{$config->baseUrl}/api/{$config->apiVersion}/",
            'handler' => $stack,
            'timeout' => $config->timeout,
            'headers' => [
                'Authorization' => "Bearer {$config->apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Arara-PHP-SDK/' . self::VERSION,
            ],
        ]);
    }
}
