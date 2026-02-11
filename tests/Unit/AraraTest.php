<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use Arara\Config;
use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
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

    private Arara $arara;

    protected function setUp(): void
    {
        $this->config = new Config(apiKey: 'test-key');
        $this->client = Mockery::mock(Client::class);
        $this->arara = new Arara($this->config, $this->client);
    }

    public function test_send_message_returns_decoded_response(): void
    {
        $body = ['id' => 'msg-123', 'status' => 'sent'];

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('messages', Mockery::type('array'))
            ->andReturn(new Response(200, [], (string) json_encode($body)));

        $result = $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');

        $this->assertSame($body, $result);
    }

    public function test_send_message_throws_validation_when_receiver_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O campo receiver é obrigatório.');

        $this->arara->sendMessage('', 'welcome');
    }

    public function test_send_message_throws_validation_when_receiver_whitespace(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O campo receiver é obrigatório.');

        $this->arara->sendMessage('   ', 'welcome');
    }

    public function test_send_message_throws_validation_when_receiver_invalid_format(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O receiver deve seguir o formato whatsapp:+<número>');

        $this->arara->sendMessage('5511999999999', 'welcome');
    }

    public function test_send_message_throws_validation_when_receiver_missing_prefix(): void
    {
        $this->expectException(ValidationException::class);

        $this->arara->sendMessage('+5511999999999', 'welcome');
    }

    public function test_send_message_throws_validation_when_template_name_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O campo templateName é obrigatório.');

        $this->arara->sendMessage('whatsapp:+5511999999999', '');
    }

    public function test_send_message_throws_authentication_exception_on_401(): void
    {
        $this->mockHttpError(401, '{"message":"Invalid API key"}');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid API key');

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
    }

    public function test_send_message_throws_bad_request_exception_on_400(): void
    {
        $this->mockHttpError(400, '{"message":"Bad request"}');

        $this->expectException(BadRequestException::class);

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
    }

    public function test_send_message_throws_not_found_exception_on_404(): void
    {
        $this->mockHttpError(404, '{"message":"Not found"}');

        $this->expectException(NotFoundException::class);

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
    }

    public function test_send_message_throws_validation_exception_on_422(): void
    {
        $this->mockHttpError(422, '{"message":"Unprocessable entity"}');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unprocessable entity');

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
    }

    public function test_send_message_throws_internal_server_exception_on_500(): void
    {
        $this->mockHttpError(500, '{"message":"Internal server error"}');

        $this->expectException(InternalServerException::class);

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
    }

    public function test_send_message_throws_arara_exception_on_unknown_status(): void
    {
        $this->mockHttpError(503, '{"message":"Service unavailable"}');

        $this->expectException(AraraException::class);
        $this->expectExceptionMessage('Service unavailable');

        $this->arara->sendMessage('whatsapp:+5511999999999', 'welcome');
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
