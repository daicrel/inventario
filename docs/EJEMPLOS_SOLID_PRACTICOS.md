# Ejemplos Prácticos de Principios SOLID

## Introducción

Este documento proporciona ejemplos concretos de código que demuestran cómo se aplican los principios SOLID en el sistema de inventario.

## 1. Principio de Responsabilidad Única (SRP)

### 1.1. Ejemplo: Separación de Responsabilidades en Handlers

```php
// ✅ BUENO: Cada handler tiene una única responsabilidad
final class CreateProductHandler
{
    public function __invoke(CreateProductCommand $command): void
    {
        // ÚNICA RESPONSABILIDAD: Orquestar creación de producto
        $this->validateCommand($command);
        $product = $this->createProduct($command);
        $this->saveProduct($product);
        $this->dispatchEvent($product);
    }
}

final class UpdateProductHandler
{
    public function __invoke(UpdateProductCommand $command): void
    {
        // ÚNICA RESPONSABILIDAD: Orquestar actualización de producto
        $product = $this->findProduct($command->getProductId());
        $this->updateProductFields($product, $command);
        $this->saveProduct($product);
    }
}

final class DeleteProductHandler
{
    public function __invoke(DeleteProductCommand $command): void
    {
        // ÚNICA RESPONSABILIDAD: Orquestar eliminación de producto
        $product = $this->findProduct($command->getProductId());
        $this->deleteProduct($product);
    }
}
```

### 1.2. Ejemplo: Value Objects con Responsabilidad Única

```php
// ✅ BUENO: Cada Value Object encapsula una responsabilidad específica
final class ProductName
{
    private string $name;

    public function __construct(string $name)
    {
        // ÚNICA RESPONSABILIDAD: Validar y encapsular nombre de producto
        $this->validateName($name);
        $this->name = $name;
    }

    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException("El nombre no puede estar vacío.");
        }
        if (strlen($name) > 255) {
            throw new InvalidArgumentException("El nombre no puede exceder 255 caracteres.");
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

final class Price
{
    private float $value;

    public function __construct(float $value)
    {
        // ÚNICA RESPONSABILIDAD: Validar y encapsular precio
        if ($value < 0) {
            throw new InvalidArgumentException('El precio no puede ser negativo.');
        }
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }
}
```

### 1.3. Ejemplo: Entidades con Responsabilidad Única

```php
// ✅ BUENO: La entidad Product solo maneja su propio estado
class Product
{
    private string $id;
    private string $name;
    private string $description;
    private float $price;
    private int $stock;
    private Collection $variants;

    public function __construct(ProductId $id, ProductName $name, ...)
    {
        // ÚNICA RESPONSABILIDAD: Validar y mantener estado del producto
        $this->validateProductData($price, $stock);
        $this->id = (string)$id;
        $this->name = (string)$name;
        // ... resto de inicialización
    }

    // Métodos que solo afectan al estado del producto
    public function updateName(ProductName $name): void
    {
        $this->name = (string)$name;
    }

    public function updatePrice(float $price): void
    {
        if ($price < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo');
        }
        $this->price = $price;
    }

    public function addVariant(Variant $variant): void
    {
        $this->variants[] = $variant;
    }

    // NO maneja persistencia, eventos, ni lógica de aplicación
}
```

## 2. Principio de Abierto/Cerrado (OCP)

### 2.1. Ejemplo: Sistema de Email Extensible

```php
// ✅ BUENO: Interfaz cerrada para modificación, abierta para extensión
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}

// Nuevas implementaciones sin modificar código existente
class SmtpMailer implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Implementación SMTP
    }
}

class SesMailer implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Implementación AWS SES
    }
}

class SendGridMailer implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Implementación SendGrid
    }
}

// ✅ FÁCIL EXTENSIÓN: Agregar nuevo proveedor sin tocar código existente
class MailgunMailer implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Nueva implementación Mailgun
    }
}
```

### 2.2. Ejemplo: Factory Pattern para Extensibilidad

```php
// ✅ BUENO: Factory permite agregar nuevos servicios sin modificar lógica existente
class EmailServiceFactory
{
    public const SMTP = 'smtp';
    public const SES = 'ses';
    public const SENDGRID = 'sendgrid';
    public const LOG = 'log';
    // FÁCIL EXTENSIÓN: Agregar nuevas constantes
    public const MAILGUN = 'mailgun';

    public function create(string $service): EmailSenderInterface
    {
        return match ($service) {
            self::SMTP => $this->smtpMailer,
            self::SES => $this->sesMailer,
            self::SENDGRID => $this->sendGridMailer,
            self::LOG => $this->logMailer,
            // FÁCIL EXTENSIÓN: Agregar nuevos casos
            self::MAILGUN => $this->mailgunMailer,
            default => throw new InvalidArgumentException("Unknown service: {$service}")
        };
    }
}
```

