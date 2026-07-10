<?php

declare(strict_types=1);

namespace Arara\Tests\Unit\Http;

use Arara\Config;
use Arara\Http\RetryPolicy;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class RetryPolicyTest extends TestCase
{
    private Config $config;

    private Request $request;

    protected function setUp(): void
    {
        $this->config = new Config(apiKey: 'test-key', retryTimes: 3, retryDelayMs: 1);
        $this->request = new Request('GET', 'templates');
    }

    public function test_decider_retries_on_connection_error(): void
    {
        $decider = RetryPolicy::decider($this->config);

        $this->assertTrue($decider(0, $this->request, null, new ConnectException('timeout', $this->request)));
    }

    public function test_decider_retries_on_server_errors_and_rate_limit(): void
    {
        $decider = RetryPolicy::decider($this->config);

        $this->assertTrue($decider(0, $this->request, new Response(500)));
        $this->assertTrue($decider(0, $this->request, new Response(503)));
        $this->assertTrue($decider(0, $this->request, new Response(429)));
    }

    public function test_decider_does_not_retry_on_client_errors(): void
    {
        $decider = RetryPolicy::decider($this->config);

        $this->assertFalse($decider(0, $this->request, new Response(400)));
        $this->assertFalse($decider(0, $this->request, new Response(422)));
        $this->assertFalse($decider(0, $this->request, new Response(200)));
    }

    public function test_decider_stops_after_configured_retries(): void
    {
        $decider = RetryPolicy::decider($this->config);

        $this->assertFalse($decider(3, $this->request, new Response(500)));
    }

    public function test_delay_uses_exponential_backoff_from_config(): void
    {
        $delay = RetryPolicy::delay($this->config);

        $this->assertSame(1, $delay(1));
        $this->assertSame(2, $delay(2));
        $this->assertSame(4, $delay(3));
    }

    public function test_delay_honors_retry_after_header_in_seconds(): void
    {
        $delay = RetryPolicy::delay($this->config);

        $this->assertSame(2000, $delay(1, new Response(429, ['Retry-After' => '2'])));
    }

    public function test_client_with_retry_middleware_recovers_from_transient_errors(): void
    {
        $mock = new MockHandler([
            new Response(500),
            new Response(429, ['Retry-After' => '0']),
            new Response(200, [], '{"ok":true}'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::retry(
            RetryPolicy::decider($this->config),
            RetryPolicy::delay($this->config),
        ));
        $client = new Client(['handler' => $stack]);

        $response = $client->get('templates');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(0, $mock->count());
    }
}
