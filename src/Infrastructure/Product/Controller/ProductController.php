<?php

// src/Infrastructure/Product/Controller/ProductController.php

namespace App\Infrastructure\Product\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Product\Command\CreateProductCommand;
use App\Application\Product\Handler\CreateProductHandler;
use Ramsey\Uuid\Uuid;

class ProductController extends AbstractController
{
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
                $data['id'] ?? Uuid::uuid4()->toString(),
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
}
