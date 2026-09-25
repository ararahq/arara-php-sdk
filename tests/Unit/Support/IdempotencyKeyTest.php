<?php

declare(strict_types=1);

namespace Arara\Tests\Unit\Support;

use Arara\Exceptions\ValidationException;
use Arara\Resources\Messages;
use Arara\Support\IdempotencyKey;
use Arara\Tests\Support\RecordingClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdempotencyKeyTest extends TestCase
{
    public function test_null_generates_a_fresh_uuid_v4(): void
    {
        $key = IdempotencyKey::resolve(null);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $key);
        $this->assertNotSame($key, IdempotencyKey::resolve(null));
    }

    public function test_caller_key_is_trimmed(): void
    {
        $this->assertSame('abc', IdempotencyKey::resolve('  abc  '));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function blankKeys(): array
    {
        return ['empty' => [''], 'whitespace' => ["  \t "]];
    }

    #[DataProvider('blankKeys')]
    public function test_blank_caller_key_is_rejected(string $blank): void
    {
        $this->expectException(ValidationException::class);

        IdempotencyKey::resolve($blank);
    }

    public function test_send_with_blank_key_fails_before_any_request(): void
    {
        $http = new RecordingClient([]);

        try {
            (new Messages($http->client))->send('+5511987654321', 'welcome', idempotencyKey: ' ');
            $this->fail('Expected ValidationException');
        } catch (ValidationException) {
            $this->assertSame(0, $http->count());
        }
    }
}
