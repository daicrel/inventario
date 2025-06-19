# Operaciones de Producto

## Endpoints Disponibles

### Comandos (Commands)

#### Crear Producto
**Endpoint**: `POST /commands/products`

**Descripción**: Crea un nuevo producto con sus variantes en el sistema.

**Cuerpo de la petición**:
```json
{
    "name": "Laptop Dell XPS 13",
    "description": "Laptop ultrabook con pantalla de 13 pulgadas",
    "price": 1299.99,
    "stock": 50,
    "variants": [
        {
            "name": "Blanco - Talla 42",
            "price": 119.99,
            "stock": 40,
            "image": "pegasus_blanco_42.jpg"
        }
    ]
}
```

**Respuesta exitosa** (201):
```json
{
    "message": "Producto creado exitosamente"
}
```

#### Actualizar Producto
**Endpoint**: `PUT /commands/products/{id}`

**Descripción**: Actualiza los datos de un producto existente.

**Cuerpo de la petición**:
```json
{
    "name": "Laptop Dell XPS 13 Actualizada",
    "description": "Nueva descripción del producto",
    "price": 1399.99,
    "stock": 60
}
```

**Respuesta exitosa** (200):
```json
{
    "message": "Producto actualizado exitosamente"
}
```

#### Eliminar Producto
**Endpoint**: `DELETE /commands/products/{id}`

**Descripción**: Elimina un producto del sistema.

**Respuesta exitosa** (200):
```json
{
    "message": "Producto eliminado exitosamente"
}
```

### Consultas (Queries)

#### Listar Productos
**Endpoint**: `GET /queries/products`

**Descripción**: Obtiene la lista completa de productos con sus variantes.

**Respuesta exitosa** (200):
```json
[
    {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "Laptop Dell XPS 13",
        "description": "Laptop ultrabook con pantalla de 13 pulgadas",
        "price": 1299.99,
        "stock": 50,
        "variants": [
            {
                "id": "550e8400-e29b-41d4-a716-446655440001",
                "name": "Blanco - Talla 42",
                "price": 119.99,
                "stock": 40,
                "image": "pegasus_blanco_42.jpg"
            }
        ]
    }
]
```

#### Obtener Producto por ID
**Endpoint**: `GET /queries/products/{id}`

**Descripción**: Obtiene los datos de un producto específico con sus variantes.

**Respuesta exitosa** (200):
```json
{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "Laptop Dell XPS 13",
    "description": "Laptop ultrabook con pantalla de 13 pulgadas",
    "price": 1299.99,
    "stock": 50,
    "variants": [
        {
            "id": "550e8400-e29b-41d4-a716-446655440001",
            "name": "Blanco - Talla 42",
            "price": 119.99,
            "stock": 40,
            "image": "pegasus_blanco_42.jpg"
        }
    ]
}
```

## Arquitectura

### Patrón CQRS
La API implementa el patrón **Command Query Responsibility Segregation (CQRS)**:

- **Commands** (`/commands/products`): Operaciones de escritura (POST, PUT, DELETE)
- **Queries** (`/queries/products`): Operaciones de lectura (GET)

### Separación de Responsabilidades
- **ProductController**: Maneja todos los comandos
- **ProductQueryController**: Maneja todas las consultas
- **Handlers**: Lógica de aplicación específica para cada operación
- **DTOs**: Estructuras de datos optimizadas para respuestas

## Repositorio

### Interface
```php
interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
    public function delete(Product $product): void;
}
```

### Adaptadores Disponibles

1. **DoctrineProductRepository**: Para producción con base de datos MySQL
2. **InMemoryProductRepository**: Para pruebas y desarrollo

## Configuración

En `config/services.yaml` puedes alternar entre repositorios:

```yaml
# Para desarrollo/pruebas (en memoria)
App\Domain\Product\Repository\ProductRepository: '@App\Infrastructure\Product\Repository\InMemoryProductRepository'

# Para producción (MySQL)
App\Domain\Product\Repository\ProductRepository: '@App\Infrastructure\Product\Repository\DoctrineProductRepository'
```

## Validaciones

### Crear Producto
- Nombre obligatorio
- Variantes obligatorias (array)
- Precio obligatorio y numérico
- Stock obligatorio y numérico
- Nombre único en el sistema

### Actualizar Producto
- ID de producto válido
- Nombre único (si se proporciona)
- Precio no negativo
- Stock no negativo

### Eliminar Producto
- ID de producto válido
- Producto debe existir

## Manejo de Errores

Todos los endpoints devuelven respuestas JSON con códigos de estado HTTP apropiados:

- `200`: Operación exitosa
- `201`: Producto creado exitosamente
- `400`: Datos de entrada inválidos
- `422`: Error de dominio (validaciones de negocio)
- `500`: Error interno del servidor

## Ejemplos de Respuestas

### Error de validación
```json
{
    "error": "Ya existe un producto con ese nombre."
}
```

### Éxito
```json
{
    "message": "Producto actualizado exitosamente"
}
```

## Testing

Los handlers pueden ser probados de forma aislada usando el `InMemoryProductRepository`:

```php
$repository = new InMemoryProductRepository();
$handler = new CreateProductHandler($repository, $eventDispatcher);
$command = new CreateProductCommand(...);
$handler->__invoke($command);
``` 