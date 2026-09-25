<?php

declare(strict_types=1);

namespace Arara\Tests\Support;

use Arara\Arara;
use Arara\Config;
use Arara\Http\RetryPolicy;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Client Guzzle real com MockHandler, retry do SDK e histórico das requests enviadas.
 */
final class RecordingClient
{
    /** @var array<int, array{request: RequestInterface}> */
    public array $history = [];

    public readonly Client $client;

    /**
     * @param array<int, ResponseInterface|\Throwable> $queue
     */
    public function __construct(array $queue, ?Config $config = null)
    {
        $config ??= new Config(apiKey: 'test-key', retryDelayMs: 0);
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::retry(RetryPolicy::decider($config), RetryPolicy::delay($config)));
        $stack->push(Middleware::history($this->history));

        $this->client = new Client([
            'base_uri' => Arara::baseUri($config),
            'handler' => $stack,
        ]);
    }

    public function request(int $index): RequestInterface
    {
        return $this->history[$index]['request'];
    }

    public function count(): int
    {
        return count($this->history);
    }
}
