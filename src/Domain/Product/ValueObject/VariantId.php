<?php

// src/Domain/Product/ValueObject/VariantId.php

namespace App\Domain\Product\ValueObject;

use Ramsey\Uuid\Uuid;

class VariantId
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("ID de variante inválido");
        }

        $this->value = $value;
    }

    public static function random(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
