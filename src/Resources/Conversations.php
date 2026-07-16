<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Conversations extends BaseResource
{
    /**
     * GET /v1/conversations
     *
     * @return array<string, mixed>
     */
    public function list(?string $status = null, ?string $leadStatus = null, int $page = 0, int $size = 20): array
    {
        $query = ['page' => $page, 'size' => $size];

        if ($status !== null) {
            $query['status'] = $status;
        }

        if ($leadStatus !== null) {
            $query['leadStatus'] = $leadStatus;
        }

        return $this->httpGet('conversations', ['query' => $query]);
    }

    /**
     * GET /v1/conversations/lead-stats
     *
     * @return array<string, mixed>
     */
    public function leadStats(): array
    {
        return $this->httpGet('conversations/lead-stats');
    }

    /**
     * GET /v1/conversations/{conversationId}/messages
     *
     * @return array<string, mixed>
     */
    public function messages(string $conversationId, int $page = 0, int $size = 50): array
    {
        return $this->httpGet("conversations/{$conversationId}/messages", ['query' => ['page' => $page, 'size' => $size]]);
    }

    /**
     * POST /v1/conversations/reply
     *
     * @return array<string, mixed>
     */
    public function reply(string $conversationId, string $body): array
    {
        return $this->httpPost('conversations/reply', [
            'json' => ['conversationId' => $conversationId, 'body' => $body],
        ]);
    }

    /**
     * PATCH /v1/conversations/{conversationId}/status
     *
     * @return array<string, mixed>
     */
    public function updateStatus(string $conversationId, string $status): array
    {
        return $this->httpPatch("conversations/{$conversationId}/status", ['json' => ['status' => $status]]);
    }

    /**
     * POST /v1/conversations/window-status
     *
     * @param array<int, string> $phones
     * @return array<string, mixed>
     */
    public function windowStatus(array $phones): array
    {
        return $this->httpPost('conversations/window-status', ['json' => ['phones' => $phones]]);
    }
}
