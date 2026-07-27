<?php

declare(strict_types=1);

namespace Arara\Tests\Unit\Utils;

use Arara\Utils\WebhookUtils;
use PHPUnit\Framework\TestCase;

final class WebhookUtilsTest extends TestCase
{
    private const SECRET = 'whsec_TESTE_1234567890';

    private const PAYLOAD = '{"event":"message.status_updated","data":{"status":"delivered"}}';

    public function testShouldAcceptSignatureProducedByTheAraraScheme(): void
    {
        $timestamp = time();
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . self::PAYLOAD, self::SECRET);

        $this->assertTrue(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldAcceptSignatureWithoutTheSha256Prefix(): void
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . self::PAYLOAD, self::SECRET);

        $this->assertTrue(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldRejectSignatureOverTheBodyAlone(): void
    {
        $timestamp = time();
        $legacySignature = hash_hmac('sha256', self::PAYLOAD, self::SECRET);

        $this->assertFalse(
            WebhookUtils::verifySignature(self::PAYLOAD, $legacySignature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldRejectWhenTimestampIsMissing(): void
    {
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, time());

        $this->assertFalse(WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET));
    }

    public function testShouldRejectReplayOutsideTheToleranceWindow(): void
    {
        $timestamp = time() - (WebhookUtils::DEFAULT_TOLERANCE_SECONDS + 1);
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, $timestamp);

        $this->assertFalse(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldRejectTimestampTooFarInTheFuture(): void
    {
        $timestamp = time() + (WebhookUtils::DEFAULT_TOLERANCE_SECONDS + 1);
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, $timestamp);

        $this->assertFalse(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldRejectNonNumericTimestamp(): void
    {
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, time());

        $this->assertFalse(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, 'not-a-timestamp'),
        );
    }

    public function testShouldRejectWhenSecretIsWrong(): void
    {
        $timestamp = time();
        $signature = WebhookUtils::sign(self::PAYLOAD, 'whsec_OUTRO_SEGREDO', $timestamp);

        $this->assertFalse(
            WebhookUtils::verifySignature(self::PAYLOAD, $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testShouldRejectWhenBodyWasTampered(): void
    {
        $timestamp = time();
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, $timestamp);

        $this->assertFalse(
            WebhookUtils::verifySignature('{"event":"outro"}', $signature, self::SECRET, (string) $timestamp),
        );
    }

    public function testSignShouldMatchTheBackendFormat(): void
    {
        $signature = WebhookUtils::sign(self::PAYLOAD, self::SECRET, 1_700_000_000);

        $this->assertSame(
            'sha256=' . hash_hmac('sha256', '1700000000.' . self::PAYLOAD, self::SECRET),
            $signature,
        );
        $this->assertMatchesRegularExpression('/^sha256=[0-9a-f]{64}$/', $signature);
    }
}
