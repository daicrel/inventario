<?php

// src/Infrastructure/Product/Controller/Queries/ProductQueryController.php

namespace App\Infrastructure\Product\Controller\Queries;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Product\Query\GetAllProductsQuery;
use App\Application\Product\Query\GetProductByIdQuery;
use App\Application\Product\Query\Handler\GetAllProductsHandler;
use App\Application\Product\Query\Handler\GetProductByIdHandler;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Consultas de Productos",
 *     description="Endpoints de consulta para obtener información de productos"
 * )
 */
class ProductQueryController
{
    /**
     * @OA\Get(
     *     path="/queries/products",
     *     summary="Listar todos los productos",
     *     description="Obtiene la lista completa de productos con sus variantes usando CQRS",
     *     tags={"Consultas de Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="name", type="string", example="Laptop Dell XPS 13"),
     *                 @OA\Property(property="description", type="string", example="Laptop ultrabook con pantalla de 13 pulgadas"),
     *                 @OA\Property(property="price", type="number", format="float", example=1299.99),
     *                 @OA\Property(property="stock", type="integer", example=50),
     *                 @OA\Property(
     *                     property="variants",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                         @OA\Property(property="name", type="string", example="Blanco - Talla 42"),
     *                         @OA\Property(property="price", type="number", format="float", example=119.99),
     *                         @OA\Property(property="stock", type="integer", example=40),
     *                         @OA\Property(property="image", type="string", example="pegasus_blanco_42.jpg")
     *                     )
     *                 )
     *             )
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
    #[Route('/products', name: 'query_list_products', methods: ['GET'])]
    public function list(GetAllProductsHandler $handler): JsonResponse
    {
        try {
            $query = new GetAllProductsQuery();
            $products = $handler->__invoke($query);
            
            return new JsonResponse($products, 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/queries/products/{id}",
     *     summary="Obtener un producto específico",
     *     description="Obtiene los datos de un producto específico con sus variantes por su ID usando CQRS",
     *     tags={"Consultas de Productos"},
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
     *             @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="name", type="string", example="Laptop Dell XPS 13"),
     *             @OA\Property(property="description", type="string", example="Laptop ultrabook con pantalla de 13 pulgadas"),
     *             @OA\Property(property="price", type="number", format="float", example=1299.99),
     *             @OA\Property(property="stock", type="integer", example=50),
     *             @OA\Property(
     *                 property="variants",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="name", type="string", example="Blanco - Talla 42"),
     *                     @OA\Property(property="price", type="number", format="float", example=119.99),
     *                     @OA\Property(property="stock", type="integer", example=40),
     *                     @OA\Property(property="image", type="string", example="pegasus_blanco_42.jpg")
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
    #[Route('/products/{id}', name: 'query_get_product', methods: ['GET'])]
    public function get(string $id, GetProductByIdHandler $handler): JsonResponse
    {
        try {
            $query = new GetProductByIdQuery($id);
            $product = $handler->__invoke($query);
            
            if (!$product) {
                return new JsonResponse(['error' => 'Producto no encontrado'], 404);
            }
            
            return new JsonResponse($product, 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }
} 