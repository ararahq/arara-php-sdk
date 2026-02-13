<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Templates extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->get('templates');
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $name): array
    {
        return $this->get("templates/{$name}");
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        return $this->post('templates', [
            'json' => $data,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function delete(string $name): array
    {
        return $this->delete("templates/{$name}");
    }
}
