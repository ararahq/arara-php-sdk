<?php

declare(strict_types=1);

namespace Arara\Resources;

final class Wallet extends BaseResource
{
    /**
     * GET /v1/wallet/transactions
     *
     * @return array<string, mixed>
     */
    public function transactions(int $page = 0, int $size = 20): array
    {
        return $this->httpGet('wallet/transactions', ['query' => ['page' => $page, 'size' => $size]]);
    }

    /**
     * GET /v1/wallet/auto-recharge
     *
     * @return array<string, mixed>
     */
    public function getAutoRecharge(): array
    {
        return $this->httpGet('wallet/auto-recharge');
    }

    /**
     * PATCH /v1/wallet/auto-recharge
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function updateAutoRecharge(array $data): array
    {
        return $this->httpPatch('wallet/auto-recharge', ['json' => $data]);
    }
}
