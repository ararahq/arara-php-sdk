<?php

declare(strict_types=1);

namespace Arara\Support;

/**
 * Gera o valor do header Idempotency-Key (UUID v4).
 */
final class IdempotencyKey
{
    public const HEADER = 'Idempotency-Key';

    public static function generate(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public static function resolve(?string $key): string
    {
        return $key !== null && trim($key) !== '' ? $key : self::generate();
    }
}
