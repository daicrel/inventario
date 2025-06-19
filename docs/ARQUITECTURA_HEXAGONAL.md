# Arquitectura Hexagonal - Sistema de Inventario

## 1. Resumen de Implementación

Este proyecto implementa la **Arquitectura Hexagonal** (también conocida como Arquitectura de Puertos y Adaptadores) siguiendo los principios de Clean Architecture. La implementación cumple con los requisitos solicitados:

✅ **El dominio no depende de infraestructura**  
✅ **Implementa al menos dos adaptadores de persistencia**  
✅ **Documenta la comunicación entre puertos y adaptadores**

## 2. Estructura del Proyecto

### 2.1. Diagrama de Carpetas

```
src/
├── Domain/                    # Capa de Dominio (Núcleo)
│   ├── Product/              # Agregado Producto
│   │   ├── Entity/           # Entidades de dominio
│   │   ├── Repository/       # Puerto de persistencia
│   │   ├── ValueObject/      # Objetos de valor
│   │   └── Event/            # Eventos de dominio
│   └── Notification/         # Puerto de notificación
├── Application/              # Capa de Aplicación
│   └── Product/
│       ├── Command/          # Comandos
│       ├── Handler/          # Casos de uso
│       ├── DTO/              # Objetos de transferencia
│       └── EventListener/    # Manejadores de eventos
└── Infrastructure/           # Capa de Infraestructura
    ├── Product/
    │   ├── Repository/       # Adaptadores de persistencia
    │   └── Controller/       # Adaptadores de entrada
    └── Notification/         # Adaptadores de notificación
```

## 3. Puertos y Adaptadores

### 3.1. Puerto de Persistencia de Productos

#### 3.1.1. Interfaz del Puerto

```php
interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
}
```

#### 3.1.2. Adaptadores de Persistencia Implementados

- **a) DoctrineProductRepository (MySQL)**
  - **Ubicación:** `src/Infrastructure/Product/Repository/DoctrineProductRepository.php`
  - **Tecnología:** Doctrine ORM + MySQL
  - **Uso:** Producción
  - **Configuración:** Activo por defecto en `config/services.yaml`

- **b) InMemoryProductRepository (Sesión)**
  - **Ubicación:** `src/Infrastructure/Product/Repository/ProductRepository.php`
  - **Tecnología:** Symfony Session Storage
  - **Uso:** Desarrollo y pruebas
  - **Configuración:** Comentado en `config/services.yaml`

- **c) FakeProductRepository (Memoria para tests)**
  - **Ubicación:** `tests/Fake/FakeProductRepository.php`
  - **Tecnología:** Array en memoria
  - **Uso:** Tests unitarios
  - **Configuración:** No requiere configuración de servicios

### 3.2. Puerto de Notificación

#### 3.2.1. Interfaz del Puerto

```php
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}
```

#### 3.2.2. Adaptadores de Notificación Implementados

- **a) SmtpMailer (SMTP)**
  - **Ubicación:** `src/Infrastructure/Notification/SmtpMailer.php`
  - **Tecnología:** Symfony Mailer + SMTP
  - **Uso:** Producción y desarrollo
  - **Configuración:** Activo por defecto en `config/services.yaml`

- **b) SesMailer (Amazon SES)**
  - **Ubicación:** `src/Infrastructure/Notification/SesMailer.php`
  - **Tecnología:** AWS SDK for PHP
  - **Uso:** Producción (alta escalabilidad)
  - **Configuración:** Requiere credenciales AWS en variables de entorno

- **c) SendGridMailer (SendGrid)**
  - **Ubicación:** `src/Infrastructure/Notification/SendGridMailer.php`
  - **Tecnología:** SendGrid PHP SDK
  - **Uso:** Producción (buena entrega)
  - **Configuración:** Requiere API Key de SendGrid

- **d) LogMailer (Desarrollo/Pruebas)**
  - **Ubicación:** `src/Infrastructure/Notification/LogMailer.php`
  - **Tecnología:** PSR-3 Logger
  - **Uso:** Desarrollo y pruebas
  - **Configuración:** Solo registra en logs, no envía emails reales

#### 3.2.3. Factory para Gestión de Servicios

- **EmailServiceFactory**
  - **Ubicación:** `src/Infrastructure/Notification/EmailServiceFactory.php`
  - **Propósito:** Facilita el cambio entre diferentes servicios de email
  - **Uso:** Permite seleccionar dinámicamente el servicio a usar

