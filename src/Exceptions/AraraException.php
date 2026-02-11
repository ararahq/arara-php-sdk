<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * Exceção base do SDK Arara. Todas as exceções HTTP estendem esta classe.
 */
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
