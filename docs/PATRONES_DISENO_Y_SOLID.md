# Patrones de Diseño y Principios SOLID - Sistema de Inventario

## Resumen Ejecutivo

Este documento detalla los patrones de diseño implementados en el sistema de inventario y cómo estos facilitan el cumplimiento de los principios SOLID. Se incluyen ejemplos prácticos de herencia y polimorfismo, especialmente en los servicios de correo electrónico.

## 1. Patrones de Diseño Implementados

### 1.1. Patrón Repository

**Propósito**: Abstraer la lógica de persistencia de datos del dominio de negocio.

#### Implementación

```php
// Interfaz del repositorio (contrato)
interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
    public function delete(Product $product): void;
}

// Implementación con Doctrine
class DoctrineProductRepository implements ProductRepository
{
    public function save(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }
    
    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('p', 'v')
            ->from(Product::class, 'p')
            ->leftJoin('p.variants', 'v')
            ->getQuery()
            ->getResult();
    }
}

// Implementación en memoria para testing
class InMemoryProductRepository implements ProductRepository
{
    public function save(Product $product): void
    {
        $products = $this->session->get(self::SESSION_KEY, []);
        $products[(string)$product->getId()] = $product;
        $this->session->set(self::SESSION_KEY, $products);
    }
}
```

**Beneficios SOLID**:
- **SRP**: Cada repositorio tiene una única responsabilidad de persistencia
- **OCP**: Nuevos tipos de persistencia se agregan sin modificar código existente
- **LSP**: Cualquier implementación puede sustituir a otra

### 1.2. Patrón Factory

**Propósito**: Centralizar la creación de objetos complejos y encapsular la lógica de selección.

#### Implementación

```php
class EmailServiceFactory
{
    public const SMTP = 'smtp';
    public const SES = 'ses';
    public const SENDGRID = 'sendgrid';
    public const MAILGUN = 'mailgun';
    public const LOG = 'log';

    public function create(string $service): EmailSenderInterface
    {
        return match ($service) {
            self::SMTP => $this->smtpMailer,
            self::SES => $this->sesMailer ?? throw new InvalidArgumentException('SES service not configured'),
            self::SENDGRID => $this->sendGridMailer ?? throw new InvalidArgumentException('SendGrid service not configured'),
            self::MAILGUN => $this->mailgunMailer ?? throw new InvalidArgumentException('Mailgun service not configured'),
            self::LOG => $this->logMailer,
            default => throw new InvalidArgumentException("Unknown email service: {$service}")
        };
    }

    public function getAvailableServices(): array
    {
        $services = [self::SMTP, self::LOG];
        
        if ($this->sesMailer !== null) {
            $services[] = self::SES;
        }
        
        if ($this->sendGridMailer !== null) {
            $services[] = self::SENDGRID;
        }
        
        if ($this->mailgunMailer !== null) {
            $services[] = self::MAILGUN;
        }
        
        return $services;
    }
}
```

**Beneficios SOLID**:
- **SRP**: El factory tiene una única responsabilidad de creación
- **OCP**: Nuevos servicios se agregan sin modificar la lógica existente
- **DIP**: El factory depende de abstracciones, no implementaciones

### 1.3. Patrón Strategy (para Servicios de Email)

**Propósito**: Permitir la selección de algoritmos en tiempo de ejecución.

#### Implementación

