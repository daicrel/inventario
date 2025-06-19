# Sistema de Gestión de Inventario

Proyecto desarrollado como prueba técnica para el proceso de selección. 
Implementa arquitectura hexagonal, DDD, CQRS y buenas prácticas SOLID con Symfony 6.2.

## Descripción

Este sistema permite gestionar productos y sus variantes para una tienda en línea. 
Incluye persistencia en MySQL, eventos de dominio y notificación por correo electrónico usando diferentes proveedores.

## Características principales

- **Arquitectura hexagonal**: Separación clara entre dominio, aplicación e infraestructura.
- **DDD**: Entidades, objetos de valor, eventos de dominio, agregados y lenguaje ubicuo.
- **CQRS**: Separación de comandos y consultas.
- **SOLID**: Código desacoplado, extensible y fácil de mantener.
- **Notificaciones**: Envío de correos electrónicos al crear productos, soportando múltiples servicios (SMTP, SES, SendGrid, etc.).
- **API REST**: Endpoints para gestión de productos y variantes, con documentación Swagger.

## Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/daicrel/inventario.git
   cd inventario
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar la base de datos**
   - Copiar el archivo `.env` y configurar los parámetros de la base de datos MySQL.

4. **Ejecutar migraciones**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Levantar el servidor**
   ```bash
   symfony server:start
   ```
   o
   ```bash
   php -S localhost:8000 -t public
   ```

## Uso

- Acceder a la documentación Swagger en:  
  [http://localhost:8000/api/doc](http://localhost:8000/api/doc)

- Probar los endpoints para crear productos, consultar productos, etc.

## Tests

Para ejecutar los tests:
```bash
php bin/phpunit
```

## Notas

- El sistema de notificaciones por correo es polimórfico y fácilmente extensible.
- El código está comentado y estructurado para facilitar la comprensión.
- Se pueden añadir nuevos adaptadores de persistencia o servicios de correo siguiendo los principios de la arquitectura.

---

Para cualquier duda o aclaración, no dudes en ponerte en contacto. 