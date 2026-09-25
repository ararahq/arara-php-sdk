<?php

declare(strict_types=1);

namespace Arara\Exceptions;

/**
 * 403 PLAN_FEATURE_LOCKED: a feature exige um plano superior ao atual.
 */
final class PlanFeatureLockedException extends ForbiddenException
{
    public const CODE = 'PLAN_FEATURE_LOCKED';

    public readonly ?string $feature;

    public readonly ?string $currentPlan;

    public readonly ?string $upgradeTo;

    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(?array $response = null)
    {
        parent::__construct($response);

        $this->feature = self::stringOrNull($this->details['feature'] ?? null);
        $this->currentPlan = self::stringOrNull($this->details['currentPlan'] ?? null);
        $this->upgradeTo = self::stringOrNull($this->details['upgradeTo'] ?? null);
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
