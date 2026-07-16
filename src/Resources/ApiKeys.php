<?php

declare(strict_types=1);

namespace Arara\Resources;

final class ApiKeys extends BaseResource
{
    /**
     * GET /v1/api-keys
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->httpGet('api-keys');
    }

    /**
     * POST /v1/api-keys
     *
     * @return array<string, mixed>
     */
    public function create(string $mode = 'LIVE'): array
    {
        return $this->httpPost('api-keys', ['query' => ['mode' => $mode]]);
    }
}
