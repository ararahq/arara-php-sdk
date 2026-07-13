<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use Arara\Config;
use Arara\Exceptions\NotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

final class TemplatesTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Mockery\MockInterface&Client $client;

    private Arara $sdk;

    protected function setUp(): void
    {
        $this->client = Mockery::mock(Client::class);
        $this->sdk = new Arara(new Config(apiKey: 'test-key'), $this->client);
    }

    public function test_list_calls_client_once_and_returns_decoded_response(): void
    {
        $body = ['data' => [['name' => 'welcome', 'status' => 'approved']]];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('templates', [])
            ->andReturn(new Response(200, [], (string) json_encode($body)));

        $this->assertSame($body, $this->sdk->templates->list());
    }

    public function test_get_calls_client_once_and_returns_decoded_response(): void
    {
        $body = ['name' => 'welcome', 'status' => 'approved'];

        $this->client
            ->shouldReceive('get')
            ->once()
            ->with('templates/welcome', [])
            ->andReturn(new Response(200, [], (string) json_encode($body)));

        $this->assertSame($body, $this->sdk->templates->get('welcome'));
    }

    public function test_delete_calls_client_once_and_returns_decoded_response(): void
    {
        $body = ['deleted' => true];

        $this->client
            ->shouldReceive('delete')
            ->once()
            ->with('templates/welcome', [])
            ->andReturn(new Response(200, [], (string) json_encode($body)));

        $this->assertSame($body, $this->sdk->templates->delete('welcome'));
    }

    public function test_create_posts_payload_and_returns_decoded_response(): void
    {
        $data = ['name' => 'welcome', 'body' => 'Ola {{1}}'];
        $body = ['name' => 'welcome', 'status' => 'pending'];

        $this->client
            ->shouldReceive('post')
            ->once()
            ->with('templates', ['json' => $data])
            ->andReturn(new Response(201, [], (string) json_encode($body)));

        $this->assertSame($body, $this->sdk->templates->create($data));
    }

    public function test_get_maps_404_with_nested_envelope_to_not_found_exception(): void
    {
        $response = new Response(404, [], '{"error":{"code":"TEMPLATE_NOT_FOUND","message":"Template not found","details":{}}}');
        $request = new Request('GET', 'templates/missing');

        $this->client
            ->shouldReceive('get')
            ->once()
            ->andThrow(RequestException::create($request, $response));

        try {
            $this->sdk->templates->get('missing');
            $this->fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame(404, $e->statusCode);
            $this->assertSame('TEMPLATE_NOT_FOUND', $e->errorCode);
            $this->assertSame('Template not found', $e->getMessage());
        }
    }
}
