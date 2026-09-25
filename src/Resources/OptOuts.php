<?php

declare(strict_types=1);

namespace Arara\Resources;

/**
 * Descadastros (opt-out). Leitura exige chave ADMIN.
 */
final class OptOuts extends BaseResource
{
    /**
     * GET /v1/opt-outs.
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->httpGet('opt-outs');
    }

    /**
     * POST /v1/opt-outs.
     *
     * @return array<string, mixed>
     */
    public function add(string $phone, ?string $reason = null): array
    {
        $payload = ['phone' => $phone];

        if ($reason !== null) {
            $payload['reason'] = $reason;
        }

        return $this->httpPost('opt-outs', ['json' => $payload]);
    }

    /**
     * GET /v1/opt-outs/{phone}.
     *
     * @return array<string, mixed>
     */
    public function get(string $phone): array
    {
        return $this->httpGet('opt-outs/' . rawurlencode($phone));
    }

    /**
     * DELETE /v1/opt-outs/{phone}.
     *
     * @return array<string, mixed>
     */
    public function remove(string $phone): array
    {
        return $this->httpDelete('opt-outs/' . rawurlencode($phone));
    }
}
