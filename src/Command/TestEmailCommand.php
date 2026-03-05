<?php

namespace App\Command;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test du système de mailing',
)]
class TestEmailCommand extends Command
{
    public function __construct(private EmailService $emailService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('📧 Test du système de mailing...');
        
        $result = $this->emailService->sendOrdonnanceNotification(
            'Dr. Test',
            'Patient Test',
            '<ul>
                <li>Paracétamol 500mg - 1 comprimé 3 fois par jour</li>
                <li>Ibuprofène 400mg - 1 comprimé 2 fois par jour</li>
            </ul>',
            'test@medilink.local'
        );
        
        if ($result) {
            $output->writeln('<info>✅ Email envoyé avec succès aux administrateurs!</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>❌ Erreur lors de l\'envoi de l\'email</error>');
            return Command::FAILURE;
        }
    }
}