### 2.3. Ejemplo: Repositorio Extensible

```php
// ✅ BUENO: Interfaz de repositorio permite múltiples implementaciones
interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
    public function delete(Product $product): void;
}

// Implementaciones existentes
class DoctrineProductRepository implements ProductRepository
{
    // Implementación con Doctrine ORM
}

class InMemoryProductRepository implements ProductRepository
{
    // Implementación en memoria para tests
}

// ✅ FÁCIL EXTENSIÓN: Nuevas implementaciones sin modificar código existente
class RedisProductRepository implements ProductRepository
{
    // Implementación con Redis
}

class MongoProductRepository implements ProductRepository
{
    // Implementación con MongoDB
}
```

## 3. Principio de Sustitución de Liskov (LSP)

### 3.1. Ejemplo: Sustitución de Implementaciones de Email

```php
// ✅ BUENO: Todas las implementaciones son sustituibles
class ProductCreatedListener
{
    public function __construct(private EmailSenderInterface $emailSender) {}

    public function __invoke(ProductCreatedDomainEvent $event): void
    {
        // Funciona igual con cualquier implementación
        $this->emailSender->send('admin@example.com', 'Nuevo producto', '...');
    }
}

// Uso con diferentes implementaciones
$listener = new ProductCreatedListener(new SmtpMailer($mailer));
$listener = new ProductCreatedListener(new SesMailer($sesClient));
$listener = new ProductCreatedListener(new SendGridMailer($sendGrid));
$listener = new ProductCreatedListener(new LogMailer($logger));

// Todas funcionan de la misma manera
```

### 3.2. Ejemplo: Sustitución de Repositorios

```php
// ✅ BUENO: Handlers funcionan con cualquier implementación de repositorio
class CreateProductHandler
{
    public function __construct(ProductRepository $productRepository)
    {
        // Funciona igual con cualquier implementación
        $this->productRepository = $productRepository;
    }

    public function __invoke(CreateProductCommand $command): void
    {
        // El código no cambia según la implementación
        $product = new Product(...);
        $this->productRepository->save($product);
    }
}

// Uso con diferentes implementaciones
$handler = new CreateProductHandler(new DoctrineProductRepository($em));
$handler = new CreateProductHandler(new InMemoryProductRepository($session));
$handler = new CreateProductHandler(new RedisProductRepository($redis));

// Todas funcionan de la misma manera
```

### 3.3. Ejemplo: Sustitución de Value Objects

```php
// ✅ BUENO: Value Objects son sustituibles en cualquier contexto
class Product
{
    public function updateName(ProductName $name): void
    {
        // Funciona igual con cualquier instancia de ProductName
        $this->name = (string)$name;
    }

    public function updatePrice(Price $price): void
    {
        // Funciona igual con cualquier instancia de Price
        $this->price = $price->value();
    }
}

// Uso con diferentes instancias
$product = new Product(...);

$product->updateName(new ProductName("Laptop"));
$product->updateName(new ProductName("Desktop"));
$product->updateName(new ProductName("Tablet"));

$product->updatePrice(new Price(100.0));
$product->updatePrice(new Price(200.0));
$product->updatePrice(new Price(150.0));

// Todas funcionan de la misma manera
```

## 4. Ejemplos de Violaciones y Soluciones

### 4.1. Violación de SRP - Ejemplo de Código Malo

```php
// ❌ MALO: Clase con múltiples responsabilidades
class ProductManager
{
    public function createProduct($data): void
    {
        // Responsabilidad 1: Validación
        $this->validateData($data);
        
        // Responsabilidad 2: Creación de entidad
        $product = new Product(...);
        
        // Responsabilidad 3: Persistencia
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        
        // Responsabilidad 4: Envío de email
        $this->emailService->sendNotification($product);
        
        // Responsabilidad 5: Logging
        $this->logger->log('Product created', $product);
        
        // Responsabilidad 6: Cache
        $this->cache->invalidate('products');
    }
}
```

### 4.2. Solución Aplicando SRP