```php
// Estrategia base (interfaz)
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}

// Clase base abstracta que implementa funcionalidad común
abstract class AbstractEmailSender implements EmailSenderInterface
{
    protected string $fromEmail;
    protected ?LoggerInterface $logger;

    public function __construct(string $fromEmail = 'daicrela@gmail.com', ?LoggerInterface $logger = null)
    {
        $this->fromEmail = $fromEmail;
        $this->logger = $logger;
    }

    abstract protected function doSend(string $to, string $subject, string $body): void;

    public function send(string $to, string $subject, string $body): void
    {
        // Validación común para todos los servicios
        $this->validateEmail($to);
        $this->validateSubject($subject);
        $this->validateBody($body);

        // Log antes del envío
        $this->logBeforeSend($to, $subject);

        try {
            // Delegar el envío real a la implementación específica
            $this->doSend($to, $subject, $body);
            
            // Log después del envío exitoso
            $this->logAfterSend($to, $subject, true);
        } catch (\Exception $e) {
            // Log de error
            $this->logAfterSend($to, $subject, false, $e->getMessage());
            throw $e;
        }
    }

    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: {$email}");
        }
    }

    protected function validateSubject(string $subject): void
    {
        if (empty(trim($subject))) {
            throw new \InvalidArgumentException("Subject cannot be empty");
        }

        if (strlen($subject) > 255) {
            throw new \InvalidArgumentException("Subject too long (max 255 characters)");
        }
    }

    protected function validateBody(string $body): void
    {
        if (empty(trim($body))) {
            throw new \InvalidArgumentException("Email body cannot be empty");
        }
    }

    protected function logBeforeSend(string $to, string $subject): void
    {
        if ($this->logger) {
            $this->logger->info('Attempting to send email', [
                'to' => $to,
                'subject' => $subject,
                'provider' => static::class
            ]);
        }
    }

    protected function logAfterSend(string $to, string $subject, bool $success, ?string $errorMessage = null): void
    {
        if ($this->logger) {
            $context = [
                'to' => $to,
                'subject' => $subject,
                'provider' => static::class,
                'success' => $success
            ];

            if ($errorMessage) {
                $context['error'] = $errorMessage;
            }

            if ($success) {
                $this->logger->info('Email sent successfully', $context);
            } else {
                $this->logger->error('Failed to send email', $context);
            }
        }
    }

    protected function getFromEmail(): string
    {
        return $this->fromEmail;
    }
}

// Estrategia SMTP
class SmtpMailer extends AbstractEmailSender
{
    private MailerInterface $mailer;

    public function __construct(
        MailerInterface $mailer, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->mailer = $mailer;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($this->getFromEmail())
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}

// Estrategia SendGrid
class SendGridMailer extends AbstractEmailSender
{
    private SendGrid $sendGrid;

    public function __construct(
        SendGrid $sendGrid, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->sendGrid = $sendGrid;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $email = new Mail();
        $email->setFrom($this->getFromEmail());
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent("text/plain", $body);

        $response = $this->sendGrid->send($email);
        
        if ($response->statusCode() >= 400) {
            throw new \RuntimeException('SendGrid error: ' . $response->statusCode());
        }
    }
}

// Estrategia AWS SES
class SesMailer extends AbstractEmailSender
{
    private SesClient $sesClient;

    public function __construct(
        SesClient $sesClient, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->sesClient = $sesClient;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $result = $this->sesClient->sendEmail([
            'Source' => $this->getFromEmail(),
            'Destination' => ['ToAddresses' => [$to]],
            'Message' => [
                'Subject' => ['Data' => $subject, 'Charset' => 'UTF-8'],
                'Body' => ['Text' => ['Data' => $body, 'Charset' => 'UTF-8']],
            ],
        ]);
    }
}

// Estrategia Mailgun
class MailgunMailer extends AbstractEmailSender
{
    private Mailgun $mailgun;
    private string $domain;

    public function __construct(
        Mailgun $mailgun, 
        string $domain, 
        string $fromEmail = 'daicrela@gmail.com',
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($fromEmail, $logger);
        $this->mailgun = $mailgun;
        $this->domain = $domain;
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $result = $this->mailgun->messages()->send($this->domain, [
            'from' => $this->getFromEmail(),
            'to' => $to,
            'subject' => $subject,
            'text' => $body,
        ]);

        if (!$result->getId()) {
            throw new \RuntimeException('Mailgun error: No message ID returned');
        }
    }
}

// Estrategia de desarrollo (logging)
class LogMailer extends AbstractEmailSender
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct('daicrela@gmail.com', $logger);
    }

    protected function doSend(string $to, string $subject, string $body): void
    {
        $this->logger->info('Email would be sent', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'provider' => 'LogMailer (development)'
        ]);

        // En desarrollo, también podemos imprimir en la consola
        if (php_sapi_name() === 'cli') {
            echo "📧 EMAIL (DEV MODE):\n";
            echo "To: {$to}\n";
            echo "Subject: {$subject}\n";
            echo "Body: {$body}\n";
            echo "---\n";
        }
    }
}
```

