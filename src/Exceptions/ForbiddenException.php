<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * Qualquer 403. `errorCode` vem preenchido quando a API manda o envelope (ex.: RESOURCE_FORBIDDEN)
 * e fica nulo no 403 sem envelope (chave sem permissão, recurso de outra organização).
 * PLAN_FEATURE_LOCKED tem subclasse própria.
 */
class ForbiddenException extends AraraException
{
    public const STATUS = 403;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null)
    {
        parent::__construct(self::STATUS, $response);
    }
}