```php
// ✅ BUENO: Separación de responsabilidades
final class CreateProductHandler
{
    public function __invoke(CreateProductCommand $command): void
    {
        // ÚNICA RESPONSABILIDAD: Orquestar creación
        $product = $this->createProduct($command);
        $this->productRepository->save($product);
        $this->eventDispatcher->dispatch(new ProductCreatedEvent($product));
    }
}

class ProductCreatedListener
{
    public function __invoke(ProductCreatedEvent $event): void
    {
        // ÚNICA RESPONSABILIDAD: Manejar evento de creación
        $this->emailService->sendNotification($event->getProduct());
    }
}

class ProductRepository
{
    public function save(Product $product): void
    {
        // ÚNICA RESPONSABILIDAD: Persistencia
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $this->cache->invalidate('products');
    }
}
```

### 4.3. Violación de OCP - Ejemplo de Código Malo

```php
// ❌ MALO: Código cerrado para extensión
class EmailService
{
    public function sendEmail($to, $subject, $body): void
    {
        if ($this->config->get('email_provider') === 'smtp') {
            $this->sendViaSmtp($to, $subject, $body);
        } elseif ($this->config->get('email_provider') === 'ses') {
            $this->sendViaSes($to, $subject, $body);
        } else {
            throw new Exception('Unsupported email provider');
        }
    }
    
    // Para agregar nuevo proveedor, hay que modificar esta clase
}
```

### 4.4. Solución Aplicando OCP

```php
// ✅ BUENO: Abierto para extensión, cerrado para modificación
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}

class EmailServiceFactory
{
    public function create(string $provider): EmailSenderInterface
    {
        return match ($provider) {
            'smtp' => $this->smtpMailer,
            'ses' => $this->sesMailer,
            'sendgrid' => $this->sendGridMailer,
            // FÁCIL EXTENSIÓN: Agregar nuevos proveedores aquí
            default => throw new InvalidArgumentException("Unknown provider: {$provider}")
        };
    }
}

// Para agregar nuevo proveedor, solo crear nueva implementación
class MailgunMailer implements EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // Implementación Mailgun
    }
}
```

## 5. Beneficios Demostrados

### 5.1. Testabilidad

```php
// ✅ FÁCIL TESTING: Sustitución con mocks
class CreateProductHandlerTest
{
    public function testCreateProduct(): void
    {
        // Mock del repositorio
        $mockRepository = $this->createMock(ProductRepository::class);
        $mockRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class));

        // Mock del event dispatcher
        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);
        $mockDispatcher->expects($this->once())
            ->method('dispatch');

        $handler = new CreateProductHandler($mockRepository, $mockDispatcher);
        $command = new CreateProductCommand(...);
        
        $handler($command);
    }
}
```

### 5.2. Mantenibilidad

```php
// ✅ FÁCIL MANTENIMIENTO: Cambios localizados
// Para cambiar la lógica de validación de productos
class ProductValidator
{
    public function validate(CreateProductCommand $command): void
    {
        // Solo modificar esta clase
        $this->validateName($command->getProductName());
        $this->validatePrice($command->getPrice());
        $this->validateStock($command->getStock());
    }
}

// Para cambiar la persistencia
class NewProductRepository implements ProductRepository
{
    // Solo implementar esta interfaz
    public function save(Product $product): void
    {
        // Nueva lógica de persistencia
    }
}
```

### 5.3. Reutilización

```php
// ✅ FÁCIL REUTILIZACIÓN: Componentes independientes
class EmailService
{
    public function __construct(EmailSenderInterface $emailSender)
    {
        // Reutilizable con cualquier implementación
        $this->emailSender = $emailSender;
    }
}

// Reutilización en diferentes contextos
$emailService = new EmailService(new SmtpMailer($mailer));
$emailService = new EmailService(new SesMailer($sesClient));
$emailService = new EmailService(new LogMailer($logger));
```

## 6. Conclusión

Los ejemplos anteriores demuestran cómo la aplicación correcta de los principios SOLID:

1. **Mejora la testabilidad** del código
2. **Facilita el mantenimiento** y la evolución
3. **Permite la reutilización** de componentes
4. **Reduce el acoplamiento** entre módulos
5. **Aumenta la flexibilidad** del sistema

La arquitectura hexagonal implementada en este proyecto facilita naturalmente el cumplimiento de estos principios al separar claramente las responsabilidades y usar interfaces para la comunicación entre capas. 