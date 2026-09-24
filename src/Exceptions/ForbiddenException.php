<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * 403 de regra de negócio com código no envelope (ex.: RESOURCE_FORBIDDEN).
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
