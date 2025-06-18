<?php

// src/Application/Product/DTO/CreateProductRequest.php

namespace App\Application\Product\DTO;

class CreateProductRequest
{
    public string $name;
    public ?string $description = null;
    public array $variants = []; // Cada variante tendrá nombre, precio, imagen, etc.
}
