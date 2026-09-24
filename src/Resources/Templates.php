<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Pagination\PaginatedResponse;

/**
 * Templates são endereçados pelo id (UUID) devolvido em list/create, nunca pelo nome.
 */
final class Templates extends BaseResource
{
    public const DEFAULT_PAGE_SIZE = 50;

    public const DEFAULT_ANALYTICS_PERIOD = '30d';

    /**
     * GET /v1/templates.
     */
    public function list(int $page = 0, int $size = self::DEFAULT_PAGE_SIZE, ?string $name = null, ?string $status = null): PaginatedResponse
    {
        $query = ['page' => $page, 'size' => $size];

        if ($name !== null) {
            $query['name'] = $name;
        }

        if ($status !== null) {
            $query['status'] = $status;
        }

        return PaginatedResponse::fromArray($this->httpGet('templates', ['query' => $query]));
    }

    /**
     * GET /v1/templates/{id}.
     *
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->httpGet(self::path($id));
    }

    /**
     * GET /v1/templates/{id}/status.
     *
     * @return array<string, mixed>
     */
    public function getStatus(string $id): array
    {
        return $this->httpGet(self::path($id) . '/status');
    }

    /**
     * POST /v1/templates.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->httpPost('templates', ['json' => $data]);
    }

    /**
     * DELETE /v1/templates/{id}.
     *
     * @return array<string, mixed>
     */
    public function delete(string $id): array
    {
        return $this->httpDelete(self::path($id));
    }

    /**
     * GET /v1/templates/{id}/analytics, ou GET /v1/templates/analytics quando $id é nulo.
     *
     * @return array<string, mixed>
     */
    public function analytics(?string $id = null, string $period = self::DEFAULT_ANALYTICS_PERIOD): array
    {
        $endpoint = $id === null ? 'templates/analytics' : self::path($id) . '/analytics';

        return $this->httpGet($endpoint, ['query' => ['period' => $period]]);
    }

    private static function path(string $id): string
    {
        return 'templates/' . rawurlencode($id);
    }
}
