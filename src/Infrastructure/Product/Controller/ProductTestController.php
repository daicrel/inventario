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
     * @OA\Post(
     *     path="/test/product/add",
     *     summary="Agregar producto de prueba",
     *     description="Crea un producto de prueba con una variante predefinida (solo para desarrollo)",
     *     tags={"Pruebas de Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="Nombre del producto", example="Camiseta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto de prueba creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto guardado en memoria")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Producto con nombre duplicado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Ya existe un producto con ese nombre.")
     *         )
     *     )
     * )
     */
    #[Route('/test/product/add', name: 'test_product_add', methods: ['POST'])]
    public function add(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validación: no permitir productos con el mismo nombre
        foreach ($productRepository->findAll() as $product) {
            if ((string)$product->getName() === $data['name']) {
                return $this->json(['error' => 'Ya existe un producto con ese nombre.'], 400);
            }
        }

        // Crear producto
        $product = new Product(
            ProductId::random(),
            new ProductName('Camiseta'),
            new ProductDescription('Camiseta de algodón'),
            19.99,
            10
        );

        // Crear variante
        $variant = new Variant(
            VariantId::random(),
            $product,
            new ProductName('Camiseta Azul M'),
            21.99,
            5,
            'imagen.jpg'
        );

        // Asociar variante al producto
        $product->addVariant($variant);

        // Guardar producto
        $productRepository->save($product);

        return $this->json(['message' => 'Producto guardado en memoria']);
    }

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
            $data[] = [
                'id' => (string) $product->getId(),
                'name' => (string) $product->getName(),
                'description' => (string) $product->getDescription(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'variants' => array_map(fn($v) => [
                    'id' => (string) $v->getId(),
                    'name' => (string) $v->getName(),
                    'price' => $v->getPrice(),
                    'stock' => $v->getStock(),
                    'image' => $v->getImage(),
                ], $product->getVariants()->toArray())
            ];
        }

        return $this->json($data);
    }
}