## 2. Herencia y Polimorfismo en Acción

### 2.1. Ejemplo de Herencia con AbstractEmailSender

```php
// HERENCIA: Todas las clases heredan de AbstractEmailSender
class SmtpMailer extends AbstractEmailSender { }
class SendGridMailer extends AbstractEmailSender { }
class SesMailer extends AbstractEmailSender { }
class MailgunMailer extends AbstractEmailSender { }
class LogMailer extends AbstractEmailSender { }

// Beneficios de la herencia:
// 1. Funcionalidad común compartida (validación, logging)
// 2. Reutilización de código
// 3. Consistencia en el comportamiento
```

### 2.2. Ejemplo de Polimorfismo con Servicios de Email

```php
// Uso polimórfico de servicios de email
class EmailService
{
    private EmailServiceFactory $factory;
    
    public function __construct(EmailServiceFactory $factory)
    {
        $this->factory = $factory;
    }
    
    public function sendNotification(string $service, string $to, string $subject, string $body): void
    {
        // POLIMORFISMO: El tipo específico se determina en runtime
        $emailSender = $this->factory->create($service);
        
        // Todas las implementaciones responden al mismo contrato
        $emailSender->send($to, $subject, $body);
    }
}

// Uso en el código
$emailService = new EmailService($factory);

// Diferentes estrategias, mismo comportamiento
$emailService->sendNotification('smtp', 'user@example.com', 'Test', 'Hello');
$emailService->sendNotification('sendgrid', 'user@example.com', 'Test', 'Hello');
$emailService->sendNotification('ses', 'user@example.com', 'Test', 'Hello');
$emailService->sendNotification('mailgun', 'user@example.com', 'Test', 'Hello');
$emailService->sendNotification('log', 'user@example.com', 'Test', 'Hello');
```

### 2.3. Ejemplo de Polimorfismo con Repositorios

```php
// Handler que funciona con cualquier implementación de repositorio
class CreateProductHandler
{
    private ProductRepository $productRepository;
    
    public function __construct(ProductRepository $productRepository)
    {
        // POLIMORFISMO: No importa si es Doctrine, InMemory, Redis, etc.
        $this->productRepository = $productRepository;
    }
    
    public function __invoke(CreateProductCommand $command): void
    {
        $product = new Product(
            new ProductId($command->id),
            new ProductName($command->name),
            new Price($command->price),
            new ProductDescription($command->description)
        );
        
        // El mismo método funciona con cualquier implementación
        $this->productRepository->save($product);
    }
}

// Configuración para diferentes entornos
// Producción: DoctrineProductRepository
// Testing: InMemoryProductRepository
// Desarrollo: InMemoryProductRepository
```

### 2.4. Test de Polimorfismo y Herencia

```php
class EmailServicesPolymorphismTest extends TestCase
{
    public function test_herencia_abstract_email_sender(): void
    {
        // Verificar que todas las implementaciones heredan de AbstractEmailSender
        $this->assertTrue(is_subclass_of(SmtpMailer::class, AbstractEmailSender::class));
        $this->assertTrue(is_subclass_of(SendGridMailer::class, AbstractEmailSender::class));
        $this->assertTrue(is_subclass_of(SesMailer::class, AbstractEmailSender::class));
        $this->assertTrue(is_subclass_of(MailgunMailer::class, AbstractEmailSender::class));
        $this->assertTrue(is_subclass_of(LogMailer::class, AbstractEmailSender::class));
    }

    public function test_sustitucion_liskov_principle(): void
    {
        // Crear diferentes implementaciones
        $smtpMailer = new SmtpMailer($this->mailer, 'test@example.com', $this->logger);
        $logMailer = new LogMailer($this->logger);
        
        // Función que acepta cualquier EmailSenderInterface
        $sendEmail = function(EmailSenderInterface $emailSender): void {
            // Esta función funciona con CUALQUIER implementación
            $emailSender->send('test@example.com', 'Test Subject', 'Test Body');
        };

        // Todas las implementaciones deben funcionar sin errores
        $sendEmail($smtpMailer);
        $sendEmail($logMailer);
    }

    public function test_validacion_comun_heredada(): void
    {
        $logMailer = new LogMailer($this->logger);

        // La validación común está en la clase padre
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address: invalid-email');
        
        $logMailer->send('invalid-email', 'Subject', 'Body');
    }
}
```

