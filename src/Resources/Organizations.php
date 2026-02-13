<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Organizations extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function getWebhook(): array
    {
        return $this->get('organizations/webhook');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateWebhook(array $data): array
    {
        return $this->post('organizations/webhook', [
            'json' => $data,
        ]);
    }
}
