<?php

// src/Domain/Product/ValueObject/ProductDescription.php

namespace App\Domain\Product\ValueObject;

use InvalidArgumentException;

final class ProductDescription
{
    private string $description;

    public function __construct(string $description)
    {
        if (empty(trim($description))) {
            throw new InvalidArgumentException("La descripción del producto no puede estar vacía.");
        }

        $this->description = $description;
    }

    public function __toString(): string
    {
        return $this->description;
    }
}
