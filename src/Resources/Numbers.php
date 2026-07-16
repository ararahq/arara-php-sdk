<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Numbers extends BaseResource
{
    /**
     * GET /v1/organizations/me/numbers
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->httpGet('organizations/me/numbers');
    }

    /**
     * PATCH /v1/organizations/me/numbers/{id}
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $id, array $data): array
    {
        return $this->httpPatch("organizations/me/numbers/{$id}", ['json' => $data]);
    }

    /**
     * DELETE /v1/organizations/me/numbers/{id}
     *
     * @return array<string, mixed>
     */
    public function delete(string $id): array
    {
        return $this->httpDelete("organizations/me/numbers/{$id}");
    }

    /**
     * POST /v1/organizations/me/numbers/request
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function request(array $data): array
    {
        return $this->httpPost('organizations/me/numbers/request', ['json' => $data]);
    }

    /**
     * GET /v1/organizations/me/numbers/requests
     *
     * @return array<string, mixed>
     */
    public function listRequests(): array
    {
        return $this->httpGet('organizations/me/numbers/requests');
    }

    /**
     * POST /v1/organizations/me/numbers/{id}/sync
     *
     * @return array<string, mixed>
     */
    public function sync(string $id): array
    {
        return $this->httpPost("organizations/me/numbers/{$id}/sync");
    }

    /**
     * GET /v1/organizations/me/numbers/{id}/warming
     *
     * @return array<string, mixed>
     */
    public function warming(string $id): array
    {
        return $this->httpGet("organizations/me/numbers/{$id}/warming");
    }
}
