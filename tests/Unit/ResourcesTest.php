<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use Arara\Config;
use Arara\Tests\Support\RecordingClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ResourcesTest extends TestCase
{
    public function test_auth_me_calls_root_auth_path_outside_v1(): void
    {
        $http = new RecordingClient([new Response(200, [], '{"email":"a@b.c"}')]);
        $sdk = new Arara(new Config(apiKey: 'k'), $http->client);

        $this->assertSame(['email' => 'a@b.c'], $sdk->auth()->me());
        $this->assertSame($sdk->auth, $sdk->auth());
        $this->assertSame('https://api.ararahq.com/auth/me', (string) $http->request(0)->getUri());
    }

    public function test_sdk_no_longer_exposes_resources_unreachable_by_api_key(): void
    {
        $reflection = new \ReflectionClass(Arara::class);

        foreach (['users', 'organizations', 'apiKeys'] as $removed) {
            $this->assertFalse($reflection->hasProperty($removed), $removed);
        }
        $this->assertSame('2.0.0', Arara::VERSION);
    }

    public function test_smart_links_list_is_paginated(): void
    {
        $body = '{"data":[{"id":"s1"}],"pagination":{"page":1,"size":10,"totalElements":11,"totalPages":2}}';
        $http = new RecordingClient([new Response(200, [], $body)]);

        $page = (new Arara(new Config(apiKey: 'k'), $http->client))->smartLinks->list(1, 10);

        $this->assertSame([['id' => 's1']], $page->data);
        $this->assertFalse($page->hasNextPage());
        $this->assertSame('page=1&size=10', $http->request(0)->getUri()->getQuery());
    }

    public function test_campaign_create_reuses_generated_key_on_retry(): void
    {
        $http = new RecordingClient([new Response(500), new Response(201, [], '{"id":"c1"}')]);

        (new Arara(new Config(apiKey: 'k'), $http->client))->campaigns->create(['name' => 'x']);

        $key = $http->request(0)->getHeaderLine('Idempotency-Key');
        $this->assertNotSame('', $key);
        $this->assertSame($key, $http->request(1)->getHeaderLine('Idempotency-Key'));
    }

    public function test_opt_outs_crud_hits_contract_paths(): void
    {
        $http = new RecordingClient(array_fill(0, 4, new Response(200, [], '{}')));
        $optOuts = (new Arara(new Config(apiKey: 'k'), $http->client))->optOuts;

        $optOuts->list();
        $optOuts->add('+5511987654321', 'pediu');
        $optOuts->get('+5511987654321');
        $optOuts->remove('+5511987654321');

        $this->assertSame('GET /v1/opt-outs', $this->line($http, 0));
        $this->assertSame('POST /v1/opt-outs', $this->line($http, 1));
        $this->assertSame('{"phone":"+5511987654321","reason":"pediu"}', (string) $http->request(1)->getBody());
        $this->assertSame('GET /v1/opt-outs/%2B5511987654321', $this->line($http, 2));
        $this->assertSame('DELETE /v1/opt-outs/%2B5511987654321', $this->line($http, 3));
    }

    public function test_opt_out_add_omits_null_reason(): void
    {
        $http = new RecordingClient([new Response(201, [], '{}')]);

        (new Arara(new Config(apiKey: 'k'), $http->client))->optOuts->add('5511987654321');

        $this->assertSame('{"phone":"5511987654321"}', (string) $http->request(0)->getBody());
    }

    private function line(RecordingClient $http, int $i): string
    {
        return $http->request($i)->getMethod() . ' ' . $http->request($i)->getUri()->getPath();
    }
}
