<?php

declare(strict_types=1);

namespace Arara\Http;

use Arara\Config;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Política de retry do SDK: reenvia em erro de conexão, 5xx e 429,
 * com backoff exponencial e respeito ao header Retry-After.
 */
final class RetryPolicy
{
    private const MILLISECONDS_PER_SECOND = 1000;

    private const HTTP_TOO_MANY_REQUESTS = 429;

    private const HTTP_INTERNAL_SERVER_ERROR = 500;

    public static function decider(Config $config): callable
    {
        return static function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?\Throwable $exception = null,
        ) use ($config): bool {
            if ($retries >= $config->retryTimes) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            return self::isRetryableStatus($response?->getStatusCode());
        };
    }

    public static function delay(Config $config): callable
    {
        return static function (int $retries, ?ResponseInterface $response = null) use ($config): int {
            $retryAfter = $response?->getHeaderLine('Retry-After');

            if (is_numeric($retryAfter)) {
                return (int) $retryAfter * self::MILLISECONDS_PER_SECOND;
            }

            return $config->retryDelayMs * (2 ** max(0, $retries - 1));
        };
    }

    private static function isRetryableStatus(?int $statusCode): bool
    {
        if ($statusCode === null) {
            return false;
        }

        return $statusCode === self::HTTP_TOO_MANY_REQUESTS
            || $statusCode >= self::HTTP_INTERNAL_SERVER_ERROR;
    }
}
