<?php

// tests/Application/Product/UpdateVariantHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Command\UpdateVariantCommand;
use App\Application\Product\Handler\UpdateVariantHandler;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\VariantId;
use App\Domain\Product\ValueObject\Price;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;

class UpdateVariantHandlerTest extends TestCase
{
    public function testVariantIsUpdated(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto con una variante
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto'),
            new ProductDescription('Descripción del producto'),
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
        
        $handler = new UpdateVariantHandler($repo);
        
        $command = new UpdateVariantCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            'Variante Actualizada',
            31.99,
            8,
            'nueva_imagen.jpg'
        );
        
        $handler($command);
        
        $updatedProduct = $repo->findById('550e8400-e29b-41d4-a716-446655440000');
        $variants = $updatedProduct->getVariants();
        
        $this->assertCount(1, $variants);
        $this->assertEquals('Variante Actualizada', (string)$variants[0]->getName());
        $this->assertEquals(31.99, $variants[0]->getPrice());
        $this->assertEquals(8, $variants[0]->getStock());
        $this->assertEquals('nueva_imagen.jpg', $variants[0]->getImage());
    }
    
    public function testProductNotFoundThrowsException(): void
    {
        $repo = new FakeProductRepository();
        $handler = new UpdateVariantHandler($repo);
        
        $command = new UpdateVariantCommand(
            'non-existent-product-id',
            'test-variant-id',
            'Nueva variante'
        );
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Producto no encontrado.');
        
        $handler($command);
    }
    
    public function testVariantNotFoundThrowsException(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto sin variantes
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto'),
            new ProductDescription('Descripción del producto'),
            new Price(19.99),
            10
        );
        
        $repo->save($product);
        
        $handler = new UpdateVariantHandler($repo);
        
        $command = new UpdateVariantCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            'non-existent-variant-id',
            'Nueva variante'
        );
        
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Variante no encontrada.');
        
        $handler($command);
    }
    
    public function testVariantWithPartialUpdate(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto con una variante
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto'),
            new ProductDescription('Descripción del producto'),
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
        
        $handler = new UpdateVariantHandler($repo);
        
        // Actualizar solo el nombre
        $command = new UpdateVariantCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
            'Solo nombre actualizado'
        );
        
        $handler($command);
        
        $updatedProduct = $repo->findById('550e8400-e29b-41d4-a716-446655440000');
        $variants = $updatedProduct->getVariants();
        
        $this->assertEquals('Solo nombre actualizado', (string)$variants[0]->getName());
        $this->assertEquals(21.99, $variants[0]->getPrice()); // Sin cambios
        $this->assertEquals(5, $variants[0]->getStock()); // Sin cambios
        $this->assertEquals('imagen_original.jpg', $variants[0]->getImage()); // Sin cambios
    }
    
    public function testVariantWithMultipleVariants(): void
    {
        $repo = new FakeProductRepository();
        
        // Crear un producto con múltiples variantes
        $product = new Product(
            new ProductId('550e8400-e29b-41d4-a716-446655440000'),
            new ProductName('Producto'),
            new ProductDescription('Descripción del producto'),
            new Price(19.99),
            10
        );
        
        $variant1 = new Variant(
            new VariantId('550e8400-e29b-41d4-a716-446655440001'),
            $product,
            new ProductName('Variante 1'),
            new Price(21.99),
            5,
            'imagen1.jpg'
        );
        
        $variant2 = new Variant(
            new VariantId('550e8400-e29b-41d4-a716-446655440002'),
            $product,
            new ProductName('Variante 2'),
            new Price(23.99),
            3,
            'imagen2.jpg'
        );
        
        $product->addVariant($variant1);
        $product->addVariant($variant2);
        $repo->save($product);
        
        $handler = new UpdateVariantHandler($repo);
        
        // Actualizar solo la segunda variante
        $command = new UpdateVariantCommand(
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440002',
            'Variante 2 Actualizada',
            33.99,
            7,
            'nueva_imagen2.jpg'
        );
        
        $handler($command);
        
        $updatedProduct = $repo->findById('550e8400-e29b-41d4-a716-446655440000');
        $variants = $updatedProduct->getVariants();
        
        $this->assertCount(2, $variants);
        
        // Verificar que la primera variante no cambió
        $this->assertEquals('Variante 1', (string)$variants[0]->getName());
        $this->assertEquals(21.99, $variants[0]->getPrice());
        $this->assertEquals(5, $variants[0]->getStock());
        $this->assertEquals('imagen1.jpg', $variants[0]->getImage());
        
        // Verificar que la segunda variante se actualizó
        $this->assertEquals('Variante 2 Actualizada', (string)$variants[1]->getName());
        $this->assertEquals(33.99, $variants[1]->getPrice());
        $this->assertEquals(7, $variants[1]->getStock());
        $this->assertEquals('nueva_imagen2.jpg', $variants[1]->getImage());
    }
} 