<?php

declare(strict_types=1);

namespace Arara\Exceptions;

final class AuthenticationException extends AraraException
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null)
    {
        parent::__construct(401, $response);
    }
}
