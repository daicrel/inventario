<?php

// src/Command/SendTestEmailCommand.php

namespace App\Command;

use App\Domain\Notification\EmailSenderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:test-email')]
class SendTestEmailCommand extends Command
{
    public function __construct(private EmailSenderInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mailer->send('daicrel@gmail.com', 'Prueba de correo', 'Esto es una prueba');
        $output->writeln('Correo enviado.');
        return Command::SUCCESS;
    }
}
