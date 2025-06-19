<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class SwaggerController extends AbstractController
{
    #[Route('/api/doc', name: 'api_doc')]
    public function index(Environment $twig): Response
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'API de Inventario',
                'description' => 'API para gestión de productos y variantes en el sistema de inventario',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'Equipo de Desarrollo',
                    'email' => 'desarrollo@inventario.com'
                ]
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Servidor de desarrollo'
                ],
                [
                    'url' => 'https://api.inventario.com',
                    'description' => 'Servidor de producción'
                ]
            ],
            'paths' => [
                '/products' => [
                    'post' => [
                        'tags' => ['Productos'],
                        'summary' => 'Crear un nuevo producto',
                        'description' => 'Crea un nuevo producto con sus variantes en el sistema de inventario',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name', 'price', 'stock', 'variants'],
                                        'properties' => [
                                            'id' => [
                                                'type' => 'string',
                                                'format' => 'uuid',
                                                'description' => 'ID único del producto (opcional, se genera automáticamente)'
                                            ],
                                            'name' => [
                                                'type' => 'string',
                                                'description' => 'Nombre del producto',
                                                'example' => 'Laptop Dell XPS 13'
                                            ],
                                            'description' => [
                                                'type' => 'string',
                                                'description' => 'Descripción del producto',
                                                'example' => 'Laptop ultrabook con pantalla de 13 pulgadas'
                                            ],
                                            'price' => [
                                                'type' => 'number',
                                                'format' => 'float',
                                                'description' => 'Precio del producto',
                                                'example' => 1299.99
                                            ],
                                            'stock' => [
                                                'type' => 'integer',
                                                'description' => 'Cantidad en stock',
                                                'example' => 50
                                            ],
                                            'variants' => [
                                                'type' => 'array',
                                                'description' => 'Lista de variantes del producto (ej: colores, tallas, etc.)',
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['name', 'value'],
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                            'description' => 'Nombre de la variante (ej: Color, Talla)',
                                                            'example' => 'Color'
                                                        ],
                                                        'value' => [
                                                            'type' => 'string',
                                                            'description' => 'Valor de la variante (ej: Negro, XL)',
                                                            'example' => 'Negro'
                                                        ]
                                                    ],
                                                    'example' => [
                                                        'name' => 'Color',
                                                        'value' => 'Negro'
                                                    ]
                                                ],
                                                'example' => [
                                                    [
                                                        'name' => 'Color',
                                                        'value' => 'Negro'
                                                    ],
                                                    [
                                                        'name' => 'Talla',
                                                        'value' => 'XL'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Producto creado exitosamente',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => [
                                                    'type' => 'string',
                                                    'example' => 'Producto creado exitosamente'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '400' => [
                                'description' => 'Datos de entrada inválidos',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'error' => [
                                                    'type' => 'string',
                                                    'example' => 'Faltan campos obligatorios'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '/products/{id}' => [
                    'put' => [
                        'tags' => ['Productos'],
                        'summary' => 'Actualizar un producto existente',
                        'description' => 'Actualiza los datos de un producto existente por su ID',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'ID único del producto',
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'uuid'
                                ]
                            ]
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'name' => [
                                                'type' => 'string',
                                                'description' => 'Nuevo nombre del producto'
                                            ],
                                            'description' => [
                                                'type' => 'string',
                                                'description' => 'Nueva descripción del producto'
                                            ],
                                            'price' => [
                                                'type' => 'number',
                                                'format' => 'float',
                                                'description' => 'Nuevo precio del producto'
                                            ],
                                            'stock' => [
                                                'type' => 'integer',
                                                'description' => 'Nueva cantidad en stock'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Producto actualizado exitosamente',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => [
                                                    'type' => 'string',
                                                    'example' => 'Producto actualizado exitosamente'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'delete' => [
                        'tags' => ['Productos'],
                        'summary' => 'Eliminar un producto',
                        'description' => 'Elimina un producto del sistema por su ID',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'ID único del producto a eliminar',
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'uuid'
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Producto eliminado exitosamente',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => [
                                                    'type' => 'string',
                                                    'example' => 'Producto eliminado exitosamente'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return new Response($twig->render('@NelmioApiDoc/SwaggerUi/index.html.twig', [
            'swagger_data' => [
                'spec' => $spec,
                'openapi' => $spec['openapi'],
                'info' => $spec['info'],
                'servers' => $spec['servers'],
                'paths' => $spec['paths']
            ],
            'swagger_ui_config' => [
                'dom_id' => '#swagger-ui',
                'url' => null,
                'validatorUrl' => null,
                'oauth2RedirectUrl' => null,
                'requestInterceptor' => null,
                'responseInterceptor' => null,
                'showMutatedRequest' => true,
                'defaultModelExpandDepth' => 1,
                'defaultModelsExpandDepth' => 1,
                'defaultModelRendering' => 'example',
                'displayRequestDuration' => false,
                'docExpansion' => 'none',
                'filter' => false,
                'showExtensions' => false,
                'showCommonExtensions' => false,
                'syntaxHighlight' => [
                    'activated' => true,
                    'theme' => 'agate'
                ],
                'tryItOutEnabled' => true,
                'supportedSubmitMethods' => ['get', 'post', 'put', 'delete', 'patch']
            ]
        ]));
    }
} 