# Operaciones CRUD de Productos

Este documento describe las operaciones disponibles para gestionar productos en el sistema de inventario.

## Arquitectura

El sistema sigue los principios de **Arquitectura Hexagonal (DDD)**:

- **Capa de Aplicación**: Contiene los comandos y handlers
- **Capa de Dominio**: Contiene las entidades y puertos (interfaces)
- **Capa de Infraestructura**: Contiene las implementaciones concretas

## Operaciones Disponibles

### 1. Crear Producto

**Endpoint**: `POST /products`

**Comando**: `CreateProductCommand`
**Handler**: `CreateProductHandler`

**Ejemplo de request**:
```json
{
    "name": "Laptop Gaming",
    "description": "Laptop para gaming de alto rendimiento",
    "price": 1299.99,
    "stock": 10,
    "variants": [
        {
            "name": "16GB RAM",
            "price": 1299.99,
            "stock": 5,
            "image": "laptop-16gb.jpg"
        },
        {
            "name": "32GB RAM",
            "price": 1499.99,
            "stock": 5,
            "image": "laptop-32gb.jpg"
        }
    ]
}
```

### 2. Actualizar Producto

**Endpoint**: `PUT /products/{id}`

**Comando**: `UpdateProductCommand`
**Handler**: `UpdateProductHandler`

**Características**:
- Todos los campos son opcionales
- Solo se actualizan los campos proporcionados
- Validación de nombres únicos
- Actualización completa de variantes

**Ejemplo de request**:
```json
{
    "name": "Laptop Gaming Pro",
    "price": 1399.99,
    "stock": 15
}
```

### 3. Eliminar Producto

**Endpoint**: `DELETE /products/{id}`

**Comando**: `DeleteProductCommand`
**Handler**: `DeleteProductHandler`

**Características**:
- Elimina el producto y todas sus variantes
- Validación de existencia del producto

## Puertos y Adaptadores

### Puerto (Interfaz)
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