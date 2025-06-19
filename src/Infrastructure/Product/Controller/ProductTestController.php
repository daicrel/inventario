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
     * Test: Crear un producto usando el endpoint POST /commands/products
     */
    public function testCreateProduct(): JsonResponse
    {
        $productData = [
            'name' => 'Producto de Prueba',
            'description' => 'Descripción del producto de prueba',
            'price' => 29.99,
            'stock' => 10,
            'variants' => [
                [
                    'name' => 'Variante 1',
                    'price' => 31.99,
                    'stock' => 5,
                    'image' => 'imagen1.jpg'
                ]
            ]
        ];

        $subRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'endpoint' => 'POST /commands/products',
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test: Listar productos usando el endpoint GET /queries/products
     */
    public function testListProducts(): JsonResponse
    {
        $subRequest = Request::create('/queries/products', 'GET');
        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Productos listados exitosamente',
            'endpoint' => 'GET /queries/products',
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test: Obtener un producto específico usando el endpoint GET /queries/products/{id}
     */
    public function testGetProduct(): JsonResponse
    {
        // Primero crear un producto para obtener su ID
        $productData = [
            'name' => 'Producto para Obtener',
            'description' => 'Producto para probar GET',
            'price' => 19.99,
            'stock' => 5,
            'variants' => []
        ];

        $createRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $createResponse = $this->forward($createRequest);
        $createData = json_decode($createResponse->getContent(), true);

        // Ahora obtener el producto creado
        $subRequest = Request::create("/queries/products/$id", 'GET');
        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Producto obtenido exitosamente',
            'endpoint' => "GET /queries/products/$id",
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test: Actualizar un producto usando el endpoint PUT /commands/products/{id}
     */
    public function testUpdateProduct(): JsonResponse
    {
        // Primero crear un producto
        $productData = [
            'name' => 'Producto Original',
            'description' => 'Producto para actualizar',
            'price' => 15.99,
            'stock' => 3,
            'variants' => []
        ];

        $createRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $createResponse = $this->forward($createRequest);
        $createData = json_decode($createResponse->getContent(), true);

        // Actualizar el producto
        $updateData = [
            'name' => 'Producto Actualizado',
            'description' => 'Producto actualizado exitosamente',
            'price' => 25.99,
            'stock' => 8
        ];

        $subRequest = Request::create(
            "/commands/products/$id",
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'endpoint' => "PUT /commands/products/$id",
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test: Eliminar un producto usando el endpoint DELETE /commands/products/{id}
     */
    public function testDeleteProduct(): JsonResponse
    {
        // Primero crear un producto
        $productData = [
            'name' => 'Producto a Eliminar',
            'description' => 'Producto que será eliminado',
            'price' => 9.99,
            'stock' => 1,
            'variants' => []
        ];

        $createRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $createResponse = $this->forward($createRequest);
        $createData = json_decode($createResponse->getContent(), true);

        // Eliminar el producto
        $subRequest = Request::create("/commands/products/$id", 'DELETE');
        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente',
            'endpoint' => "DELETE /commands/products/$id",
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test: Actualizar una variante usando el endpoint PUT /commands/products/{productId}/variants/{variantId}
     */
    public function testUpdateVariant(): JsonResponse
    {
        // Primero crear un producto con variantes
        $productData = [
            'name' => 'Producto con Variantes',
            'description' => 'Producto para probar actualización de variantes',
            'price' => 39.99,
            'stock' => 10,
            'variants' => [
                [
                    'name' => 'Variante Original',
                    'price' => 41.99,
                    'stock' => 5,
                    'image' => 'imagen_original.jpg'
                ]
            ]
        ];

        $createRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $createResponse = $this->forward($createRequest);
        $createData = json_decode($createResponse->getContent(), true);

        // Obtener el producto para acceder a las variantes
        $listRequest = Request::create('/queries/products', 'GET');
        $listResponse = $this->forward($listRequest);
        $products = json_decode($listResponse->getContent(), true);

        $productId = null;
        $variantId = null;

        foreach ($products as $product) {
            if ($product['name'] === 'Producto con Variantes') {
                $productId = $product['id'];
                if (!empty($product['variants'])) {
                    $variantId = $product['variants'][0]['id'];
                }
                break;
            }
        }

        if (!$productId || !$variantId) {
            return $this->json(['error' => 'No se pudo encontrar el producto o variante'], 404);
        }

        // Actualizar la variante
        $updateData = [
            'name' => 'Variante Actualizada',
            'price' => 45.99,
            'stock' => 8,
            'image' => 'imagen_actualizada.jpg'
        ];

        $subRequest = Request::create(
            "/commands/products/$productId/variants/$variantId",
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $this->forward($subRequest);

        return $this->json([
            'success' => true,
            'message' => 'Variante actualizada exitosamente',
            'endpoint' => "PUT /commands/products/$productId/variants/$variantId",
            'response' => json_decode($response->getContent(), true)
        ]);
    }

    /**
     * Test completo del flujo CRUD
     */
    public function testCompleteCrudFlow(): JsonResponse
    {
        $results = [];

        // 1. Crear producto
        $productData = [
            'name' => 'Producto CRUD Test',
            'description' => 'Producto para probar flujo completo',
            'price' => 49.99,
            'stock' => 15,
            'variants' => [
                [
                    'name' => 'Variante CRUD',
                    'price' => 51.99,
                    'stock' => 7,
                    'image' => 'crud_variant.jpg'
                ]
            ]
        ];

        $createRequest = Request::create(
            '/commands/products',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($productData)
        );

        $createResponse = $this->forward($createRequest);
        $results['create'] = json_decode($createResponse->getContent(), true);

        // 2. Listar productos
        $listRequest = Request::create('/queries/products', 'GET');
        $listResponse = $this->forward($listRequest);
        $products = json_decode($listResponse->getContent(), true);
        $results['list'] = $products;

        // 3. Obtener producto específico
        $productId = null;
        foreach ($products as $product) {
            if ($product['name'] === 'Producto CRUD Test') {
                $productId = $product['id'];
                break;
            }
        }

        if ($productId) {
            $getRequest = Request::create("/queries/products/$productId", 'GET');
            $getResponse = $this->forward($getRequest);
            $results['get'] = json_decode($getResponse->getContent(), true);

            // 4. Actualizar producto
            $updateData = [
                'name' => 'Producto CRUD Actualizado',
                'price' => 59.99,
                'stock' => 20
            ];

            $updateRequest = Request::create(
                "/commands/products/$productId",
                'PUT',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($updateData)
            );

            $updateResponse = $this->forward($updateRequest);
            $results['update'] = json_decode($updateResponse->getContent(), true);

            // 5. Eliminar producto
            $deleteRequest = Request::create("/commands/products/$productId", 'DELETE');
            $deleteResponse = $this->forward($deleteRequest);
            $results['delete'] = json_decode($deleteResponse->getContent(), true);
        }

        return $this->json([
            'success' => true,
            'message' => 'Flujo CRUD completo ejecutado',
            'results' => $results
        ]);
    }
}
