# Servicios de Email - Documentación

## Resumen

El sistema implementa múltiples servicios de email usando una interfaz común (`EmailSenderInterface`), siguiendo los principios de la arquitectura hexagonal. Esto permite cambiar fácilmente entre diferentes proveedores de email sin modificar el código de la aplicación.

## Servicios Implementados

### 1. SmtpMailer (SMTP)
- **Ubicación**: `src/Infrastructure/Notification/SmtpMailer.php`
- **Tecnología**: Symfony Mailer + SMTP
- **Uso**: Producción y desarrollo
- **Configuración**: Usa `MAILER_DSN` de Symfony

### 2. SesMailer (Amazon SES)
- **Ubicación**: `src/Infrastructure/Notification/SesMailer.php`
- **Tecnología**: AWS SDK for PHP
- **Uso**: Producción (alta escalabilidad)
- **Configuración**: Requiere credenciales AWS

### 3. SendGridMailer (SendGrid)
- **Ubicación**: `src/Infrastructure/Notification/SendGridMailer.php`
- **Tecnología**: SendGrid PHP SDK
- **Uso**: Producción (buena entrega)
- **Configuración**: Requiere API Key de SendGrid

### 4. LogMailer (Desarrollo)
- **Ubicación**: `src/Infrastructure/Notification/LogMailer.php`
- **Tecnología**: PSR-3 Logger
- **Uso**: Desarrollo y pruebas
- **Configuración**: Solo registra en logs, no envía emails reales

## Arquitectura

### Interfaz Común
```php
interface EmailSenderInterface
{
    public function send(string $to, string $subject, string $body): void;
}
```

### Factory Pattern
```php
class EmailServiceFactory
{
    public function create(string $service): EmailSenderInterface
    {
        return match ($service) {
            'smtp' => $this->smtpMailer,
            'ses' => $this->sesMailer,
            'sendgrid' => $this->sendGridMailer,
            'log' => $this->logMailer,
        };
    }
}
```

## Configuración

### Variables de Entorno (.env)
```env
# Email general
EMAIL_FROM=daicrela@gmail.com
EMAIL_SERVICE=smtp

# AWS SES
AWS_SES_REGION=us-east-1
AWS_SES_ACCESS_KEY=your_access_key
AWS_SES_SECRET_KEY=your_secret_key

# SendGrid
SENDGRID_API_KEY=your_sendgrid_api_key
```

### Configuración de Servicios (services.yaml)
```yaml
# Puerto/adaptador para envío de emails
App\Domain\Notification\EmailSenderInterface: '@App\Infrastructure\Notification\SmtpMailer'
```

## Uso en la Aplicación

### 1. En Event Listeners
```php
class ProductCreatedListener
{
    public function __construct(private EmailSenderInterface $emailSender) {}
    
    public function __invoke(ProductCreatedDomainEvent $event): void
    {
        $this->emailSender->send('admin@example.com', 'Nuevo producto', '...');
    }
}
```

### 2. En Comandos
```php
class SendTestEmailCommand extends Command
{
    public function __construct(private EmailSenderInterface $mailer)
    {
        parent::__construct();
    }
}
```

### 3. Usando el Factory
```php
class EmailServiceController extends AbstractController
{
    public function __construct(private EmailServiceFactory $emailFactory) {}
    
    public function testService(string $service): void
    {
        $emailService = $this->emailFactory->create($service);
        $emailService->send('test@example.com', 'Test', 'Body');
    }
}
```

## Comandos Disponibles

### Listar Servicios Disponibles
```bash
php bin/console app:test-email-services --list
```

### Probar Servicio Específico
```bash
# Probar SMTP
php bin/console app:test-email-services --service=smtp --to=test@example.com

# Probar SES
php bin/console app:test-email-services --service=ses --to=test@example.com

# Probar SendGrid
php bin/console app:test-email-services --service=sendgrid --to=test@example.com

# Probar Log (desarrollo)
php bin/console app:test-email-services --service=log --to=test@example.com
```

## Endpoints de API

### Listar Servicios Disponibles
```http
GET /api/email/services
```

### Probar Servicio
```http
POST /api/email/test
Content-Type: application/json

{
    "service": "smtp",
    "to": "test@example.com",
    "subject": "Prueba",
    "body": "Cuerpo del email"
}
```

### Cambiar Servicio
```http
POST /api/email/switch/smtp
```

## Instalación de Dependencias

### AWS SES
```bash
composer require aws/aws-sdk-php
```

### SendGrid
```bash
composer require sendgrid/sendgrid
```

## Ventajas de esta Implementación

### 1. Desacoplamiento
- El dominio no conoce detalles de infraestructura
- Los listeners usan la interfaz, no implementaciones concretas

### 2. Flexibilidad
- Fácil cambio entre proveedores
- Configuración por entorno (dev/prod)

### 3. Testabilidad
- LogMailer para pruebas sin envío real
- Fácil mock de la interfaz

### 4. Escalabilidad
- Múltiples proveedores para diferentes necesidades
- Factory pattern para gestión centralizada

## Casos de Uso

### Desarrollo
- Usar `LogMailer` para no enviar emails reales
- Configurar en `services.yaml` o usar factory

### Producción
- Usar `SesMailer` para alta escalabilidad
- Usar `SendGridMailer` para buena entrega
- Usar `SmtpMailer` para control total

### Pruebas
- Usar `LogMailer` para tests unitarios
- Mock de `EmailSenderInterface` para tests de integración

## Troubleshooting

### Error: "SES service not configured"
- Verificar variables de entorno AWS
- Comprobar que `SesMailer` está configurado en `services.yaml`

### Error: "SendGrid service not configured"
- Verificar `SENDGRID_API_KEY`
- Comprobar que `SendGridMailer` está configurado

### Error: "Unknown email service"
- Verificar que el servicio existe en `EmailServiceFactory`
- Comprobar configuración en `services.yaml` 