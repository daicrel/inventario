<?php

// src/Domain/Product/ValueObject/Price.php

namespace App\Domain\Product\ValueObject;

final class Price
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }
}