## 4. Comunicación entre Capas

### 4.1. Flujo de Creación de Producto

#### 4.1.1. Adaptador de Entrada (Controller)

```php
// ProductController recibe HTTP request
public function create(Request $request, CreateProductHandler $handler)
```

#### 4.1.2. Capa de Aplicación (Handler)

```php
// CreateProductHandler orquesta el caso de uso
public function __invoke(CreateProductCommand $command)
{
    // Usa el puerto de persistencia
    $this->productRepository->save($product);
    
    // Despacha evento de dominio
    $this->eventDispatcher->dispatch(new ProductCreatedDomainEvent(...));
}
```

#### 4.1.3. Capa de Dominio (Entidad)

```php
// Product contiene la lógica de negocio
public function __construct(ProductId $id, ProductName $name, ...)
{
    // Validaciones de dominio
    if ($price < 0) {
        throw new \InvalidArgumentException('El precio no puede ser negativo');
    }
}
```

#### 4.1.4. Adaptador de Persistencia

```php
// DoctrineProductRepository implementa el puerto
public function save(Product $product): void
{
    $this->em->persist($product);
    $this->em->flush();
}
```

#### 4.1.5. Adaptador de Notificación

```php
// ProductCreatedListener responde al evento
public function __invoke(ProductCreatedDomainEvent $event): void
{
    // Usa el puerto de notificación
    $this->emailSender->send($to, $subject, $body);
}
```

## 5. Configuración de Adaptadores

### 5.1. Cambiar Adaptador de Persistencia

En `config/services.yaml`:

```yaml
# Para usar MySQL (Producción)
App\Domain\Product\Repository\ProductRepository: '@App\Infrastructure\Product\Repository\DoctrineProductRepository'

# Para usar Sesión (Desarrollo)
# App\Domain\Product\Repository\ProductRepository: '@App\Infrastructure\Product\Repository\InMemoryProductRepository'
```

### 5.2. Agregar Nuevo Adaptador

1. **Crear nueva implementación:**
   ```php
   class FileProductRepository implements ProductRepository
   {
       public function save(Product $product): void
       {
           // Lógica de persistencia en archivo
       }
       // ... otros métodos
   }
   ```

2. **Configurar en services.yaml:**
   ```yaml
   App\Domain\Product\Repository\ProductRepository: '@App\Infrastructure\Product\Repository\FileProductRepository'
   ```

## 6. Beneficios de esta Arquitectura

### 6.1. Independencia del Dominio

- El dominio no conoce detalles de infraestructura
- Las entidades no tienen anotaciones de Doctrine (excepto para mapeo)
- Los casos de uso dependen de interfaces, no implementaciones

### 6.2. Testabilidad

- Fácil mockeo de dependencias
- Tests unitarios sin base de datos
- Tests de integración con adaptadores reales

### 6.3. Flexibilidad

- Cambio de base de datos sin modificar lógica de negocio
- Múltiples adaptadores para diferentes contextos
- Fácil agregar nuevos adaptadores

### 6.4. Separación de Responsabilidades

- Dominio: Lógica de negocio
- Aplicación: Casos de uso
- Infraestructura: Detalles técnicos

## 7. Verificación de Cumplimiento

✅ **El dominio no depende de infraestructura:**
- Las entidades del dominio no importan clases de infraestructura
- Los repositorios del dominio son interfaces
- Los eventos de dominio son independientes

✅ **Implementa al menos dos adaptadores de persistencia:**
- `DoctrineProductRepository` (MySQL)
- `InMemoryProductRepository` (Sesión)
- `FakeProductRepository` (Memoria para tests)

✅ **Implementa varios servicios de email usando una interfaz común:**
- `SmtpMailer` (SMTP)
- `SesMailer` (Amazon SES)
- `SendGridMailer` (SendGrid)
- `LogMailer` (Desarrollo/Pruebas)
- Todos implementan `EmailSenderInterface`

✅ **El listener del evento usa la interfaz, no la implementación concreta:**
- `ProductCreatedListener` inyecta `EmailSenderInterface`
- No depende de implementaciones específicas
- Permite cambiar el servicio de email sin modificar el listener

✅ **Documenta cómo se comunican los puertos y adaptadores:**
- Este documento explica la arquitectura
- Los flujos están documentados con ejemplos de código
- La configuración está comentada en `services.yaml` 