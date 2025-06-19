# Documentación de Endpoints y Pruebas

## Endpoints CQRS

### Comandos (Commands) - Escritura

- **Crear producto**
  - Método: `POST`
  - URL: `http://localhost:8000/commands/products`

- **Actualizar producto**
  - Método: `PUT`
  - URL: `http://localhost:8000/commands/products/{id}`

- **Eliminar producto**
  - Método: `DELETE`
  - URL: `http://localhost:8000/commands/products/{id}`

- **Actualizar variante**
  - Método: `PUT`
  - URL: `http://localhost:8000/commands/products/{productId}/variants/{variantId}`

### Consultas (Queries) - Lectura

- **Listar productos**
  - Método: `GET`
  - URL: `http://localhost:8000/queries/products`

- **Obtener producto por ID**
  - Método: `GET`
  - URL: `http://localhost:8000/queries/products/{id}`

---

## Ejemplo de JSON para crear/actualizar producto

### Crear producto:
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

### Actualizar producto:
```json
{
  "name": "Laptop Dell XPS 13 Pro",
  "description": "Laptop ultrabook premium con pantalla de 13 pulgadas y procesador Intel i7",
  "price": 1499.99,
  "stock": 25,
  "variants": [
    {
      "name": "Plata - 512GB SSD",
      "price": 1599.99,
      "stock": 15,
      "image": "xps13_plata_512gb.jpg"
    }
  ]
}
```

---

## Pruebas de Endpoints (manual)

Puedes probar los endpoints usando herramientas como Postman, Insomnia o cURL. Ejemplo con cURL:

- Crear producto:
  ```bash
  curl -X POST http://localhost:8000/commands/products \
    -H "Content-Type: application/json" \
    -d '{
      "name": "Laptop Dell XPS 13",
      "description": "Laptop ultrabook con pantalla de 13 pulgadas",
      "price": 1299.99,
      "stock": 50,
      "variants": [
        {"name": "Blanco - Talla 42", "price": 119.99, "stock": 40, "image": "pegasus_blanco_42.jpg"}
      ]
    }'
  ```

- Actualizar producto:
  ```bash
  curl -X PUT http://localhost:8000/commands/products/{id} \
    -H "Content-Type: application/json" \
    -d '{
      "name": "Laptop Dell XPS 13 Actualizada",
      "price": 1399.99
    }'
  ```

- Listar productos:
  ```bash
  curl http://localhost:8000/queries/products
  ```

- Obtener producto por ID:
  ```bash
  curl http://localhost:8000/queries/products/{id}
  ```

---

## Pruebas Unitarias y Funcionales

- Ejecutar **todos los tests**:
  ```bash
  php bin/phpunit
  ```

- Ejecutar **tests unitarios de Application**:
  ```bash
  php bin/phpunit tests/Application/Product/
  ```

- Ejecutar **tests funcionales de los controladores**:
  ```bash
  php bin/phpunit tests/Infrastructure/Product/Controller/
  ```

---

## Documentación Swagger UI

- Accede a la documentación interactiva en:
  - [http://localhost:8000/api/doc](http://localhost:8000/api/doc)

---

**Nota:** Cambia `{id}` y `{productId}`/`{variantId}` por los valores reales de tus productos/variantes. 