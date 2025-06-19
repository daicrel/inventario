<?php

// src/Infrastructure/Product/Controller/ProductTestController.php

namespace App\Infrastructure\Product\Controller;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Variant;
use App\Domain\Product\ValueObject\ProductId;
use App\Domain\Product\ValueObject\ProductName;
use App\Domain\Product\ValueObject\ProductDescription;
use App\Domain\Product\ValueObject\VariantId;
use App\Domain\Product\Repository\ProductRepository;
use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\Command\UpdateProductCommand;
use App\Application\Product\Command\DeleteProductCommand;
use App\Application\Product\Command\UpdateVariantCommand;
use App\Application\Product\Handler\CreateProductHandler;
use App\Application\Product\Handler\UpdateProductHandler;
use App\Application\Product\Handler\DeleteProductHandler;
use App\Application\Product\Handler\UpdateVariantHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Pruebas de Productos",
 *     description="Endpoints de prueba para productos (solo desarrollo)"
 * )
 */
class ProductTestController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/test/product/list",
     *     summary="Listar todos los productos",
     *     description="Obtiene la lista completa de productos con sus variantes",
     *     tags={"Pruebas de Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="Camiseta"),
     *                 @OA\Property(property="description", type="string", example="Camiseta de algodón"),
     *                 @OA\Property(property="price", type="number", format="float", example=19.99),
     *                 @OA\Property(property="stock", type="integer", example=10),
     *                 @OA\Property(
     *                     property="variants",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="Camiseta Azul M"),
     *                         @OA\Property(property="price", type="number", format="float", example=21.99),
     *                         @OA\Property(property="stock", type="integer", example=5),
     *                         @OA\Property(property="image", type="string", example="imagen.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/test/product/list', name: 'test_product_list', methods: ['GET'])]
    public function list(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $data = [];
        foreach ($products as $product) {
            $variants = [];
            foreach ($product->getVariants() as $variant) {
                $variants[] = [
                    'id' => (string) $variant->getId(),
                    'name' => (string) $variant->getName(),
                    'price' => $variant->getPrice(),
                    'stock' => $variant->getStock(),
                    'image' => $variant->getImage()
                ];
            }

            $data[] = [
                'id' => (string) $product->getId(),
                'name' => (string) $product->getName(),
                'description' => (string) $product->getDescription(),
                'price' => $product->price()->value(),
                'stock' => $product->getStock(),
                'variants' => $variants
            ];
        }

        return $this->json($data);
    }

    /**
     * @OA\Get(
     *     path="/test/product/{id}",
     *     summary="Obtener un producto específico de prueba",
     *     description="Obtiene los datos de un producto específico con sus variantes por su ID (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto obtenido exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", example="Camiseta"),
     *             @OA\Property(property="description", type="string", example="Camiseta de algodón"),
     *             @OA\Property(property="price", type="number", format="float", example=19.99),
     *             @OA\Property(property="stock", type="integer", example=10),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="Camiseta Azul M"),
     *                     @OA\Property(property="price", type="number", format="float", example=21.99),
     *                     @OA\Property(property="stock", type="integer", example=5),
     *                     @OA\Property(property="image", type="string", example="imagen.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/{id}', name: 'test_get_product', methods: ['GET'])]
    public function get(string $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->findById($id);
        
        if (!$product) {
            return $this->json(['error' => 'Producto no encontrado'], 404);
        }
        
        $variants = [];
        foreach ($product->getVariants() as $variant) {
            $variants[] = [
                'id' => (string) $variant->getId(),
                'name' => (string) $variant->getName(),
                'price' => $variant->getPrice(),
                'stock' => $variant->getStock(),
                'image' => $variant->getImage()
            ];
        }

        $data = [
            'id' => (string) $product->getId(),
            'name' => (string) $product->getName(),
            'description' => (string) $product->getDescription(),
            'price' => $product->price()->value(),
            'stock' => $product->getStock(),
            'variants' => $variants
        ];

        return $this->json($data);
    }

    /**
     * @OA\Post(
     *     path="/test/product/create",
     *     summary="Crear un producto de prueba usando handlers",
     *     description="Crea un producto de prueba usando los handlers de la aplicación (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "stock", "variants"},
     *             @OA\Property(property="name", type="string", description="Nombre del producto", example="Camiseta"),
     *             @OA\Property(property="description", type="string", description="Descripción del producto", example="Camiseta de algodón"),
     *             @OA\Property(property="price", type="number", format="float", description="Precio del producto", example=19.99),
     *             @OA\Property(property="stock", type="integer", description="Cantidad en stock", example=10),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 description="Lista de variantes del producto",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", description="Nombre de la variante", example="Camiseta Azul M"),
     *                     @OA\Property(property="price", type="number", format="float", description="Precio de la variante", example=21.99),
     *                     @OA\Property(property="stock", type="integer", description="Stock de la variante", example=5),
     *                     @OA\Property(property="image", type="string", description="Imagen de la variante", example="imagen.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Faltan campos obligatorios")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/create', name: 'test_create_product', methods: ['POST'])]
    public function create(Request $request, CreateProductHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['variants'])) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        if (empty($data['name']) || empty($data['variants']) || !is_array($data['variants'])) {
            return $this->json(['error' => 'Campos obligatorios faltantes o no válidos: nombre o variantes'], 400);
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->json(['error' => 'El precio es obligatorio y debe ser numérico.'], 400);
        }

        if (!isset($data['stock']) || !is_numeric($data['stock'])) {
            return $this->json(['error' => 'El stock es obligatorio y debe ser numérico'], 400);
        }

        try {
            $command = new CreateProductCommand(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                $data['name'],
                $data['description'] ?? '',
                (float) $data['price'],
                (int) $data['stock'],
                $data['variants'] ?? []
            );

            $handler->__invoke($command);

            return $this->json(['message' => 'Producto creado exitosamente'], 201);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/test/product/{id}",
     *     summary="Actualizar un producto de prueba",
     *     description="Actualiza los datos de un producto existente por su ID (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nuevo nombre del producto"),
     *             @OA\Property(property="description", type="string", description="Nueva descripción del producto"),
     *             @OA\Property(property="price", type="number", format="float", description="Nuevo precio del producto"),
     *             @OA\Property(property="stock", type="integer", description="Nueva cantidad en stock")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No se proporcionaron datos para actualizar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación del dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/{id}', name: 'test_update_product', methods: ['PUT'])]
    public function update(string $id, Request $request, UpdateProductHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->json(['error' => 'No se proporcionaron datos para actualizar'], 400);
        }

        try {
            $command = new UpdateProductCommand(
                $id,
                $data['name'] ?? null,
                $data['description'] ?? null,
                isset($data['price']) ? (float) $data['price'] : null,
                isset($data['stock']) ? (int) $data['stock'] : null,
                $data['variants'] ?? null
            );

            $handler->__invoke($command);

            return $this->json(['message' => 'Producto actualizado exitosamente'], 200);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/test/product/{id}",
     *     summary="Eliminar un producto de prueba",
     *     description="Elimina un producto del sistema por su ID (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto a eliminar",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación del dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/{id}', name: 'test_delete_product', methods: ['DELETE'])]
    public function delete(string $id, DeleteProductHandler $handler): JsonResponse
    {
        try {
            $command = new DeleteProductCommand($id);
            $handler->__invoke($command);

            return $this->json(['message' => 'Producto eliminado exitosamente'], 200);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/test/product/{productId}/variants/{variantId}",
     *     summary="Actualizar una variante de producto de prueba",
     *     description="Actualiza los datos de una variante específica de un producto (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         required=true,
     *         description="ID único del producto",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="variantId",
     *         in="path",
     *         required=true,
     *         description="ID único de la variante",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nuevo nombre de la variante"),
     *             @OA\Property(property="price", type="number", format="float", description="Nuevo precio de la variante"),
     *             @OA\Property(property="stock", type="integer", description="Nueva cantidad en stock de la variante"),
     *             @OA\Property(property="image", type="string", description="Nueva imagen de la variante")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Variante actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Variante actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No se proporcionaron datos para actualizar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación del dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/{productId}/variants/{variantId}', name: 'test_update_variant', methods: ['PUT'])]
    public function updateVariant(string $productId, string $variantId, Request $request, UpdateVariantHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            return $this->json(['error' => 'No se proporcionaron datos para actualizar'], 400);
        }

        try {
            $command = new UpdateVariantCommand(
                $productId,
                $variantId,
                $data['name'] ?? null,
                isset($data['price']) ? (float) $data['price'] : null,
                isset($data['stock']) ? (int) $data['stock'] : null,
                $data['image'] ?? null
            );

            $handler->__invoke($command);

            return $this->json(['message' => 'Variante actualizada exitosamente'], 200);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
}