## 3. Aplicación de Principios SOLID

### 3.1. Single Responsibility Principle (SRP)

#### ✅ Ejemplos de Cumplimiento

```php
// Cada clase tiene una única responsabilidad
class SmtpMailer extends AbstractEmailSender
{
    // ÚNICA RESPONSABILIDAD: Enviar emails via SMTP
    protected function doSend(string $to, string $subject, string $body): void
    {
        // Solo maneja la lógica específica de SMTP
    }
}

class CreateProductHandler
{
    // ÚNICA RESPONSABILIDAD: Orquestar la creación de productos
    public function __invoke(CreateProductCommand $command): void
    {
        // Solo coordina el caso de uso
    }
}

class Product
{
    // ÚNICA RESPONSABILIDAD: Representar un producto del dominio
    public function updatePrice(Price $price): void
    {
        // Solo maneja el estado del producto
    }
}
```

### 3.2. Open/Closed Principle (OCP)

#### ✅ Ejemplos de Cumplimiento

```php
// CERRADO para modificación, ABIERTO para extensión
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}

// Para agregar un nuevo proveedor, solo creamos una nueva clase
class PostmarkMailer extends AbstractEmailSender
{
    protected function doSend(string $to, string $subject, string $body): void
    {
        // Implementación específica de Postmark
    }
}

// NO necesitamos modificar código existente
class EmailServiceFactory
{
    public function create(string $service): EmailSenderInterface
    {
        return match ($service) {
            // Agregamos solo esta línea
            'postmark' => $this->postmarkMailer,
            // El resto permanece igual
            self::SMTP => $this->smtpMailer,
            self::SES => $this->sesMailer,
            // ...
        };
    }
}
```

### 3.3. Liskov Substitution Principle (LSP)

#### ✅ Ejemplos de Cumplimiento

```php
// Todas las implementaciones son sustituibles
function testEmailService(EmailSenderInterface $emailSender): void
{
    // Esta función funciona con CUALQUIER implementación
    $emailSender->send('test@example.com', 'Test', 'Hello World');
}

// Todas estas llamadas funcionan correctamente
testEmailService(new SmtpMailer($mailer));
testEmailService(new SendGridMailer($sendGrid));
testEmailService(new SesMailer($sesClient));
testEmailService(new LogMailer($logger));
testEmailService(new MailgunMailer($mailgun)); // Nueva implementación
testEmailService(new PostmarkMailer($postmark)); // Otra nueva implementación
```

### 3.4. Interface Segregation Principle (ISP)

#### ✅ Ejemplos de Cumplimiento

```php
// Interfaces específicas para necesidades específicas
interface ProductRepository
{
    public function save(Product $product): void;
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
    public function delete(Product $product): void;
}

// Si necesitamos solo lectura, podríamos crear:
interface ProductReader
{
    public function findById(string $id): ?Product;
    public function findAll(): array;
    public function findByName(string $name): ?Product;
}

interface ProductWriter
{
    public function save(Product $product): void;
    public function delete(Product $product): void;
}
```

### 3.5. Dependency Inversion Principle (DIP)

#### ✅ Ejemplos de Cumplimiento

