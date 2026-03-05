<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Créer ou réinitialiser les utilisateurs de test avec le mot de passe "test123"',
)]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $testUsers = [
            [
                'email' => 'admin@medilink.tn',
                'fullName' => 'Administrateur Test',
                'roles' => ['ROLE_ADMIN'],
            ],
            [
                'email' => 'medecin@medilink.tn',
                'fullName' => 'Docteur Test',
                'roles' => ['ROLE_MEDECIN'],
            ],
            [
                'email' => 'patient@medilink.tn',
                'fullName' => 'Patient Test',
                'roles' => ['ROLE_USER'],
            ],
        ];

        $password = 'test123';

        foreach ($testUsers as $userData) {
            $user = $this->userRepository->findOneBy(['email' => $userData['email']]);
            
            if (!$user) {
                $user = new User();
                $user->setEmail($userData['email']);
                $user->setFullName($userData['fullName']);
                $io->success(sprintf('Création de l\'utilisateur: %s', $userData['email']));
            } else {
                $io->info(sprintf('Mise à jour de l\'utilisateur: %s', $userData['email']));
            }

            $user->setRoles($userData['roles']);
            $user->setStatus(User::STATUS_ACTIVE);
            $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success('Utilisateurs de test créés/mis à jour avec succès !');
        $io->newLine();
        $io->section('Comptes disponibles (mot de passe: test123)');
        $io->table(
            ['Email', 'Rôle', 'Nom'],
            [
                ['admin@medilink.tn', 'ROLE_ADMIN', 'Administrateur Test'],
                ['medecin@medilink.tn', 'ROLE_MEDECIN', 'Docteur Test'],
                ['patient@medilink.tn', 'ROLE_USER', 'Patient Test'],
            ]
        );

        return Command::SUCCESS;
    }
}
