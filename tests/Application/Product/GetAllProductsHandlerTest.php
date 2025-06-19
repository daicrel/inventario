<?php

// tests/Application/Product/GetAllProductsHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Query\GetAllProductsQuery;
use App\Application\Product\Query\Handler\GetAllProductsHandler;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\Price;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;

class GetAllProductsHandlerTest extends TestCase
{
    public function testReturnsEmptyArrayWhenNoProducts(): void
    {
        $repo = new FakeProductRepository();
        $handler = new GetAllProductsHandler($repo);
        
        $query = new GetAllProductsQuery();
        $result = $handler->__invoke($query);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testReturnsAllProducts(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear productos de prueba
        $product1 = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto 1'),
            new ProductDescription('Descripción del producto 1'),
            new Price(19.99),
            10
        );
        
        $product2 = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440001'),
            new ProductName('Producto 2'),
            new ProductDescription('Descripción del producto 2'),
            new Price(29.99),
            20
        );
        
        $repo->save($product1);
        $repo->save($product2);
        
        $handler = new GetAllProductsHandler($repo);
        $query = new GetAllProductsQuery();
        $result = $handler->__invoke($query);
        
        $this->assertCount(2, $result);
        $this->assertEquals('Producto 1', $result[0]->name);
        $this->assertEquals('Producto 2', $result[1]->name);
        $this->assertEquals(19.99, $result[0]->price);
        $this->assertEquals(29.99, $result[1]->price);
    }
    
    public function testReturnsProductsWithVariants(): void
    {
        $repo = new FakeProductRepository();
        
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto con Variantes'),
            new ProductDescription('Descripción del producto'),
            new Price(19.99),
            10
        );
        
        // Agregar variantes
        $product->addVariant(new \App\Domain\Product\Entity\Variant(
            new \App\Domain\Product\ValueObject\VariantId('550e8400-e29b-41d4-a716-446655440001'),
            $product,
            new ProductName('Variante 1'),
            new Price(21.99),
            5,
            'imagen1.jpg'
        ));
        
        $product->addVariant(new \App\Domain\Product\Entity\Variant(
            new \App\Domain\Product\ValueObject\VariantId('550e8400-e29b-41d4-a716-446655440002'),
            $product,
            new ProductName('Variante 2'),
            new Price(23.99),
            3,
            'imagen2.jpg'
        ));
        
        $repo->save($product);
        
        $handler = new GetAllProductsHandler($repo);
        $query = new GetAllProductsQuery();
        $result = $handler->__invoke($query);
        
        $this->assertCount(1, $result);
        $this->assertCount(2, $result[0]->variants);
        $this->assertEquals('Variante 1', $result[0]->variants[0]['name']);
        $this->assertEquals('Variante 2', $result[0]->variants[1]['name']);
        $this->assertEquals(21.99, $result[0]->variants[0]['price']);
        $this->assertEquals(23.99, $result[0]->variants[1]['price']);
    }
} 