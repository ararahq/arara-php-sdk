<?php

declare(strict_types=1);

namespace Arara\Exceptions;

final class BadRequestException extends AraraException
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null)
    {
        parent::__construct(400, $response);
    }
}
