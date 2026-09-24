<?php

declare(strict_types=1);

namespace Arara;

use Arara\Http\RetryPolicy;
use Arara\Resources\Auth;
use Arara\Resources\Campaigns;
use Arara\Resources\Contacts;
use Arara\Resources\Conversations;
use Arara\Resources\Messages;
use Arara\Resources\Numbers;
use Arara\Resources\OptOuts;
use Arara\Resources\SmartLinks;
use Arara\Resources\Templates;
use Arara\Resources\Wallet;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

final class Arara
{
    public const VERSION = '2.0.0';

    public readonly Messages $messages;

    public readonly Templates $templates;

    public readonly Contacts $contacts;

    public readonly Conversations $conversations;

    public readonly Wallet $wallet;

    public readonly Numbers $numbers;

    public readonly SmartLinks $smartLinks;

    public readonly Campaigns $campaigns;

    public readonly OptOuts $optOuts;

    public readonly Auth $auth;

    public function __construct(Config $config, ?Client $http = null)
    {
        $client = $http ?? self::createClient($config);

        $this->messages = new Messages($client);
        $this->templates = new Templates($client);
        $this->contacts = new Contacts($client);
        $this->conversations = new Conversations($client);
        $this->wallet = new Wallet($client);
        $this->numbers = new Numbers($client);
        $this->smartLinks = new SmartLinks($client);
        $this->campaigns = new Campaigns($client);
        $this->optOuts = new OptOuts($client);
        $this->auth = new Auth($client);
    }

    public function auth(): Auth
    {
        return $this->auth;
    }

    private static function createClient(Config $config): Client
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            RetryPolicy::decider($config),
            RetryPolicy::delay($config),
        ));

        return new Client([
            'base_uri' => "{$config->baseUrl}/{$config->apiVersion}/",
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
