<?php

declare(strict_types=1);

namespace Arara\Pagination;

final readonly class Pagination
{
    public function __construct(
        public int $page,
        public int $size,
        public int $totalElements,
        public int $totalPages,
    ) {
    }

    public static function fromMixed(mixed $raw): self
    {
        $raw = is_array($raw) ? $raw : [];

        return new self(
            self::int($raw['page'] ?? null),
            self::int($raw['size'] ?? null),
            self::int($raw['totalElements'] ?? null),
            self::int($raw['totalPages'] ?? null),
        );
    }

    private static function int(mixed $value): int
    {
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }
}
