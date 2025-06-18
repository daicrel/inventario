# Lenguaje Ubicuo y Límites de Agregados

## Lenguaje Ubicuo

- **Producto**: Entidad central del sistema, representa un artículo vendible en la tienda. Un producto tiene un nombre, una descripción, un precio base y una cantidad en stock general.
- **Variante**: Representa una versión específica de un producto (por ejemplo, un color o tamaño diferente). Cada variante tiene su propio identificador, nombre, precio, cantidad en stock e imagen asociada.
- **Precio**: Objeto de valor que encapsula la lógica y validación del precio de un producto o variante. Garantiza que el precio nunca sea negativo.
- **Nombre de producto**: Objeto de valor que encapsula la lógica y validación del nombre de un producto o variante. Garantiza que el nombre no esté vacío.
- **Evento de dominio**: Objeto que representa un hecho relevante ocurrido en el dominio, como la creación de un producto. Permite desacoplar acciones secundarias (por ejemplo, notificaciones) de la lógica principal.
- **Agregado**: Conjunto de entidades y objetos de valor que se tratan como una unidad para la consistencia de datos. El agregado raíz es el único punto de acceso externo.
- **Identidad**: Cada entidad (Producto, Variante) tiene un identificador único (ProductId, VariantId) que la distingue dentro del sistema.
- **Invariante**: Regla de negocio que siempre debe cumplirse, como que el precio y el stock no pueden ser negativos.

## Límite del Agregado

- El **Producto** es el agregado raíz. Todas las operaciones externas (crear, modificar, eliminar variantes) deben realizarse a través del Producto.
- Las **Variantes** son entidades internas del agregado Producto. No deben ser manipuladas directamente desde fuera del agregado.
- El **Producto** garantiza la consistencia de sus invariantes y de las variantes asociadas.
- Los objetos de valor (como Precio y Nombre) encapsulan reglas de validación y se usan tanto en Producto como en Variante.

## Ejemplo de interacción

- Para crear una variante, se debe invocar un método del Producto, no crear la Variante directamente desde fuera.
- Cuando se crea un Producto, se lanza un **evento de dominio** (`ProductCreatedDomainEvent`) que puede ser escuchado por otros servicios (por ejemplo, para enviar un email de notificación).

## Ejemplo de código (resumido)

```php
$product = new Product(
    new ProductId('prod-123'),
    new ProductName('Camiseta'),
    new Price(19.99),
    'Camiseta básica de algodón',
    100 
);

$product->addVariant(
    new VariantId('var-001'),
    new ProductName('Camiseta Roja - M'),
    new Price(21.99),
    10,
    'camiseta-roja-m.jpg'
);

$product->addVariant(
    new VariantId('var-002'),
    new ProductName('Camiseta Azul - L'),
    new Price(22.99),
    5,
    'camiseta-azul-l.jpg'
);
```

## Resumen

- El **Producto** es el único punto de acceso para modificar variantes.
- Las **Variantes** no se manipulan directamente desde fuera del agregado.
- Los **objetos de valor** aseguran la validez de los datos.
- Los **eventos de dominio** permiten reaccionar a cambios importantes en el dominio de forma desacoplada.