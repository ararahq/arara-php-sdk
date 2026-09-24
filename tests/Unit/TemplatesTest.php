<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Exceptions\NotFoundException;
use Arara\Resources\Templates;
use Arara\Tests\Support\RecordingClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class TemplatesTest extends TestCase
{
    private const ID = '3f1c2a9e-8b7d-4c21-9f3a-1b2c3d4e5f60';

    public function test_list_returns_paginated_response_and_sends_filters(): void
    {
        $body = [
            'data' => [['id' => self::ID, 'name' => 'welcome'], 'lixo'],
            'pagination' => ['page' => 0, 'size' => 50, 'totalElements' => 51, 'totalPages' => 2],
        ];
        $http = new RecordingClient([new Response(200, [], (string) json_encode($body))]);

        $page = (new Templates($http->client))->list(status: 'APPROVED', name: 'welcome');

        $this->assertSame([['id' => self::ID, 'name' => 'welcome']], $page->data);
        $this->assertSame(51, $page->pagination->totalElements);
        $this->assertSame(2, $page->pagination->totalPages);
        $this->assertTrue($page->hasNextPage());
        parse_str($http->request(0)->getUri()->getQuery(), $query);
        $this->assertSame(['page' => '0', 'size' => '50', 'name' => 'welcome', 'status' => 'APPROVED'], $query);
    }

    public function test_get_status_and_delete_address_the_template_by_id(): void
    {
        $http = new RecordingClient([
            new Response(200, [], '{"id":"x"}'),
            new Response(200, [], '{"status":"APPROVED"}'),
            new Response(204),
        ]);
        $templates = new Templates($http->client);

        $templates->get(self::ID);
        $templates->getStatus(self::ID);
        $this->assertSame([], $templates->delete(self::ID));

        $this->assertSame('/v1/templates/' . self::ID, $http->request(0)->getUri()->getPath());
        $this->assertSame('/v1/templates/' . self::ID . '/status', $http->request(1)->getUri()->getPath());
        $this->assertSame('DELETE', $http->request(2)->getMethod());
        $this->assertSame('/v1/templates/' . self::ID, $http->request(2)->getUri()->getPath());
    }

    public function test_analytics_by_id_and_global(): void
    {
        $http = new RecordingClient([new Response(200, [], '{}'), new Response(200, [], '{}')]);
        $templates = new Templates($http->client);

        $templates->analytics(self::ID, '7d');
        $templates->analytics();

        $this->assertSame('/v1/templates/' . self::ID . '/analytics', $http->request(0)->getUri()->getPath());
        $this->assertSame('period=7d', $http->request(0)->getUri()->getQuery());
        $this->assertSame('/v1/templates/analytics', $http->request(1)->getUri()->getPath());
        $this->assertSame('period=30d', $http->request(1)->getUri()->getQuery());
    }

    public function test_create_posts_payload(): void
    {
        $http = new RecordingClient([new Response(201, [], '{"id":"x"}')]);

        (new Templates($http->client))->create(['name' => 'welcome', 'body' => 'Ola {{1}}']);

        $this->assertSame('{"name":"welcome","body":"Ola {{1}}"}', (string) $http->request(0)->getBody());
    }

    public function test_get_maps_404_envelope_to_not_found_exception(): void
    {
        $http = new RecordingClient([new Response(404, [], '{"error":{"code":"TEMPLATE_NOT_FOUND","message":"Template not found","details":{}}}')]);

        try {
            (new Templates($http->client))->get(self::ID);
            $this->fail('Expected NotFoundException');
        } catch (NotFoundException $e) {
            $this->assertSame('TEMPLATE_NOT_FOUND', $e->errorCode);
            $this->assertSame('Template not found', $e->getMessage());
        }
    }
}
