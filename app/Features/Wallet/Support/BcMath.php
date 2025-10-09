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
        public int $scale = self::SCALE
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

    public function normalizePercentage(string $value): string
    {
        return bcdiv($value, 100, self::PERCENTAGE_NORMALIZATION_SCALE);
    }

    public function round(string $value): string
    {
        return bcround($value, self::SCALE);
    }
}
