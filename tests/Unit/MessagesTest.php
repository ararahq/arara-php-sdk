<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Arara;
use Arara\Config;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\ValidationException;
use Arara\Resources\Messages;
use Arara\Tests\Support\RecordingClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MessagesTest extends TestCase
{
    private const UUID_V4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    /**
     * @return array<string, array{string}>
     */
    public static function receiverFormats(): array
    {
        return [
            'whatsapp prefix' => ['whatsapp:+5511987654321'],
            'plus sign' => ['+5511987654321'],
            'digits only' => ['5511987654321'],
        ];
    }

    #[DataProvider('receiverFormats')]
    public function test_should_accept_every_receiver_format_the_api_accepts(string $receiver): void
    {
        $http = new RecordingClient([new Response(202, [], '{"id":"m1"}')]);

        (new Arara(new Config(apiKey: 'k'), $http->client))->messages->send($receiver, 'welcome', ['Ana']);

        $payload = json_decode((string) $http->request(0)->getBody(), true);
        $this->assertSame($receiver, $payload['receiver']);
        $this->assertSame(['Ana'], $payload['variables']);
        $this->assertSame('/v1/messages', $http->request(0)->getUri()->getPath());
    }

    public function test_should_generate_uuid_idempotency_key_when_caller_does_not_pass_one(): void
    {
        $http = new RecordingClient([new Response(202, [], '{}')]);

        (new Messages($http->client))->send('+5511987654321', body: 'oi');

        $this->assertMatchesRegularExpression(self::UUID_V4, $http->request(0)->getHeaderLine('Idempotency-Key'));
    }

    public function test_should_reuse_the_same_idempotency_key_when_retrying_after_5xx(): void
    {
        $http = new RecordingClient([new Response(503), new Response(502), new Response(202, [], '{"id":"m1"}')]);

        $result = (new Messages($http->client))->send('+5511987654321', 'welcome');

        $this->assertSame(['id' => 'm1'], $result);
        $this->assertSame(3, $http->count());
        $key = $http->request(0)->getHeaderLine('Idempotency-Key');
        $this->assertNotSame('', $key);
        $this->assertSame($key, $http->request(1)->getHeaderLine('Idempotency-Key'));
        $this->assertSame($key, $http->request(2)->getHeaderLine('Idempotency-Key'));
    }

    public function test_should_use_the_idempotency_key_given_by_the_caller(): void
    {
        $http = new RecordingClient([new Response(500), new Response(202, [], '{}')]);

        (new Messages($http->client))->send('+5511987654321', 'welcome', idempotencyKey: 'pedido-42');

        $this->assertSame('pedido-42', $http->request(0)->getHeaderLine('Idempotency-Key'));
        $this->assertSame('pedido-42', $http->request(1)->getHeaderLine('Idempotency-Key'));
    }

    public function test_should_throw_server_exception_after_exhausting_retries(): void
    {
        $http = new RecordingClient([new Response(500), new Response(500), new Response(500), new Response(503)]);

        try {
            (new Messages($http->client))->send('+5511987654321', 'welcome');
            $this->fail('Expected InternalServerException');
        } catch (InternalServerException $e) {
            $this->assertSame(503, $e->statusCode);
            $this->assertSame(4, $http->count());
        }
    }

    public function test_should_merge_extra_fields_and_send_media_url(): void
    {
        $http = new RecordingClient([new Response(202, [], '{}')]);

        (new Messages($http->client))->send(
            '+5511987654321',
            'invoice',
            mediaUrl: 'https://x.test/a.pdf',
            extra: ['sender' => '+5511900000000', 'scheduledAt' => '2026-10-01T12:00:00Z'],
        );

        $payload = json_decode((string) $http->request(0)->getBody(), true);
        $this->assertSame('+5511900000000', $payload['sender']);
        $this->assertSame('2026-10-01T12:00:00Z', $payload['scheduledAt']);
        $this->assertSame('https://x.test/a.pdf', $payload['mediaUrl']);
        $this->assertArrayNotHasKey('body', $payload);
    }

    public function test_should_reject_blank_receiver_locally(): void
    {
        $this->expectException(ValidationException::class);

        (new Messages((new RecordingClient([]))->client))->send('  ', 'welcome');
    }

    public function test_should_reject_blank_template_name_locally(): void
    {
        $this->expectException(ValidationException::class);

        (new Messages((new RecordingClient([]))->client))->send('+5511987654321', ' ');
    }

    public function test_send_batch_posts_items_with_idempotency_key(): void
    {
        $http = new RecordingClient([new Response(202, [], '{"batchId":"b1"}')]);

        $result = (new Messages($http->client))->sendBatch('welcome', [['receiver' => '+5511987654321', 'variables' => ['Ana']]]);

        $this->assertSame(['batchId' => 'b1'], $result);
        $this->assertSame('/v1/messages/batch', $http->request(0)->getUri()->getPath());
        $this->assertMatchesRegularExpression(self::UUID_V4, $http->request(0)->getHeaderLine('Idempotency-Key'));
        $payload = json_decode((string) $http->request(0)->getBody(), true);
        $this->assertSame('welcome', $payload['templateName']);
        $this->assertCount(1, $payload['messages']);
    }

    public function test_send_batch_rejects_more_than_the_api_limit(): void
    {
        $this->expectException(ValidationException::class);

        $items = array_fill(0, Messages::MAX_BATCH_SIZE + 1, ['receiver' => '+5511987654321']);
        (new Messages((new RecordingClient([]))->client))->sendBatch('welcome', $items);
    }

    public function test_send_batch_rejects_empty_list_and_blank_template(): void
    {
        $messages = new Messages((new RecordingClient([]))->client);

        foreach ([['', [['receiver' => '1']]], ['welcome', []]] as [$template, $items]) {
            try {
                $messages->sendBatch($template, $items);
                $this->fail('Expected ValidationException');
            } catch (ValidationException $e) {
                $this->assertSame(422, $e->statusCode);
            }
        }
    }

    public function test_get_fetches_message_by_id(): void
    {
        $http = new RecordingClient([new Response(200, [], '{"id":"abc","status":"DELIVERED"}')]);

        $result = (new Messages($http->client))->get('abc');

        $this->assertSame('DELIVERED', $result['status']);
        $this->assertSame('GET', $http->request(0)->getMethod());
        $this->assertSame('/v1/messages/abc', $http->request(0)->getUri()->getPath());
    }
}
