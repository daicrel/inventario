<?php

// src/Infrastructure/Notification/Controller/EmailServiceController.php

namespace App\Infrastructure\Notification\Controller;

use App\Infrastructure\Notification\EmailServiceFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/email')]
class EmailServiceController extends AbstractController
{
    public function __construct(private EmailServiceFactory $emailFactory)
    {
    }

    #[Route('/services', name: 'list_email_services', methods: ['GET'])]
    public function listServices(): JsonResponse
    {
        $availableServices = $this->emailFactory->getAvailableServices();
        
        $serviceDescriptions = [
            'smtp' => 'Envío via SMTP usando Symfony Mailer',
            'ses' => 'Amazon Simple Email Service (SES)',
            'sendgrid' => 'SendGrid Email Service',
            'log' => 'Solo registra en logs (desarrollo/pruebas)'
        ];

        $services = [];
        foreach ($availableServices as $service) {
            $services[] = [
                'name' => $service,
                'description' => $serviceDescriptions[$service] ?? 'Servicio de email'
            ];
        }

        return $this->json([
            'available_services' => $services,
            'current_service' => 'smtp' // Por defecto
        ]);
    }

    #[Route('/test', name: 'test_email_service', methods: ['POST'])]
    public function testService(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $service = $data['service'] ?? 'log';
        $to = $data['to'] ?? 'test@example.com';
        $subject = $data['subject'] ?? 'Prueba de servicio de email';
        $body = $data['body'] ?? 'Este es un email de prueba enviado desde el sistema de inventario.';

        try {
            $emailService = $this->emailFactory->create($service);
            $emailService->send($to, $subject, $body);

            return $this->json([
                'success' => true,
                'message' => "Email enviado exitosamente usando {$service}",
                'service' => $service,
                'to' => $to,
                'subject' => $subject
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $service
            ], 500);
        }
    }

    #[Route('/switch/{service}', name: 'switch_email_service', methods: ['POST'])]
    public function switchService(string $service): JsonResponse
    {
        try {
            // Verificar que el servicio existe
            $this->emailFactory->create($service);

            return $this->json([
                'success' => true,
                'message' => "Servicio de email cambiado a {$service}",
                'service' => $service
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
} 