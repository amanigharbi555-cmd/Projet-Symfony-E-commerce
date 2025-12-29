<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(Users::class)
            ->findOneBy(['email' => 'amani@gmail.com']);

        if ($existingUser) {
            $io->warning('Un utilisateur avec l\'email amani@gmail.com existe déjà.');
            return Command::FAILURE;
        }

        // Créer le nouvel utilisateur administrateur
        $admin = new Users();
        $admin->setEmail('amani@gmail.com');
        $admin->setFirstname('amani');
        $admin->setLastname('amani');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'amani1234')
        );
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Utilisateur administrateur créé avec succès !');
        $io->table(
            ['Champ', 'Valeur'],
            [
                ['Email', 'amani@gmail.com'],
                ['Prénom', 'amani'],
                ['Nom', 'amani'],
                ['Rôle', 'ROLE_ADMIN'],
                ['Mot de passe', 'amani1234'],
            ]
        );

        return Command::SUCCESS;
    }
}

