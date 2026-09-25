<?php

declare(strict_types=1);

namespace Arara\Exceptions;

final class RateLimitException extends AraraException
{
    public const STATUS = 429;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        ?array $response = null,
        ?int $retryAfter = null,
        ?string $message = null,
    ) {
        parent::__construct(self::STATUS, $response, $message, $retryAfter);
    }
}
