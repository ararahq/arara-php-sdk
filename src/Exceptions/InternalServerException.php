<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * Qualquer 5xx (500, 502, 503...).
 */
final class InternalServerException extends AraraException
{
    public const DEFAULT_STATUS = 500;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null, int $statusCode = self::DEFAULT_STATUS, ?int $retryAfter = null)
    {
        parent::__construct($statusCode, $response, null, $retryAfter);
    }
}
