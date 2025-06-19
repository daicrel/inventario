# Implementación CQRS (Command Query Responsibility Segregation)

## Descripción General

Se ha implementado el patrón CQRS para separar claramente las operaciones de lectura (queries) de las operaciones de escritura (commands) en el sistema de inventario de productos.

## Estructura de Archivos

### Commands (Comandos)
```
src/Application/Product/Command/
├── CreateProductCommand.php
├── UpdateProductCommand.php
├── DeleteProductCommand.php
└── UpdateVariantCommand.php

src/Application/Product/Handler/
├── CreateProductHandler.php
├── UpdateProductHandler.php
├── DeleteProductHandler.php
└── UpdateVariantHandler.php
```

### Queries (Consultas)
```
src/Application/Product/Query/
├── GetAllProductsQuery.php
└── GetProductByIdQuery.php

src/Application/Product/Query/Handler/
├── GetAllProductsHandler.php
└── GetProductByIdHandler.php

src/Application/Product/Query/Response/
└── ProductResponse.php
```

### Controladores
```
src/Infrastructure/Product/Controller/
├── ProductController.php          # Solo comandos (POST, PUT, DELETE)
└── ProductQueryController.php     # Solo consultas (GET)
```

## Separación de Responsabilidades

### ProductController (Comandos)
- **POST** `/commands/products` - Crear producto
- **PUT** `/commands/products/{id}` - Actualizar producto
- **DELETE** `/commands/products/{id}` - Eliminar producto
- **PUT** `/commands/products/{productId}/variants/{variantId}` - Actualizar variante

### ProductQueryController (Consultas)
- **GET** `/queries/products` - Listar todos los productos
- **GET** `/queries/products/{id}` - Obtener producto por ID

## Beneficios de la Implementación CQRS

### 1. Separación Clara de Responsabilidades
- Los comandos solo modifican el estado del sistema
- Las consultas solo leen datos sin efectos secundarios
- Cada controlador tiene una responsabilidad específica

### 2. Escalabilidad
- Las consultas pueden optimizarse independientemente de los comandos
- Posibilidad de usar diferentes bases de datos para lectura y escritura
- Mejor rendimiento en operaciones de solo lectura

### 3. Mantenibilidad
- Código más organizado y fácil de entender
- Tests más específicos y aislados
- Cambios en consultas no afectan comandos y viceversa

### 4. Flexibilidad
- DTOs específicos para respuestas de consultas
- Posibilidad de implementar cache en consultas
- Optimizaciones específicas por tipo de operación

## DTOs de Respuesta

### ProductResponse
```php
class ProductResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public float $price,
        public int $stock,
        public array $variants = []
    ) {}
}
```

Este DTO se usa exclusivamente para las respuestas de las consultas, proporcionando una estructura consistente y optimizada para la presentación de datos.

## Testing

### Tests de Commands
- `tests/Application/Product/CreateProductHandlerTest.php`
- `tests/Application/Product/UpdateProductHandlerTest.php`
- `tests/Application/Product/DeleteProductHandlerTest.php`
- `tests/Application/Product/UpdateVariantHandlerTest.php`

### Tests de Queries
- `tests/Application/Product/GetAllProductsHandlerTest.php`
- `tests/Application/Product/GetProductByIdHandlerTest.php`
- `tests/Infrastructure/Product/Controller/ProductQueryControllerTest.php`

## Documentación OpenAPI

La documentación OpenAPI se ha actualizado para reflejar la separación:

- **Comandos de Productos**: Tag para operaciones de escritura
- **Consultas de Productos**: Tag para operaciones de lectura

## Ejemplo de Uso

### Crear un Producto (Command)
```bash
POST /commands/products
{
    "name": "Laptop Dell XPS 13",
    "description": "Laptop ultrabook",
    "price": 1299.99,
    "stock": 50,
    "variants": [...]
}
```

### Obtener Productos (Query)
```bash
GET /queries/products
# Retorna lista de ProductResponse
```

### Obtener Producto Específico (Query)
```bash
GET /queries/products/{id}
# Retorna ProductResponse específico
```

## Ventajas de esta Implementación

1. **Claridad**: Es evidente qué endpoints modifican datos y cuáles solo los leen
2. **Performance**: Las consultas pueden optimizarse sin afectar los comandos
3. **Escalabilidad**: Posibilidad de escalar consultas y comandos independientemente
4. **Mantenimiento**: Cambios en un lado no afectan el otro
5. **Testing**: Tests más específicos y aislados
6. **Documentación**: API más clara y organizada

## Próximos Pasos

1. Implementar cache para consultas frecuentes
2. Considerar separación de bases de datos para lectura/escritura
3. Implementar proyecciones específicas para diferentes vistas
4. Agregar más queries especializadas según necesidades del negocio 