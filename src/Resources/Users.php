<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Users extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function getMe(): array
    {
        return $this->httpGet('users/me');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(array $data): array
    {
        return $this->httpPost('users/me', [
            'json' => $data,
        ]);
    }
}
