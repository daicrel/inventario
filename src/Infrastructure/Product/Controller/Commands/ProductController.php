<?php

// src/Infrastructure/Product/Controller/Commands/ProductController.php

namespace App\Infrastructure\Product\Controller\Commands;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\Command\UpdateProductCommand;
use App\Application\Product\Command\DeleteProductCommand;
use App\Application\Product\Command\UpdateVariantCommand;
use App\Application\Product\Handler\CreateProductHandler;
use App\Application\Product\Handler\UpdateProductHandler;
use App\Application\Product\Handler\DeleteProductHandler;
use App\Application\Product\Handler\UpdateVariantHandler;
use Ramsey\Uuid\Uuid;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Comandos de Productos",
 *     description="Operaciones de escritura para gestionar productos en el inventario (CQRS - Commands)"
 * )
 */
class ProductController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/commands/products",
     *     summary="Crear un nuevo producto",
     *     description="Crea un nuevo producto con sus variantes en el sistema de inventario usando CQRS",
     *     tags={"Comandos de Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "stock", "variants"},
     *             @OA\Property(property="name", type="string", description="Nombre del producto", example="Laptop Dell XPS 13"),
     *             @OA\Property(property="description", type="string", description="Descripción del producto", example="Laptop ultrabook con pantalla de 13 pulgadas"),
     *             @OA\Property(property="price", type="number", format="float", description="Precio del producto", example=1299.99),
     *             @OA\Property(property="stock", type="integer", description="Cantidad en stock", example=50),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 description="Lista de variantes del producto",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", description="Nombre de la variante", example="Blanco - Talla 42"),
     *                     @OA\Property(property="price", type="number", format="float", description="Precio de la variante", example=119.99),
     *                     @OA\Property(property="stock", type="integer", description="Stock de la variante", example=40),
     *                     @OA\Property(property="image", type="string", description="Imagen de la variante", example="pegasus_blanco_42.jpg")
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación del dominio",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="El nombre del producto no puede estar vacío")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="trace", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request, CreateProductHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['variants'])) {
            return $this->json(['error' => 'Faltan campos obligatorios'], 400);
        }

        if (empty($data['name']) || empty($data['variants']) || !is_array($data['variants'])) {
            return $this->json(['error' => 'Campos obligatorios faltantes o no válidos: nombre o variantes'], 400);
        }

        // validaciones adicionales
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            return $this->json(['error' => 'El precio es obligatorio y debe ser numérico.'], 400);
        }

        if (!isset($data['stock']) || !is_numeric($data['stock'])) {
            return $this->json(['error' => 'El stock es obligatorio y debe ser numérico'], 400);
        }

        try {
            $command = new CreateProductCommand(
                Uuid::uuid4()->toString(),
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
     *     path="/commands/products/{id}",
     *     summary="Actualizar un producto existente",
     *     description="Actualiza los datos de un producto existente por su ID usando CQRS",
     *     tags={"Comandos de Productos"},
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
     *             @OA\Property(property="name", type="string", description="Nuevo nombre del producto", example="Laptop Dell XPS 13 Pro"),
     *             @OA\Property(property="description", type="string", description="Nueva descripción del producto", example="Laptop ultrabook premium con pantalla de 13 pulgadas y procesador Intel i7"),
     *             @OA\Property(property="price", type="number", format="float", description="Nuevo precio del producto", example=1499.99),
     *             @OA\Property(property="stock", type="integer", description="Nueva cantidad en stock", example=25),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 description="Nueva lista de variantes del producto",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", description="Nombre de la variante", example="Plata - 512GB SSD"),
     *                     @OA\Property(property="price", type="number", format="float", description="Precio de la variante", example=1599.99),
     *                     @OA\Property(property="stock", type="integer", description="Stock de la variante", example=15),
     *                     @OA\Property(property="image", type="string", description="Imagen de la variante", example="xps13_plata_512gb.jpg")
     *                 )
     *             )
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="trace", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/products/{id}', name: 'update_product', methods: ['PUT'])]
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
     *     path="/commands/products/{id}",
     *     summary="Eliminar un producto",
     *     description="Elimina un producto del sistema por su ID usando CQRS",
     *     tags={"Comandos de Productos"},
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="trace", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/products/{id}', name: 'delete_product', methods: ['DELETE'])]
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
     *     path="/commands/products/{productId}/variants/{variantId}",
     *     summary="Actualizar una variante de producto",
     *     description="Actualiza los datos de una variante específica de un producto usando CQRS",
     *     tags={"Comandos de Productos"},
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
     *             @OA\Property(property="name", type="string", description="Nuevo nombre de la variante", example="Color Azul"),
     *             @OA\Property(property="price", type="number", format="float", description="Nuevo precio de la variante", example=25.99),
     *             @OA\Property(property="stock", type="integer", description="Nueva cantidad en stock de la variante", example=15),
     *             @OA\Property(property="image", type="string", description="Nueva imagen de la variante", example="imagen_azul.jpg")
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="trace", type="string")
     *         )
     *     )
     * )
     */
    #[Route('/products/{productId}/variants/{variantId}', name: 'update_variant', methods: ['PUT'])]
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
