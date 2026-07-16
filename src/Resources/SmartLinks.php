<?php

declare(strict_types=1);

namespace Arara\Resources;

final class SmartLinks extends BaseResource
{
    /**
     * POST /v1/smart-links/whatsapp
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->httpPost('smart-links/whatsapp', ['json' => $data]);
    }

    /**
     * PUT /v1/smart-links/whatsapp/{id}
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $id, array $data): array
    {
        return $this->httpPut("smart-links/whatsapp/{$id}", ['json' => $data]);
    }

    /**
     * GET /v1/smart-links/whatsapp
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->httpGet('smart-links/whatsapp');
    }

    /**
     * GET /v1/smart-links/whatsapp/{id}/stats
     *
     * @return array<string, mixed>
     */
    public function stats(string $id): array
    {
        return $this->httpGet("smart-links/whatsapp/{$id}/stats");
    }
}
