<?php

// tests/Infrastructure/Notification/EmailServicesPolymorphismTest.php

namespace App\Tests\Infrastructure\Notification;

use App\Infrastructure\Notification\AbstractEmailSender;
use App\Infrastructure\Notification\EmailServiceFactory;
use App\Infrastructure\Notification\LogMailer;
use App\Infrastructure\Notification\SmtpMailer;
use App\Domain\Notification\EmailSenderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

class EmailServicesPolymorphismTest extends TestCase
{
    private LoggerInterface $logger;
    private MailerInterface $mailer;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
    }

    public function test_herencia_abstract_email_sender(): void
    {
        // Verificar que las implementaciones disponibles heredan de AbstractEmailSender
        $this->assertTrue(is_subclass_of(SmtpMailer::class, AbstractEmailSender::class));
        $this->assertTrue(is_subclass_of(LogMailer::class, AbstractEmailSender::class));
    }

    public function test_polimorfismo_implementaciones_email_sender(): void
    {
        // Verificar que las implementaciones disponibles implementan EmailSenderInterface
        $this->assertTrue(is_subclass_of(SmtpMailer::class, EmailSenderInterface::class));
        $this->assertTrue(is_subclass_of(LogMailer::class, EmailSenderInterface::class));
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
        $this->expectNotToPerformAssertions();
        
        $sendEmail($smtpMailer);
        $sendEmail($logMailer);
    }

    public function test_factory_crea_diferentes_implementaciones(): void
    {
        $factory = new EmailServiceFactory(
            new SmtpMailer($this->mailer, 'test@example.com', $this->logger),
            new LogMailer($this->logger)
        );

        // Verificar que el factory crea diferentes tipos de implementaciones
        $smtp = $factory->create('smtp');
        $log = $factory->create('log');

        // Todas deben ser instancias de EmailSenderInterface
        $this->assertInstanceOf(EmailSenderInterface::class, $smtp);
        $this->assertInstanceOf(EmailSenderInterface::class, $log);

        // Todas deben heredar de AbstractEmailSender
        $this->assertInstanceOf(AbstractEmailSender::class, $smtp);
        $this->assertInstanceOf(AbstractEmailSender::class, $log);
    }

    public function test_herencia_comparte_funcionalidad_comun(): void
    {
        $smtpMailer = new SmtpMailer($this->mailer, 'test@example.com', $this->logger);
        $logMailer = new LogMailer($this->logger);

        // Verificar que ambas instancias tienen acceso a métodos heredados
        $this->assertTrue(method_exists($smtpMailer, 'send'));
        $this->assertTrue(method_exists($logMailer, 'send'));

        // Verificar que ambas instancias tienen propiedades heredadas
        $this->assertTrue(property_exists($smtpMailer, 'fromEmail'));
        $this->assertTrue(property_exists($logMailer, 'fromEmail'));
        $this->assertTrue(property_exists($smtpMailer, 'logger'));
        $this->assertTrue(property_exists($logMailer, 'logger'));
    }

    public function test_validacion_comun_heredada(): void
    {
        $logMailer = new LogMailer($this->logger);

        // La validación común está en la clase padre
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address: invalid-email');
        
        $logMailer->send('invalid-email', 'Subject', 'Body');
    }

    public function test_servicios_disponibles_factory(): void
    {
        $factory = new EmailServiceFactory(
            new SmtpMailer($this->mailer, 'test@example.com', $this->logger),
            new LogMailer($this->logger)
        );

        $availableServices = $factory->getAvailableServices();

        $this->assertContains('smtp', $availableServices);
        $this->assertContains('log', $availableServices);
    }

    public function test_abstract_email_sender_implements_interface(): void
    {
        // Verificar que AbstractEmailSender implementa EmailSenderInterface
        $this->assertTrue(is_subclass_of(AbstractEmailSender::class, EmailSenderInterface::class));
    }

    public function test_template_method_pattern(): void
    {
        $logMailer = new LogMailer($this->logger);
        
        // Verificar que el patrón Template Method funciona
        // El método send() está en la clase padre, pero doSend() está en la hija
        $this->assertTrue(method_exists($logMailer, 'send'));
        $this->assertTrue(method_exists($logMailer, 'doSend'));
        
        // Verificar que doSend es protegido (solo accesible por herencia)
        $reflection = new \ReflectionClass($logMailer);
        $doSendMethod = $reflection->getMethod('doSend');
        $this->assertTrue($doSendMethod->isProtected());
    }
} 