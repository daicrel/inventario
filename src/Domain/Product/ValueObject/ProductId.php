<?php

// src/Domain/Product/ValueObject/ProductId.php

namespace App\Domain\Product\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class ProductId
{
    private string $id;

    public function __construct(string $id)
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidArgumentException("UUID inválido para ProductId: $id.");
        }
        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(ProductId $other): bool
    {
        return $this->id === $other->id;
    }

    public static function random(): self
    {
        return new self(Uuid::uuid4()->toString());
    }
}
