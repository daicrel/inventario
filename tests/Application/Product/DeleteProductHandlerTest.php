<?php

// tests/Application/Product/DeleteProductHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Command\DeleteProductCommand;
use App\Application\Product\Handler\DeleteProductHandler;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\Price;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;

class DeleteProductHandlerTest extends TestCase
{
    public function testProductIsDeleted(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto existente
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto a eliminar'),
            new ProductDescription('Descripción del producto'),
            new Price(19.99),
            10
        );
        
        $repo->save($product);
        
        // Verificar que el producto existe
        $this->assertNotNull($repo->findById('550e8400-e29b-41d4-a716-446655440000'));
        
        $handler = new DeleteProductHandler($repo);
        
        $command = new DeleteProductCommand('550e8400-e29b-41d4-a716-446655440000');
        
        $handler($command);
        
        // Verificar que el producto fue eliminado
        $this->assertNull($repo->findById('550e8400-e29b-41d4-a716-446655440000'));
    }
    
    public function testProductNotFoundThrowsException(): void
    {
        $repo = new FakeProductRepository();
        $handler = new DeleteProductHandler($repo);
        
        $command = new DeleteProductCommand('non-existent-id');
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Producto no encontrado.');
        
        $handler($command);
    }
    
    public function testProductWithVariantsIsDeleted(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto existente con variantes
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto con variantes'),
            new ProductDescription('Descripción del producto'),
            new Price(19.99),
            10
        );
        
        // Agregar algunas variantes
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
        
        // Verificar que el producto existe
        $this->assertNotNull($repo->findById('550e8400-e29b-41d4-a716-446655440000'));
        
        $handler = new DeleteProductHandler($repo);
        
        $command = new DeleteProductCommand('550e8400-e29b-41d4-a716-446655440000');
        
        $handler($command);
        
        // Verificar que el producto fue eliminado
        $this->assertNull($repo->findById('550e8400-e29b-41d4-a716-446655440000'));
    }
} 