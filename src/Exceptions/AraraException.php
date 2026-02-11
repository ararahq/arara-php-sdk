<?php

declare(strict_types=1);

namespace Arara\Exceptions;

class AraraException extends \Exception
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly ?array $response = null,
        ?string $message = null,
    ) {
        parent::__construct($message ?? $response['message'] ?? "HTTP {$statusCode}");
    }
}
