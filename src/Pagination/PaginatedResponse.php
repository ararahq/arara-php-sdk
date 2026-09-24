<?php

declare(strict_types=1);

namespace Arara\Pagination;

/**
 * Resposta paginada no formato {data: [...], pagination: {page,size,totalElements,totalPages}}.
 */
final readonly class PaginatedResponse
{
    /**
     * @param list<array<string, mixed>> $data
     */
    public function __construct(
        public array $data,
        public Pagination $pagination,
    ) {
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function fromArray(array $body): self
    {
        $rawData = is_array($body['data'] ?? null) ? $body['data'] : [];

        $data = [];
        foreach ($rawData as $item) {
            if (is_array($item)) {
                $data[] = $item;
            }
        }

        return new self($data, Pagination::fromMixed($body['pagination'] ?? null));
    }

    public function hasNextPage(): bool
    {
        return $this->pagination->page + 1 < $this->pagination->totalPages;
    }
}
