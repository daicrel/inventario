<?php

// tests/Application/Product/UpdateProductHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Command\UpdateProductCommand;
use App\Application\Product\Handler\UpdateProductHandler;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\VariantId;
use App\Domain\Product\ValueObject\Price;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;

class UpdateProductHandlerTest extends TestCase
{
    public function testProductIsUpdated(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto existente
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto Original'),
            new ProductDescription('Descripción original'),
            new Price(19.99),
            10
        );
        
        $repo->save($product);
        
        $handler = new UpdateProductHandler($repo);
        
        $command = new UpdateProductCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            'Producto Actualizado',
            'Nueva descripción',
            29.99,
            20
        );
        
        $handler($command);
        
        $updatedProduct = $repo->findById('550e8400-e29b-41d4-a716-446655440000');
        $this->assertEquals('Producto Actualizado', $updatedProduct->getNameValue());
        $this->assertEquals('Nueva descripción', (string)$updatedProduct->getDescription());
        $this->assertEquals(29.99, $updatedProduct->price()->value());
        $this->assertEquals(20, $updatedProduct->getStock());
    }
    
    public function testProductNotFoundThrowsException(): void
    {
        $repo = new FakeProductRepository();
        $handler = new UpdateProductHandler($repo);
        
        $command = new UpdateProductCommand(
            'non-existent-id',
            'Nuevo nombre'
        );
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Producto no encontrado.');
        
        $handler($command);
    }
    
    public function testProductWithVariantsIsUpdated(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto existente con variantes
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto Original'),
            new ProductDescription('Descripción original'),
            new Price(19.99),
            10
        );
        
        $variant = new Variant(
            new VariantId('550e8400-e29b-41d4-a716-446655440001'),
            $product,
            new ProductName('Variante Original'),
            new Price(21.99),
            5,
            'imagen_original.jpg'
        );
        
        $product->addVariant($variant);
        $repo->save($product);
        
        $handler = new UpdateProductHandler($repo);
        
        $command = new UpdateProductCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            'Producto Actualizado',
            'Nueva descripción',
            29.99,
            20,
            [
                [
                    'name' => 'Nueva Variante',
                    'price' => 31.99,
                    'stock' => 8,
                    'image' => 'nueva_imagen.jpg'
                ]
            ]
        );
        
        $handler($command);
        
        $updatedProduct = $repo->findById('550e8400-e29b-41d4-a716-446655440000');
        $this->assertEquals('Producto Actualizado', $updatedProduct->getNameValue());
        
        $variants = $updatedProduct->getVariants();
        $this->assertCount(1, $variants);
        $this->assertEquals('Nueva Variante', (string)$variants[0]->getName());
        $this->assertEquals(31.99, $variants[0]->getPrice());
        $this->assertEquals(8, $variants[0]->getStock());
        $this->assertEquals('nueva_imagen.jpg', $variants[0]->getImage());
    }
} 