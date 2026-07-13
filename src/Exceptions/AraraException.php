<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * Exceção base do SDK Arara. Todas as exceções HTTP estendem esta classe.
 */
class AraraException extends \Exception
{
    public readonly ?string $errorCode;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly ?array $response = null,
        ?string $message = null,
    ) {
        $error = is_array($response['error'] ?? null) ? $response['error'] : [];
        $this->errorCode = is_string($error['code'] ?? null) ? $error['code'] : null;

        parent::__construct($message ?? $this->extractMessage($error, $response, $statusCode));
    }

    /**
     * @param array<mixed, mixed> $error
     * @param array<string, mixed>|null $response
     */
    private function extractMessage(array $error, ?array $response, int $statusCode): string
    {
        if (is_string($error['message'] ?? null)) {
            return $error['message'];
        }

        if (is_string($response['message'] ?? null)) {
            return $response['message'];
        }

        return "HTTP {$statusCode}";
    }
}