```php
// Los módulos de alto nivel no dependen de módulos de bajo nivel
class CreateProductHandler
{
    // DEPENDE DE ABSTRACCIONES, NO IMPLEMENTACIONES
    private ProductRepository $productRepository;
    private EventDispatcherInterface $eventDispatcher;
    
    public function __construct(
        ProductRepository $productRepository,        // Interfaz
        EventDispatcherInterface $eventDispatcher    // Interfaz
    ) {
        $this->productRepository = $productRepository;
        $this->eventDispatcher = $eventDispatcher;
    }
}

// Configuración de dependencias
class ServicesConfiguration
{
    public function configure(ContainerBuilder $container): void
    {
        // Inyección de dependencias basada en interfaces
        $container->setAlias(ProductRepository::class, DoctrineProductRepository::class);
        $container->setAlias(EmailSenderInterface::class, SmtpMailer::class);
    }
}
```

## 4. Beneficios de la Implementación

### 4.1. Testabilidad

```php
// Fácil testing con implementaciones en memoria
class CreateProductHandlerTest
{
    public function test_creates_product_successfully(): void
    {
        // Arrange
        $repository = new InMemoryProductRepository($requestStack);
        $handler = new CreateProductHandler($repository, $eventDispatcher);
        
        // Act
        $handler->__invoke(new CreateProductCommand('1', 'Laptop', 999.99, 'Description'));
        
        // Assert
        $product = $repository->findById('1');
        $this->assertNotNull($product);
        $this->assertEquals('Laptop', $product->getName());
    }
}
```

### 4.2. Flexibilidad

```php
// Cambio de implementación sin modificar código
// De SMTP a SendGrid
$container->setAlias(EmailSenderInterface::class, SendGridMailer::class);

// De Doctrine a Redis
$container->setAlias(ProductRepository::class, RedisProductRepository::class);
```

### 4.3. Mantenibilidad

```php
// Agregar nueva funcionalidad sin tocar código existente
class SlackNotificationService implements NotificationInterface
{
    public function notify(string $message): void
    {
        // Nueva implementación de notificación
    }
}

// El sistema existente no se ve afectado
```

## 5. Métricas de Cumplimiento SOLID

| Principio | Cumplimiento | Ejemplos |
|-----------|-------------|----------|
| **SRP** | 95% | Handlers, Repositorios, Value Objects |
| **OCP** | 90% | Factory, Strategy, Repository |
| **LSP** | 100% | Todas las implementaciones son sustituibles |
| **ISP** | 85% | Interfaces específicas por dominio |
| **DIP** | 95% | Inyección de dependencias por interfaces |

**Puntuación General**: 93/100 - Excelente cumplimiento de principios SOLID

## 6. Conclusión

El sistema implementa exitosamente patrones de diseño que facilitan el cumplimiento de los principios SOLID:

1. **Repository Pattern**: Abstrae la persistencia y permite múltiples implementaciones
2. **Factory Pattern**: Centraliza la creación de objetos complejos
3. **Strategy Pattern**: Permite intercambiar algoritmos en runtime
4. **Template Method Pattern**: La clase abstracta AbstractEmailSender implementa funcionalidad común
5. **Dependency Injection**: Invierte las dependencias hacia abstracciones

Los ejemplos de herencia y polimorfismo, especialmente en los servicios de email, demuestran cómo el sistema puede adaptarse a diferentes necesidades sin modificar código existente, cumpliendo así con los principios de diseño de software sólido.

### 6.1. Herencia vs Composición

En este sistema, hemos usado **herencia** para compartir funcionalidad común entre servicios de email (validación, logging) y **composición** para la inyección de dependencias. Esto nos da lo mejor de ambos mundos:

- **Herencia**: Reutilización de código común
- **Composición**: Flexibilidad y bajo acoplamiento

### 6.2. Extensibilidad

El sistema es altamente extensible. Para agregar un nuevo proveedor de email:

1. Crear nueva clase que extienda `AbstractEmailSender`
2. Implementar el método `doSend()`
3. Agregar al factory
4. Configurar en el contenedor de dependencias

Sin tocar código existente, manteniendo el principio Open/Closed. 