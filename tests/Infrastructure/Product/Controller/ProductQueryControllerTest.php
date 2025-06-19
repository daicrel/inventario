<?php

// tests/Infrastructure/Product/Controller/ProductQueryControllerTest.php

namespace App\Tests\Infrastructure\Product\Controller;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Product\Controller\Queries\ProductQueryController;
use App\Application\Product\Query\Handler\GetAllProductsHandler;
use App\Application\Product\Query\Handler\GetProductByIdHandler;
use App\Application\Product\Query\Response\ProductResponse;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\Price;
use App\Domain\Product\ValueObject\VariantId;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductQueryControllerTest extends TestCase
{
    public function testListReturnsAllProducts(): void
    {
        $handler = $this->createMock(GetAllProductsHandler::class);
        
        $expectedProducts = [
            new ProductResponse(
                '550e8400-e29b-41d4-a716-446655440000',
                'Producto 1',
                'Descripción 1',
                19.99,
                10,
                []
            ),
            new ProductResponse(
                '550e8400-e29b-41d4-a716-446655440001',
                'Producto 2',
                'Descripción 2',
                29.99,
                20,
                []
            )
        ];
        
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn($expectedProducts);
        
        $controller = new ProductQueryController();
        $response = $controller->list($handler);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertEquals('Producto 1', $data[0]['name']);
        $this->assertEquals('Producto 2', $data[1]['name']);
    }
    
    public function testGetReturnsProductWhenFound(): void
    {
        $handler = $this->createMock(GetProductByIdHandler::class);
        
        $expectedProduct = new ProductResponse(
            '550e8400-e29b-41d4-a716-446655440000',
            'Producto de Prueba',
            'Descripción del producto',
            19.99,
            10,
            [
                [
                    'id' => '550e8400-e29b-41d4-a716-446655440001',
                    'name' => 'Variante 1',
                    'price' => 21.99,
                    'stock' => 5,
                    'image' => 'imagen1.jpg'
                ]
            ]
        );
        
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn($expectedProduct);
        
        $controller = new ProductQueryController();
        $response = $controller->get('550e8400-e29b-41d4-a716-446655440000', $handler);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Producto de Prueba', $data['name']);
        $this->assertEquals(19.99, $data['price']);
        $this->assertCount(1, $data['variants']);
        $this->assertEquals('Variante 1', $data['variants'][0]['name']);
    }
    
    public function testGetReturns404WhenProductNotFound(): void
    {
        $handler = $this->createMock(GetProductByIdHandler::class);
        
        $handler->expects($this->once())
            ->method('__invoke')
            ->willReturn(null);
        
        $controller = new ProductQueryController();
        $response = $controller->get('non-existent-id', $handler);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Producto no encontrado', $data['error']);
    }
    
    public function testListHandlesException(): void
    {
        $handler = $this->createMock(GetAllProductsHandler::class);
        
        $handler->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new \Exception('Error de base de datos'));
        
        $controller = new ProductQueryController();
        $response = $controller->list($handler);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Error de base de datos', $data['error']);
        $this->assertArrayHasKey('trace', $data);
    }
    
    public function testGetHandlesException(): void
    {
        $handler = $this->createMock(GetProductByIdHandler::class);
        
        $handler->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new \Exception('Error de base de datos'));
        
        $controller = new ProductQueryController();
        $response = $controller->get('test-id', $handler);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Error de base de datos', $data['error']);
        $this->assertArrayHasKey('trace', $data);
    }
} 