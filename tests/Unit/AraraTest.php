<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use Arara\Config;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\RateLimitException;
use Arara\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class AraraTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Config $config;

    private Mockery\MockInterface&Client $client;

    private Arara $sdk;

    protected function setUp(): void
    {
        $this->config = new Config(apiKey: 'test-key');
        $this->client = Mockery::mock(Client::class);
        $this->sdk = new Arara($this->config, $this->client);
    }

    public function test_send_message_returns_decoded_response(): void
    {
        $body = ['id' => 'msg-123', 'status' => 'sent'];

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('messages', Mockery::type('array'))
            ->andReturn(new Response(200, [], (string) json_encode($body)));

        $result = $this->sdk->messages->send('whatsapp:+5511987654321', 'welcome');

        $this->assertSame($body, $result);
    }

    public function test_send_message_throws_validation_when_receiver_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The receiver field is required.');

        $this->sdk->messages->send('', 'welcome');
    }

    public function test_send_message_throws_validation_when_receiver_whitespace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The receiver field is required.');

        $this->sdk->messages->send('   ', 'welcome');
    }

    public function test_send_message_throws_validation_when_receiver_invalid_format(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The receiver must follow the format whatsapp:+<number>');

        $this->sdk->messages->send('5511987654321', 'welcome');
    }

    public function test_send_message_throws_validation_when_template_name_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The templateName field is required.');

        $this->sdk->messages->send('whatsapp:+5511987654321', '');
    }

    public function test_send_message_throws_authentication_exception_on_401(): void
    {
        $this->mockHttpError(401, '{"message":"Invalid API key"}');

        $this->expectException(AuthenticationException::class);
        // We no longer check for exact message in handleException logic in this SDK
        // but the types must match.

        $this->sdk->messages->send('whatsapp:+5511987654321', 'welcome');
    }

    public function test_send_message_throws_bad_request_exception_on_400(): void
    {
        $this->mockHttpError(400, '{"message":"Bad request"}');

        $this->expectException(BadRequestException::class);

        $this->sdk->messages->send('whatsapp:+5511987654321', 'welcome');
    }

    public function test_send_message_throws_rate_limit_exception_on_429_with_retry_after(): void
    {
        $response = new Response(429, ['Retry-After' => '7'], '{"error":{"code":"RATE_LIMITED","message":"Too many requests"}}');
        $request = new Request('POST', 'messages');

        $this->client
            ->shouldReceive('post')
            ->once()
            ->andThrow(RequestException::create($request, $response));

        try {
            $this->sdk->messages->send('whatsapp:+5511987654321', 'welcome');
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertSame(429, $e->statusCode);
            $this->assertSame(7, $e->retryAfter);
            $this->assertSame('RATE_LIMITED', $e->errorCode);
            $this->assertSame('Too many requests', $e->getMessage());
        }
    }

    private function mockHttpError(int $statusCode, string $body): void
    {
        $response = new Response($statusCode, [], $body);
        $request = new Request('POST', 'messages');
        $exception = RequestException::create($request, $response);

        $this->client
            ->shouldReceive('post')
            ->once()
            ->andThrow($exception);
    }
}
