<?php

declare(strict_types=1);

namespace Arara\Utils;

final class WebhookUtils
{
    public const SIGNATURE_HEADER = 'X-Arara-Signature';

    public const TIMESTAMP_HEADER = 'X-Arara-Timestamp';

    public const DEFAULT_TOLERANCE_SECONDS = 300;

    private const SIGNATURE_PREFIX = 'sha256=';

    private const HMAC_ALGORITHM = 'sha256';

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

    /**
     * Valida a assinatura de uma entrega de webhook da Arara.
     *
     * A Arara assina cada tentativa com `X-Arara-Signature: sha256=<hex>`, onde o
     * HMAC-SHA256 cobre a string `"{timestamp}.{corpo cru}"` e o `X-Arara-Timestamp`
     * (epoch em segundos) vem no mesmo request. Sem o timestamp não há como recompor
     * a mensagem assinada nem barrar replay, então a verificação falha fechada.
     *
     * O payload precisa ser o corpo cru da requisição (`file_get_contents('php://input')`);
     * qualquer decode/re-encode muda os bytes e invalida o HMAC.
     *
     * @param string      $payload           corpo cru da requisição
     * @param string      $signature         conteúdo do header X-Arara-Signature
     * @param string      $secret            webhook secret da organização (whsec_...)
     * @param string|null $timestamp         conteúdo do header X-Arara-Timestamp
     * @param int         $toleranceSeconds  janela anti-replay; a Arara documenta 5 minutos
     */
    public static function verifySignature(
        string $payload,
        string $signature,
        string $secret,
        ?string $timestamp = null,
        int $toleranceSeconds = self::DEFAULT_TOLERANCE_SECONDS,
    ): bool {
        if ($timestamp === null || ! self::isTimestampWithinTolerance($timestamp, $toleranceSeconds)) {
            return false;
        }

        $expected = self::sign($payload, $secret, (int) $timestamp);

        return hash_equals(self::stripPrefix($expected), self::stripPrefix($signature));
    }

    /**
     * Recompõe o header X-Arara-Signature (`sha256=<hex>`) do mesmo jeito que o backend.
     */
    public static function sign(string $payload, string $secret, int $timestampEpochSeconds): string
    {
        return self::SIGNATURE_PREFIX . hash_hmac(
            self::HMAC_ALGORITHM,
            $timestampEpochSeconds . '.' . $payload,
            $secret,
        );
    }

    private static function isTimestampWithinTolerance(string $timestamp, int $toleranceSeconds): bool
    {
        if ($toleranceSeconds <= 0 || preg_match('/^\d{1,19}$/', $timestamp) !== 1) {
            return false;
        }

        return abs(time() - (int) $timestamp) <= $toleranceSeconds;
    }

    private static function stripPrefix(string $signature): string
    {
        if (str_starts_with($signature, self::SIGNATURE_PREFIX)) {
            return substr($signature, strlen(self::SIGNATURE_PREFIX));
        }

        return $signature;
    }
}
