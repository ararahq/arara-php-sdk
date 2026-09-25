<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Campaigns extends BaseResource
{
    /**
     * POST /v1/campaigns.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data, ?string $idempotencyKey = null): array
    {
        return $this->httpPost('campaigns', $this->withIdempotencyKey(['json' => $data], $idempotencyKey));
    }

    /**
     * GET /v1/campaigns.
     *
     * @return array<string, mixed>
     */
    public function list(int $page = 0, int $size = 20, ?string $status = null): array
    {
        $query = ['page' => $page, 'size' => $size];

        if ($status !== null) {
            $query['status'] = $status;
        }

        return $this->httpGet('campaigns', ['query' => $query]);
    }

    /**
     * GET /v1/campaigns/estimate.
     *
     * @return array<string, mixed>
     */
    public function estimate(string $templateName, int $count): array
    {
        return $this->httpGet('campaigns/estimate', ['query' => ['templateName' => $templateName, 'count' => $count]]);
    }

    /**
     * GET /v1/campaigns/{id}.
     *
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->httpGet("campaigns/{$id}");
    }

    /**
     * POST /v1/campaigns/{id}/cancel.
     *
     * @return array<string, mixed>
     */
    public function cancel(string $id): array
    {
        return $this->httpPost("campaigns/{$id}/cancel");
    }
}
