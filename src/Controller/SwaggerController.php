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
                                                    'required' => ['name', 'price', 'stock'],
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                            'description' => 'Nombre de la variante',
                                                            'example' => 'Blanco - Talla 42'
                                                        ],
                                                        'price' => [
                                                            'type' => 'number',
                                                            'format' => 'float',
                                                            'description' => 'Precio de la variante',
                                                            'example' => 119.99
                                                        ],
                                                        'stock' => [
                                                            'type' => 'integer',
                                                            'description' => 'Stock de la variante',
                                                            'example' => 40
                                                        ],
                                                        'image' => [
                                                            'type' => 'string',
                                                            'description' => 'Imagen de la variante',
                                                            'example' => 'pegasus_blanco_42.jpg'
                                                        ]
                                                    ],
                                                    'example' => [
                                                        'name' => 'Blanco - Talla 42',
                                                        'price' => 119.99,
                                                        'stock' => 40,
                                                        'image' => 'pegasus_blanco_42.jpg'
                                                    ]
                                                ],
                                                'example' => [
                                                    [
                                                        'name' => 'Blanco - Talla 42',
                                                        'price' => 119.99,
                                                        'stock' => 40,
                                                        'image' => 'pegasus_blanco_42.jpg'
                                                    ],
                                                    [
                                                        'name' => 'Negro - Talla 43',
                                                        'price' => 119.99,
                                                        'stock' => 30,
                                                        'image' => 'pegasus_negro_43.jpg'
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
                    ],
                    'get' => [
                        'tags' => ['Productos'],
                        'summary' => 'Listar todos los productos',
                        'description' => 'Obtiene la lista completa de productos con sus variantes',
                        'responses' => [
                            '200' => [
                                'description' => 'Lista de productos obtenida exitosamente',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => [
                                                        'type' => 'string',
                                                        'format' => 'uuid',
                                                        'example' => '550e8400-e29b-41d4-a716-446655440000'
                                                    ],
                                                    'name' => [
                                                        'type' => 'string',
                                                        'example' => 'Laptop Dell XPS 13'
                                                    ],
                                                    'description' => [
                                                        'type' => 'string',
                                                        'example' => 'Laptop ultrabook con pantalla de 13 pulgadas'
                                                    ],
                                                    'price' => [
                                                        'type' => 'number',
                                                        'format' => 'float',
                                                        'example' => 1299.99
                                                    ],
                                                    'stock' => [
                                                        'type' => 'integer',
                                                        'example' => 50
                                                    ],
                                                    'variants' => [
                                                        'type' => 'array',
                                                        'items' => [
                                                            'type' => 'object',
                                                            'properties' => [
                                                                'id' => [
                                                                    'type' => 'string',
                                                                    'format' => 'uuid',
                                                                    'example' => '550e8400-e29b-41d4-a716-446655440001'
                                                                ],
                                                                'name' => [
                                                                    'type' => 'string',
                                                                    'example' => 'Blanco - Talla 42'
                                                                ],
                                                                'price' => [
                                                                    'type' => 'number',
                                                                    'format' => 'float',
                                                                    'example' => 119.99
                                                                ],
                                                                'stock' => [
                                                                    'type' => 'integer',
                                                                    'example' => 40
                                                                ],
                                                                'image' => [
                                                                    'type' => 'string',
                                                                    'example' => 'pegasus_blanco_42.jpg'
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '500' => [
                                'description' => 'Error interno del servidor',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'error' => [
                                                    'type' => 'string'
                                                ],
                                                'trace' => [
                                                    'type' => 'string'
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
                ],
                '/products/{productId}/variants/{variantId}' => [
                    'put' => [
                        'tags' => ['Productos'],
                        'summary' => 'Actualizar una variante de producto',
                        'description' => 'Actualiza los datos de una variante específica de un producto',
                        'parameters' => [
                            [
                                'name' => 'productId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'ID único del producto',
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'uuid'
                                ]
                            ],
                            [
                                'name' => 'variantId',
                                'in' => 'path',
                                'required' => true,
                                'description' => 'ID único de la variante',
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
                                                'description' => 'Nuevo nombre de la variante',
                                                'example' => 'Color Azul'
                                            ],
                                            'price' => [
                                                'type' => 'number',
                                                'format' => 'float',
                                                'description' => 'Nuevo precio de la variante',
                                                'example' => 25.99
                                            ],
                                            'stock' => [
                                                'type' => 'integer',
                                                'description' => 'Nueva cantidad en stock de la variante',
                                                'example' => 15
                                            ],
                                            'image' => [
                                                'type' => 'string',
                                                'description' => 'Nueva imagen de la variante',
                                                'example' => 'imagen_azul.jpg'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Variante actualizada exitosamente',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => [
                                                    'type' => 'string',
                                                    'example' => 'Variante actualizada exitosamente'
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
                                                    'example' => 'No se proporcionaron datos para actualizar'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '422' => [
                                'description' => 'Error de validación del dominio',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'error' => [
                                                    'type' => 'string',
                                                    'example' => 'Producto no encontrado'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '500' => [
                                'description' => 'Error interno del servidor',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'error' => [
                                                    'type' => 'string'
                                                ],
                                                'trace' => [
                                                    'type' => 'string'
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