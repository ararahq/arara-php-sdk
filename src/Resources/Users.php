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
        return $this->get('users/me');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(array $data): array
    {
        return $this->post('users/me', [
            'json' => $data,
        ]);
    }
}
