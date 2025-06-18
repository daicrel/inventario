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
            10
        );

        $handler($command);

        $products = $repo->findAll();
        $this->assertCount(1, $products);
        $this->assertEquals('Camiseta', $products[0]->getNameValue());
    }
}
