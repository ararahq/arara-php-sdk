<?php

declare(strict_types=1);

namespace Arara\Utils;

final class WebhookUtils
{
    /**
     * @param array<string, mixed> $event
     */
    public static function isMessageStatusEvent(array $event): bool
    {
        return ($event['event'] ?? '') === 'message.status_updated';
    }

    /**
     * @param array<string, mixed> $event
     */
    public static function isInboundMessageEvent(array $event): bool
    {
        return ($event['event'] ?? '') === 'inbound_message';
    }

    /**
     * @param array<string, mixed> $event
     */
    public static function isRevenueRecoveryEvent(array $event): bool
    {
        return ($event['event'] ?? '') === 'revenue_recovery';
    }

    /**
     * @param array<string, mixed> $event
     */
    public static function isAbacatePayEvent(array $event): bool
    {
        return str_starts_with($event['event'] ?? '', 'abacatepay.');
    }

    public static function verifySignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}
