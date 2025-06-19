<?php

// tests/Application/Product/CreateProductHandlerTest.php

namespace App\Tests\Application\Product;

use App\Application\Product\Command\CreateProductCommand as Command;
use App\Application\Product\Handler\CreateProductHandler;
use App\Tests\Fake\FakeProductRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CreateProductHandlerTest extends TestCase
{
    public function testProductIsPersisted(): void
    {
        $repo = new FakeProductRepository();
        $dispatcher = new EventDispatcher();

        $handler = new CreateProductHandler($repo, $dispatcher);

        $command = new Command(
            'uuid-product-id',
            'Camiseta',
            'Camiseta de algodón',
            19.99,
            10,
            [
                [
                    'name' => 'Camiseta Azul M',
                    'price' => 21.99,
                    'stock' => 5,
                    'image' => 'imagen.jpg'
                ]
            ]
        );

        $handler($command);

        $products = $repo->findAll();
        $this->assertCount(1, $products);
        $this->assertEquals('Camiseta', $products[0]->getNameValue());
        
        // Verificar que se creó la variante
        $variants = $products[0]->getVariants();
        $this->assertCount(1, $variants);
        $this->assertEquals('Camiseta Azul M', (string)$variants[0]->getName());
        $this->assertEquals(21.99, $variants[0]->getPrice());
        $this->assertEquals(5, $variants[0]->getStock());
        $this->assertEquals('imagen.jpg', $variants[0]->getImage());
    }

    public function testProductWithMultipleVariantsIsPersisted(): void
    {
        $repo = new FakeProductRepository();
        $dispatcher = new EventDispatcher();

        $handler = new CreateProductHandler($repo, $dispatcher);

        $command = new Command(
            'uuid-product-id',
            'Zapatillas Nike',
            'Zapatillas de running',
            119.99,
            50,
            [
                [
                    'name' => 'Blanco - Talla 42',
                    'price' => 119.99,
                    'stock' => 20,
                    'image' => 'nike_blanco_42.jpg'
                ],
                [
                    'name' => 'Negro - Talla 43',
                    'price' => 119.99,
                    'stock' => 15,
                    'image' => 'nike_negro_43.jpg'
                ]
            ]
        );

        $handler($command);

        $products = $repo->findAll();
        $this->assertCount(1, $products);
        
        // Verificar que se crearon las variantes
        $variants = $products[0]->getVariants();
        $this->assertCount(2, $variants);
        
        // Verificar primera variante
        $this->assertEquals('Blanco - Talla 42', (string)$variants[0]->getName());
        $this->assertEquals(119.99, $variants[0]->getPrice());
        $this->assertEquals(20, $variants[0]->getStock());
        $this->assertEquals('nike_blanco_42.jpg', $variants[0]->getImage());
        
        // Verificar segunda variante
        $this->assertEquals('Negro - Talla 43', (string)$variants[1]->getName());
        $this->assertEquals(119.99, $variants[1]->getPrice());
        $this->assertEquals(15, $variants[1]->getStock());
        $this->assertEquals('nike_negro_43.jpg', $variants[1]->getImage());
    }

    public function testProductWithoutVariantsIsPersisted(): void
    {
        $repo = new FakeProductRepository();
        $dispatcher = new EventDispatcher();

        $handler = new CreateProductHandler($repo, $dispatcher);

        $command = new Command(
            'uuid-product-id',
            'Producto Simple',
            'Producto sin variantes',
            29.99,
            25,
            []
        );

        $handler($command);

        $products = $repo->findAll();
        $this->assertCount(1, $products);
        $this->assertEquals('Producto Simple', $products[0]->getNameValue());
        
        // Verificar que no hay variantes
        $variants = $products[0]->getVariants();
        $this->assertCount(0, $variants);
    }
}
