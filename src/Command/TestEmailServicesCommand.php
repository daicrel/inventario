<?php

// src/Command/TestEmailServicesCommand.php

namespace App\Command;

use App\Infrastructure\Notification\EmailServiceFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:test-email-services')]
class TestEmailServicesCommand extends Command
{
    public function __construct(private EmailServiceFactory $emailFactory)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Prueba los diferentes servicios de email disponibles')
            ->addOption(
                'service',
                's',
                InputOption::VALUE_REQUIRED,
                'Servicio de email a probar (smtp, ses, sendgrid, log)',
                'log'
            )
            ->addOption(
                'to',
                't',
                InputOption::VALUE_REQUIRED,
                'Email de destino',
                'test@example.com'
            )
            ->addOption(
                'subject',
                null,
                InputOption::VALUE_REQUIRED,
                'Asunto del email',
                'Prueba de servicio de email'
            )
            ->addOption(
                'body',
                'b',
                InputOption::VALUE_REQUIRED,
                'Cuerpo del email',
                'Este es un email de prueba enviado desde el sistema de inventario.'
            )
            ->addOption(
                'list',
                'l',
                InputOption::VALUE_NONE,
                'Lista los servicios disponibles'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            $this->listAvailableServices($io);
            return Command::SUCCESS;
        }

        $service = $input->getOption('service');
        $to = $input->getOption('to');
        $subject = $input->getOption('subject');
        $body = $input->getOption('body');

        try {
            $emailService = $this->emailFactory->create($service);
            
            $io->info("Enviando email usando el servicio: {$service}");
            $io->text("Destinatario: {$to}");
            $io->text("Asunto: {$subject}");
            
            $emailService->send($to, $subject, $body);
            
            $io->success("Email enviado exitosamente usando {$service}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error("Error al enviar email: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function listAvailableServices(SymfonyStyle $io): void
    {
        $services = $this->emailFactory->getAvailableServices();
        
        $io->title('Servicios de Email Disponibles');
        $io->table(
            ['Servicio', 'Descripción'],
            [
                ['smtp', 'Envío via SMTP usando Symfony Mailer'],
                ['ses', 'Amazon Simple Email Service (SES)'],
                ['sendgrid', 'SendGrid Email Service'],
                ['log', 'Solo registra en logs (desarrollo/pruebas)']
            ]
        );
        
        $io->text('Servicios configurados: ' . implode(', ', $services));
    }
} 