<?php

// tests/Infrastructure/Notification/EmailServiceFactoryTest.php

namespace App\Tests\Infrastructure\Notification;

use App\Domain\Notification\EmailSenderInterface;
use App\Infrastructure\Notification\EmailServiceFactory;
use App\Infrastructure\Notification\LogMailer;
use App\Infrastructure\Notification\SendGridMailer;
use App\Infrastructure\Notification\SesMailer;
use App\Infrastructure\Notification\SmtpMailer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EmailServiceFactoryTest extends TestCase
{
    private EmailServiceFactory $factory;
    private SmtpMailer $smtpMailer;
    private LogMailer $logMailer;
    private SesMailer $sesMailer;
    private SendGridMailer $sendGridMailer;

    protected function setUp(): void
    {
        $this->smtpMailer = $this->createMock(SmtpMailer::class);
        $this->logMailer = $this->createMock(LogMailer::class);
        $this->sesMailer = $this->createMock(SesMailer::class);
        $this->sendGridMailer = $this->createMock(SendGridMailer::class);

        $this->factory = new EmailServiceFactory(
            $this->smtpMailer,
            $this->logMailer,
            $this->sesMailer,
            $this->sendGridMailer
        );
    }

    public function testCreateSmtpService(): void
    {
        $service = $this->factory->create(EmailServiceFactory::SMTP);
        
        $this->assertInstanceOf(EmailSenderInterface::class, $service);
        $this->assertSame($this->smtpMailer, $service);
    }

    public function testCreateLogService(): void
    {
        $service = $this->factory->create(EmailServiceFactory::LOG);
        
        $this->assertInstanceOf(EmailSenderInterface::class, $service);
        $this->assertSame($this->logMailer, $service);
    }

    public function testCreateSesService(): void
    {
        $service = $this->factory->create(EmailServiceFactory::SES);
        
        $this->assertInstanceOf(EmailSenderInterface::class, $service);
        $this->assertSame($this->sesMailer, $service);
    }

    public function testCreateSendGridService(): void
    {
        $service = $this->factory->create(EmailServiceFactory::SENDGRID);
        
        $this->assertInstanceOf(EmailSenderInterface::class, $service);
        $this->assertSame($this->sendGridMailer, $service);
    }

    public function testCreateUnknownServiceThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown email service: unknown');
        
        $this->factory->create('unknown');
    }

    public function testGetAvailableServices(): void
    {
        $services = $this->factory->getAvailableServices();
        
        $this->assertContains(EmailServiceFactory::SMTP, $services);
        $this->assertContains(EmailServiceFactory::LOG, $services);
        $this->assertContains(EmailServiceFactory::SES, $services);
        $this->assertContains(EmailServiceFactory::SENDGRID, $services);
    }

    public function testGetAvailableServicesWithNullServices(): void
    {
        $factory = new EmailServiceFactory(
            $this->smtpMailer,
            $this->logMailer,
            null, // SES no configurado
            null  // SendGrid no configurado
        );

        $services = $factory->getAvailableServices();
        
        $this->assertContains(EmailServiceFactory::SMTP, $services);
        $this->assertContains(EmailServiceFactory::LOG, $services);
        $this->assertNotContains(EmailServiceFactory::SES, $services);
        $this->assertNotContains(EmailServiceFactory::SENDGRID, $services);
    }

    public function testCreateSesServiceWhenNotConfiguredThrowsException(): void
    {
        $factory = new EmailServiceFactory(
            $this->smtpMailer,
            $this->logMailer,
            null, // SES no configurado
            $this->sendGridMailer
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SES service not configured');
        
        $factory->create(EmailServiceFactory::SES);
    }

    public function testCreateSendGridServiceWhenNotConfiguredThrowsException(): void
    {
        $factory = new EmailServiceFactory(
            $this->smtpMailer,
            $this->logMailer,
            $this->sesMailer,
            null // SendGrid no configurado
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SendGrid service not configured');
        
        $factory->create(EmailServiceFactory::SENDGRID);
    }
} 