<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * HTTP 401. Hoje a API responde 403 (ForbiddenException) para chave inválida.
 */
final class AuthenticationException extends AraraException
{
    public const DEFAULT_STATUS = 401;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null, int $statusCode = self::DEFAULT_STATUS)
    {
        parent::__construct($statusCode, $response);
    }
}
