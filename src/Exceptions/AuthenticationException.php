<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * Chave inválida, expirada, sem permissão ou ausente.
 *
 * A API responde 403 sem envelope nesses casos (nunca 401); 401 também cai aqui.
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
