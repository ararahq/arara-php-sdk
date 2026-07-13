<?php

declare(strict_types=1);

namespace Arara\Exceptions;

final class RateLimitException extends AraraException
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        ?array $response = null,
        public readonly ?int $retryAfter = null,
        ?string $message = null,
    ) {
        parent::__construct(429, $response, $message);
    }
}
