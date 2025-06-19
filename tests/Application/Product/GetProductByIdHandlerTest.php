<?php

// tests/Application/Product/GetProductByIdHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Query\GetProductByIdQuery;
use App\Application\Product\Query\Handler\GetProductByIdHandler;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\Price;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;

class GetProductByIdHandlerTest extends TestCase
{
    public function testReturnsNullWhenProductNotFound(): void
    {
        $repo = new FakeProductRepository();
        $handler = new GetProductByIdHandler($repo);
        
        $query = new GetProductByIdQuery('non-existent-id');
        $result = $handler->__invoke($query);
        
        $this->assertNull($result);
    }
    
    public function testReturnsProductWhenFound(): void
    {
        $repo = new FakeProductRepository();
        
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto de Prueba'),
            new ProductDescription('Descripción del producto de prueba'),
            new Price(19.99),
            10
        );
        
        $repo->save($product);
        
        $handler = new GetProductByIdHandler($repo);
        $query = new GetProductByIdQuery('550e8400-e29b-41d4-a716-446655440000');
        $result = $handler->__invoke($query);
        
        $this->assertNotNull($result);
        $this->assertEquals('Producto de Prueba', $result->name);
        $this->assertEquals('Descripción del producto de prueba', $result->description);
        $this->assertEquals(19.99, $result->price);
        $this->assertEquals(10, $result->stock);
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $result->id);
    }
    
    public function testReturnsProductWithVariants(): void
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
        
        $handler = new GetProductByIdHandler($repo);
        $query = new GetProductByIdQuery('550e8400-e29b-41d4-a716-446655440000');
        $result = $handler->__invoke($query);
        
        $this->assertNotNull($result);
        $this->assertCount(2, $result->variants);
        $this->assertEquals('Variante 1', $result->variants[0]['name']);
        $this->assertEquals('Variante 2', $result->variants[1]['name']);
        $this->assertEquals(21.99, $result->variants[0]['price']);
        $this->assertEquals(23.99, $result->variants[1]['price']);
        $this->assertEquals('imagen1.jpg', $result->variants[0]['image']);
        $this->assertEquals('imagen2.jpg', $result->variants[1]['image']);
    }
} 