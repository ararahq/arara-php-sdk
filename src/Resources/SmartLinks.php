<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Pagination\PaginatedResponse;

final class SmartLinks extends BaseResource
{
    public const DEFAULT_PAGE_SIZE = 50;

    /**
     * POST /v1/smart-links/whatsapp.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->httpPost('smart-links/whatsapp', ['json' => $data]);
    }

    /**
     * PUT /v1/smart-links/whatsapp/{id}.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $id, array $data): array
    {
        return $this->httpPut("smart-links/whatsapp/{$id}", ['json' => $data]);
    }

    /**
     * GET /v1/smart-links/whatsapp.
     */
    public function list(int $page = 0, int $size = self::DEFAULT_PAGE_SIZE): PaginatedResponse
    {
        return PaginatedResponse::fromArray(
            $this->httpGet('smart-links/whatsapp', ['query' => ['page' => $page, 'size' => $size]]),
        );
    }

    /**
     * GET /v1/smart-links/whatsapp/{id}/stats.
     *
     * @return array<string, mixed>
     */
    public function stats(string $id): array
    {
        return $this->httpGet("smart-links/whatsapp/{$id}/stats");
    }
}
