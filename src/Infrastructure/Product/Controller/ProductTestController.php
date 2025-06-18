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

class ProductTestController extends AbstractController
{
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
