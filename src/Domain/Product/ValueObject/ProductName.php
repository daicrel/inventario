<?php

// src/Domain/Product/ValueObject/ProductName.php

namespace App\Domain\Product\ValueObject;

use InvalidArgumentException;

final class ProductName
{
    private string $name;

    public function __construct(string $name)
    {
        $name = trim($name);
        if (empty($name)) {
            throw new InvalidArgumentException("El nombre del producto no puede estar vacío.");
        }
        if (strlen($name) > 255) {
            throw new InvalidArgumentException("El nombre del producto no puede exceder los 255 caracteres.");
        }
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function equals(ProductName $other): bool
    {
        return $this->name === $other->name;
    }
}
