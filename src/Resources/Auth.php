<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Auth extends BaseResource
{
    /**
     * GET /auth/me (fora do prefixo /v1; exige chave ADMIN).
     *
     * @return array<string, mixed>
     */
    public function me(): array
    {
        return $this->httpGet('/auth/me');
    }
}
