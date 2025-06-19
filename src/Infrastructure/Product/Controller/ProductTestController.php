<?php

// src/Infrastructure/Product/Controller/ProductTestController.php

namespace App\Infrastructure\Product\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use App\Application\Product\Handler\CreateProductHandler;
use App\Application\Product\Handler\UpdateProductHandler;
use App\Application\Product\Handler\DeleteProductHandler;
use App\Application\Product\Handler\UpdateVariantHandler;
use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\Command\UpdateProductCommand;
use App\Application\Product\Command\DeleteProductCommand;
use App\Application\Product\Command\UpdateVariantCommand;
use Ramsey\Uuid\Uuid;

/**
 * Controlador de pruebas para testear los endpoints del ProductController
 * Este controlador hace peticiones internas a los endpoints reales
 */
class ProductTestController extends AbstractController
{
    /**
     * Test: Crear un producto usando el endpoint POST /products
     */
    #[Route('/test/product/create', name: 'test_create_product', methods: ['POST'])]
    public function testCreateProduct(Request $request, HttpKernelInterface $httpKernel): JsonResponse
    {
        // JSON de prueba para crear un producto
        $testData = [
            'name' => 'Zapatillas Nike Air Max',
            'description' => 'Zapatillas deportivas de alta calidad',
            'price' => 129.99,
            'stock' => 25,
            'variants' => [
                [
                    'name' => 'Blanco - Talla 42',
                    'price' => 129.99,
                    'stock' => 10,
                    'image' => 'nike_airmax_blanco_42.jpg'
                ],
                [
                    'name' => 'Negro - Talla 43',
                    'price' => 129.99,
                    'stock' => 8,
                    'image' => 'nike_airmax_negro_43.jpg'
                ]
            ]
        ];

        // Crear una petición interna al endpoint real
        $subRequest = Request::create(
            '/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($testData)
        );

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Crear producto',
            'endpoint' => 'POST /products',
            'data_sent' => $testData,
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test: Listar productos usando el endpoint GET /products
     */
    #[Route('/test/product/list', name: 'test_list_products', methods: ['GET'])]
    public function testListProducts(HttpKernelInterface $httpKernel): JsonResponse
    {
        // Crear una petición interna al endpoint real
        $subRequest = Request::create('/products', 'GET');

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Listar productos',
            'endpoint' => 'GET /products',
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test: Obtener un producto específico usando el endpoint GET /products/{id}
     */
    #[Route('/test/product/get/{id}', name: 'test_get_product', methods: ['GET'])]
    public function testGetProduct(string $id, HttpKernelInterface $httpKernel): JsonResponse
    {
        // Crear una petición interna al endpoint real
        $subRequest = Request::create("/products/$id", 'GET');

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Obtener producto específico',
            'endpoint' => "GET /products/$id",
            'product_id' => $id,
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test: Actualizar un producto usando el endpoint PUT /products/{id}
     */
    #[Route('/test/product/update/{id}', name: 'test_update_product', methods: ['PUT'])]
    public function testUpdateProduct(string $id, HttpKernelInterface $httpKernel): JsonResponse
    {
        // JSON de prueba para actualizar un producto
        $testData = [
            'name' => 'Zapatillas Nike Air Max Actualizadas',
            'description' => 'Zapatillas deportivas actualizadas con nueva tecnología',
            'price' => 139.99,
            'stock' => 30
        ];

        // Crear una petición interna al endpoint real
        $subRequest = Request::create(
            "/products/$id",
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($testData)
        );

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Actualizar producto',
            'endpoint' => "PUT /products/$id",
            'product_id' => $id,
            'data_sent' => $testData,
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test: Eliminar un producto usando el endpoint DELETE /products/{id}
     */
    #[Route('/test/product/delete/{id}', name: 'test_delete_product', methods: ['DELETE'])]
    public function testDeleteProduct(string $id, HttpKernelInterface $httpKernel): JsonResponse
    {
        // Crear una petición interna al endpoint real
        $subRequest = Request::create("/products/$id", 'DELETE');

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Eliminar producto',
            'endpoint' => "DELETE /products/$id",
            'product_id' => $id,
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test: Actualizar una variante usando el endpoint PUT /products/{productId}/variants/{variantId}
     */
    #[Route('/test/product/{productId}/variants/{variantId}/update', name: 'test_update_variant', methods: ['PUT'])]
    public function testUpdateVariant(string $productId, string $variantId, HttpKernelInterface $httpKernel): JsonResponse
    {
        // JSON de prueba para actualizar una variante
        $testData = [
            'name' => 'Blanco - Talla 42 Actualizada',
            'price' => 135.99,
            'stock' => 12,
            'image' => 'nike_airmax_blanco_42_actualizada.jpg'
        ];

        // Crear una petición interna al endpoint real
        $subRequest = Request::create(
            "/products/$productId/variants/$variantId",
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($testData)
        );

        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->json([
            'test' => 'Actualizar variante',
            'endpoint' => "PUT /products/$productId/variants/$variantId",
            'product_id' => $productId,
            'variant_id' => $variantId,
            'data_sent' => $testData,
            'response' => json_decode($response->getContent(), true),
            'status_code' => $response->getStatusCode()
        ]);
    }

    /**
     * Test completo: Crear, listar, obtener, actualizar y eliminar un producto
     */
    #[Route('/test/product/full-test', name: 'test_full_product_cycle', methods: ['GET'])]
    public function testFullProductCycle(HttpKernelInterface $httpKernel): JsonResponse
    {
        $results = [];

        // 1. Crear producto
        $createData = [
            'name' => 'Camiseta de Prueba',
            'description' => 'Camiseta para testing',
            'price' => 29.99,
            'stock' => 15,
            'variants' => [
                [
                    'name' => 'Azul - M',
                    'price' => 29.99,
                    'stock' => 8,
                    'image' => 'camiseta_azul_m.jpg'
                ]
            ]
        ];

        $createRequest = Request::create(
            '/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($createData)
        );

        $createResponse = $httpKernel->handle($createRequest, HttpKernelInterface::SUB_REQUEST);
        $results['create'] = [
            'status' => $createResponse->getStatusCode(),
            'response' => json_decode($createResponse->getContent(), true)
        ];

        // 2. Listar productos para obtener el ID
        $listRequest = Request::create('/products', 'GET');
        $listResponse = $httpKernel->handle($listRequest, HttpKernelInterface::SUB_REQUEST);
        $products = json_decode($listResponse->getContent(), true);
        
        if (!empty($products)) {
            $productId = $products[0]['id'];
            $variantId = !empty($products[0]['variants']) ? $products[0]['variants'][0]['id'] : null;

            // 3. Obtener producto específico
            $getRequest = Request::create("/products/$productId", 'GET');
            $getResponse = $httpKernel->handle($getRequest, HttpKernelInterface::SUB_REQUEST);
            $results['get'] = [
                'status' => $getResponse->getStatusCode(),
                'response' => json_decode($getResponse->getContent(), true)
            ];

            // 4. Actualizar producto
            $updateData = [
                'name' => 'Camiseta de Prueba Actualizada',
                'price' => 34.99
            ];

            $updateRequest = Request::create(
                "/products/$productId",
                'PUT',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($updateData)
            );

            $updateResponse = $httpKernel->handle($updateRequest, HttpKernelInterface::SUB_REQUEST);
            $results['update'] = [
                'status' => $updateResponse->getStatusCode(),
                'response' => json_decode($updateResponse->getContent(), true)
            ];

            // 5. Actualizar variante si existe
            if ($variantId) {
                $variantUpdateData = [
                    'name' => 'Azul - M Actualizada',
                    'price' => 34.99,
                    'stock' => 10
                ];

                $variantRequest = Request::create(
                    "/products/$productId/variants/$variantId",
                    'PUT',
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    json_encode($variantUpdateData)
                );

                $variantResponse = $httpKernel->handle($variantRequest, HttpKernelInterface::SUB_REQUEST);
                $results['update_variant'] = [
                    'status' => $variantResponse->getStatusCode(),
                    'response' => json_decode($variantResponse->getContent(), true)
                ];
            }

            // 6. Eliminar producto
            $deleteRequest = Request::create("/products/$productId", 'DELETE');
            $deleteResponse = $httpKernel->handle($deleteRequest, HttpKernelInterface::SUB_REQUEST);
            $results['delete'] = [
                'status' => $deleteResponse->getStatusCode(),
                'response' => json_decode($deleteResponse->getContent(), true)
            ];
        }

        return $this->json([
            'test' => 'Ciclo completo de producto',
            'description' => 'Crear, obtener, actualizar y eliminar un producto',
            'results' => $results
        ]);
    }
}
