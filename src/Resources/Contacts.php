<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Contacts extends BaseResource
{
    /**
     * GET /v1/contacts
     *
     * @return array<string, mixed>
     */
    public function list(int $page = 0, int $size = 20, ?string $q = null, ?string $lifecycle = null): array
    {
        $query = ['page' => $page, 'size' => $size];

        if ($q !== null) {
            $query['q'] = $q;
        }

        if ($lifecycle !== null) {
            $query['lifecycle'] = $lifecycle;
        }

        return $this->httpGet('contacts', ['query' => $query]);
    }

    /**
     * POST /v1/contacts/batch
     *
     * @param array<int, array<string, mixed>> $contacts
     * @return array<string, mixed>
     */
    public function importBatch(array $contacts): array
    {
        return $this->httpPost('contacts/batch', ['json' => $contacts]);
    }

    /**
     * GET /v1/contacts/stats
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        return $this->httpGet('contacts/stats');
    }

    /**
     * GET /v1/contacts/reactivation
     *
     * @return array<string, mixed>
     */
    public function reactivationCandidates(int $limit = 100): array
    {
        return $this->httpGet('contacts/reactivation', ['query' => ['limit' => $limit]]);
    }

    /**
     * GET /v1/contacts/tags
     *
     * @return array<string, mixed>
     */
    public function listTags(): array
    {
        return $this->httpGet('contacts/tags');
    }

    /**
     * GET /v1/contacts/{phone}
     *
     * @return array<string, mixed>
     */
    public function get(string $phone): array
    {
        return $this->httpGet("contacts/{$phone}");
    }

    /**
     * PATCH /v1/contacts/{phone}
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $phone, array $data): array
    {
        return $this->httpPatch("contacts/{$phone}", ['json' => $data]);
    }

    /**
     * GET /v1/contacts/{phone}/messages
     *
     * @return array<string, mixed>
     */
    public function messages(string $phone, int $limit = 30): array
    {
        return $this->httpGet("contacts/{$phone}/messages", ['query' => ['limit' => $limit]]);
    }
}
