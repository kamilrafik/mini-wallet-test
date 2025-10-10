<?php

namespace App\Features\Wallet\Support;

/**
 * The only purpose of class is to encapsulate scale.
 */
class BcMath
{
    private const SCALE = 2;

    /**
     * To work with such values as 1.5%
     */
    private const PERCENTAGE_NORMALIZATION_SCALE = 3;

    public function __construct(
        private int $scale = self::SCALE,
        private int $percentageNormalizationScale = self::PERCENTAGE_NORMALIZATION_SCALE
    )
    {
    }

    public function add(string $value1, string $value2): string
    {
        return bcadd($value1, $value2, $this->scale);
    }

    public function sub(string $value1, string $value2): string
    {
        return bcsub($value1, $value2, $this->scale);
    }

    public function mul(string $value1, string $value2): string
    {
        return bcmul($value1, $value2, $this->scale);
    }

    public function div(string $value1, string $value2): string
    {
        return bcdiv($value1, $value2, $this->scale);
    }

    public function comp(string $value1, string $value2): int
    {
        return bccomp($value1, $value2, $this->scale);
    }

    public function calcPercentage(string $value, string $percentage): string
    {
        $value = $this->normalizePercentagedValue($value);
        $percentage = $this->normalizePercentage($percentage);
        $result = bcmul($value, $percentage, $this->percentageNormalizationScale);
        return bcround($result, $this->scale);
    }

    private function normalizePercentagedValue(string $value): string
    {
        return bcround($value, $this->percentageNormalizationScale);
    }

    private function normalizePercentage(string $value): string
    {
        return bcdiv($value, 100, $this->percentageNormalizationScale);
    }

    public function normalizeValue(string $value): string
    {
        return bcround($value, self::SCALE);
    }
}
